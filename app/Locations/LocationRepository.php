<?php

namespace App\Locations;

use Laracasts\Commander\Events\EventGenerator;
use App\Locations\Events\NewLocationCreated;

use Dingo\Api\Exception\ResourceException;

class LocationRepository
{
    use EventGenerator;

    /**
     * @var App\Locations\Location
     */
    protected $model;

    /**
     * Repository constructor
     *
     * @param App\Locations\Location
     *
     * @return null
     */
    public function __construct(Location $model)
    {
        $this->model = $model;
    }

    /**
     * Location search,
     * Search not implemented yet...
     *
     * @param array // desired filters
     * @param array // desired columns
     *
     * @return null
     */
    public function getFilteredList($filters, $columns = array('*'))
    {
        #TODO: Add proper filtering
        return $this->model
            ->all($columns);
    }
    
    public function find ($id)
    {
        return (new $this->model)->find($id);
    }

    /**
     * Save a freshly created or recently modified location
     *
     * @param App\Locations\Location
     *
     * @return boolean
     */
    public function save(Location $location)
    {
        return $location->save();
    }

    /**
     * Create location parameters from an address, city, and province
     *
     * @param string    //  street address
     * @param string    //  city
     * @param string    //  province
     *
     * @return array    //  location parameters for create or update
     */
    public function geoLocate ($address1, $city, $province)
    {
        // url encode the address
        $address = urlencode("$address1, $city, $province");
        // google map geocode api url
        $url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address={$address}";
        // get the json response
        $geo = json_decode(file_get_contents($url), true);

        $result = array_shift($geo['results']);

        if (!$result) throw new ResourceException ($geo['status']);

        $params = [
            "lat" => $result['geometry']['location']['lat'],
            "lng" => $result['geometry']['location']['lng'],
        ];
        
        foreach($result['address_components'] as $part) {
            foreach ($part['types'] as $key => $value) {
                if ($value == "neighborhood") {
                    $params['neighbourhood'] = $part['long_name'];
                }
                if ($value == "postal_code") {
                    $params['postal'] = $part['long_name'];
                }
                if ($value == "country") {
                    $params['country'] = $part['long_name'];
                }
                if ($value == "administrative_area_level_1") {
                    $params['province'] = $part['long_name'];
                }
                if ($value == "locality") {
                    $params['city'] = $part['long_name'];
                }
            }
        }
        
        return $params;
    }

    /**
     * Create a new location
     *
     * @param User or Studio
     * @param array     //  location parameters
     *
     * @return App\Locations\Location
     */
    public function createNewLocation($owner, $params)
    {
        $params = (object) $params;

        if (!isset($params->lat) || !isset($params->lng)) {
            $geo = $this->geoLocate($params->address1, $params->city, $params->province);

            $params->lat = $geo['lat'];
            $params->lng = $geo['lng'];
        }

        return (new $this->model)
            ->fill([
                'owner_type'    => get_class($owner),
                'owner_id'      => $owner->id,

                'type'          => isset($params->type) ? $params->type : null,
                'referrer'      => \Request::path(),
                
                'address1'      => $params->address1,
                'address2'      => isset($params->address2) ? $params->address2 : null,
                'neighbourhood' => isset($params->neighbourhood) ? $params->neighbourhood : null,
                'postal'        => $params->postal,
                'city'          => $params->city,
                'province'      => $params->province,
                'country'       => $params->country,
                'lat'           => $params->lat,
                'lng'           => $params->lng
            ]);
    }

    // not sure that we need this any more
    public function getCities($area = null)
    {
        $query = $this->model
            ->select("city")
            ->has("studios")
            ->distinct();

        if ($area) {
            $query->whereHas("studios", function ($q) use ($area)
            {
                $q->whereHas("tags", function ($qq) use ($area)
                {
                    $qq->locations()->whereName($area);
                });
            });
        }

        return $query->orderBy("city", "ASC")->get();
    }

    public function matchLocations (Location $location, $params)
    {
        $same = true;

        if (!empty($params['address1']))
            if ($location->address1 != $params['address1'])
                $same = false;

        if (!empty($params['address2']))
            if ($location->address2 != $params['address2'])
                $same = false;

        if (!empty($params['postal']))
            if ($location->postal != $params['postal'])
                $same = false;

        if (!empty($params['city']))
            if ($location->city != $params['city'])
                $same = false;

        if (!empty($params['province']))
            if ($location->province != $params['province'])
                $same = false;

        if (!empty($params['country']))
            if ($location->country != $params['country'])
                $same = false;

        if (!empty($params['lat']))
            if ($location->lat != $params['lat'])
                $same = false;

        if (!empty($params['lng']))
            if ($location->lng != $params['lng'])
                $same = false;

        return $same;
    }
}

<?php 

namespace App\Locations;

use League\Fractal\TransformerAbstract;

class LocationTransformer extends TransformerAbstract
{
    public function transform (Location $location)
    {
        $result = [
            'type'        => $location->type,
            'address1'     => $location->address1,
            'address2'     => $location->address2,
            'neighbourhood' => $location->neighbourhood,
            'province'     => $location->province,
            'postal'       => $location->postal,
            'city'         => $location->city,
            'country'      => $location->country,
            'lat'          => $location->lat,
            'lng'          => $location->lng,
            'full'         =>  $location->fullAddress,
            'taxrate'      => $location->taxrate,
        ];

        if ($location->street)
            $result["street"] = $location->street;
        
        if ($location->street_number)
            $result["street_number"] = $location->street_number;

        return $result;
    }
}

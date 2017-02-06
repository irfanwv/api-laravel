<?php

namespace App;

use App;

use GeoIp2\WebService\Client;

class MaxMind
{
    protected $client;

    public function __construct ()
    {
        $this->client = new Client(106414, 'unVWS5Kliv1w');
    }

    public static function geo ($ip)
    {
        $caller = new MaxMind;

        return $caller->geoip($ip);
    }

    public function geoip ($ip)
    {
        if (App::environment() !== 'production') {
            return [
                "city"      => "Oshawa",
                "province"  => "Ontario",
                "country"   => "Canada",
                "postal"    => "L1G",
                "lat"       => 43.9233,
                "lng"       => -78.8684,
            ];
        }

        $result = $this->client->city($ip);

        return [
            'city'      =>  $result->city->name,
            'province'  =>  $result->mostSpecificSubdivision->name,
            'country'   =>  $result->country->name,
            'postal'    =>  $result->postal->code,
            'lat'       =>  $result->location->latitude,
            'lng'       =>  $result->location->longitude,
        ];
    }
}

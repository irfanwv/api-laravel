<?php namespace App\Locations\Events;


use App\Locations\Location;

class NewLocationCreated
{
    public $location;

    public function __construct(Location $location)
    {
        $this->location = $location;
    }
}
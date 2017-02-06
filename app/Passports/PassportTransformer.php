<?php

namespace App\Passports;

use League\Fractal\TransformerAbstract;

use App\Studios\StudioTransformer;
use App\Tags\TagTransformer;

class PassportTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'city', 'studiosLeft', 'studios'
    ];

    protected $defaultIncludes = [
        'city'
    ];

    protected $city;

    public function transform (Passport $passport)
    {
        $this->city = $passport->city()->withTrashed()->first();

        $params = [
            'number'        =>  $passport->number,
            'city_name'     =>  $this->city->name,
            'activated_at' =>   $passport->activated_at->timestamp,
            'expires_at'   =>   $passport->expires_at->timestamp,
            'expired'       =>  $passport->isExpired()
        ];

        return $params;
    }

    public function includeCity (Passport $passport)
    {
        // $city = $passport->city()->withTrashed()->first();

        return ($this->city) ? $this->item($this->city, new TagTransformer) : null;
    }

    public function includeStudiosLeft (Passport $passport)
    {
        $studios = \App\Studios\Studio::byArea ($this->city->name)
            ->hasClasses()
            ->whereDoesntHave('passports', function ($q) use ($passport)
            {
                $q->where('id', $passport->id);
            })
            ->orderBy('name', 'asc')
            ->get();

        return $this->collection ($studios, new StudioTransformer);
    }

    public function includeStudios (Passport $passport)
    {
        $studios = $passport->studios()->orderBy('name', 'asc')->get();

        return $this->collection ($studios, new StudioTransformer);
    }
}

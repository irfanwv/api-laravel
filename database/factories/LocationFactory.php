<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Locations\Location::class, function ($faker)
{
    return [
        'address1'  =>  $faker->streetAddress,
        'address2'  =>  '',
        'postal'    =>  $faker->postcode,
        'city'      =>  $faker->city,
        'province'  =>  $faker->state,
        'country'   =>  $faker->country,
        'lat'       =>  $faker->latitude,
        'lng'       =>  $faker->longitude,
    ];
});

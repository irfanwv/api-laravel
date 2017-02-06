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

$factory->define(App\Studios\Studio::class, function ($faker)
{
    return [
        'name'      	=> 	$faker->company,
        'email' 		=>	$faker->freeEmail,
        'phone'			=>	$faker->phoneNumber(),
        'website'       =>  $faker->domainName(),
    ];
});

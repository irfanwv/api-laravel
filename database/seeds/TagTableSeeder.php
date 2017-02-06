<?php

use Illuminate\Database\Seeder;

class TagTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\Tags\Tag::create(['name' => 'admin', 'type' => 'App\Users\User']);
        App\Tags\Tag::create(['name' => 'customer', 'type' => 'App\Users\User']);
        App\Tags\Tag::create(['name' => 'studio', 'type' => 'App\Users\User']);

        App\Tags\Tag::create(['name' => 'Calgary', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'Edmonton', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'Montreal', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'Ottawa', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'Toronto', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'Vancouver', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'Victoria', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'Atlanta', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'Chicago', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'Denver', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'Los Angeles', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'Miami', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'New York', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'Portland', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'Raleigh-Durham', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'San Diego', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'San Francisco', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'Seattle', 'type' => 'App\Studios\Studio']);
        App\Tags\Tag::create(['name' => 'Tampa Bay', 'type' => 'App\Studios\Studio']);
    }
}

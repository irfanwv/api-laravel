<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $faker = Faker\Factory::create();

        App\Users\User::create([
                'first_name'    =>  'Adam',
                'last_name'     =>  'Kunz',
                'email'         =>  'adam@impulsesolutions.ca',
                'active'        =>  true,
                'password'      =>  bcrypt('impulse1')
            ])  
            ->tags()
            ->attach(1);

        App\Users\User::create([
                'first_name'    =>  'Rajen',
                'last_name'     =>  'Gandhi',
                'email'         =>  'info@impulsesolutions.ca',
                'active'        =>  true,
                'password'      =>  bcrypt('impulse1')
            ])
            ->tags()
            ->attach(1);

        // if (App::environment('production')) return true;

        // factory('App\Users\User', 30)->create()
        //     ->each(function ($user, $key) use ($faker)
        //     {
        //         $user->tags()->attach(2);
        //         $user->customer()->save(factory('App\Customers\Customer')->make());
        //     });   
    }
}
                //     $user->tags()->attach(3);
                    
                //     $studio = factory('App\Studios\Studio')->make();
                //     $user->studio()->save($studio);
                    
                //     $studio->tags()->attach($faker->numberBetween(4, 18));
                    
                //     $location = factory('App\Locations\Location')->make();
                //     $studio->locations()->save($location);
                //     $studio->location_id = $location->id;
                //     $studio->save();

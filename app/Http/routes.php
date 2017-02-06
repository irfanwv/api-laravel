<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| We're using the dingo/api package to serve API requests.
| https://github.com/dingo/api
|
*/

//$api = app('api.router');
$api = app('Dingo\Api\Routing\Router');
//Route::get('test','testController@show');
Route::post('index','jsonController@index');
//Route::get('teststats','testController@stats');
header('Access-Control-Allow-Origin: *');
header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );
Route::get('work','ContentController@work');
//Route::post('product/create', 'ProductController@store');
//Route::get('product/all', 'ProductController@show');
//Route::post('customerspoint/add', 'CustomerPointsController@store');
//Route::post('customerspoint/add', 'CustomerPointsController@show');
//Route::get('neworder', 'NewOrderController@show');
Route::post('referral', 'ReferralController@store');

$api->version('v1', function ($api)
{
    // my test func
    $api->get('stats', 'App\Http\Controllers\testController@stats');
    //app('Dingo\Api\Routing\UrlGenerator')->version('v1')->route('ContentController.work');
   // $api->get('work', 'App\Http\Controllers\ContentController@work');

    // general site info
    //$api->get('stats', 'App\Http\Controllers\ContentController@stats');
    $api->get('geoip', 'App\Http\Controllers\ContentController@geoip');
    $api->get('validate/email', 'App\Http\Controllers\ValidationController@email');
    $api->post('contact', 'App\Http\Controllers\ContentController@contact');
    $api->post('newsletter', 'App\Http\Controllers\ContentController@newsletter');

    // publicly available data
    $api->post('studios/register', 'App\Http\Controllers\StudioController@register');
    $api->get('studios/{slug}', 'App\Http\Controllers\StudioController@show');
    $api->get('studios', 'App\Http\Controllers\StudioController@search');

    $api->get('locations', 'App\Http\Controllers\StudioController@locations');
    $api->get('cities', 'App\Http\Controllers\CityController@index');
    $api->get('types', 'App\Http\Controllers\TagController@yogaTypes');
    
    $api->post('product/add', 'App\Http\Controllers\ProductController@store');
    $api->get('product/list', 'App\Http\Controllers\ProductController@show');
    $api->post('customerspoint/add', 'App\Http\Controllers\CustomerPointsController@store');
    $api->post('customerspoint/show', 'App\Http\Controllers\CustomerPointsController@show');
    /**
     * Authorization and Authentication routes
     *
     */

    // deprecated, but left on in case someone finds an old email or something
    $api->put('auth/activate', 'App\Http\Controllers\Auth\AuthController@activate');
    // alias for /customers
    $api->post('auth/register', 'App\Http\Controllers\CustomerController@store');
    // refresh an expired token
    $api->post('auth/refresh', 'App\Http\Controllers\Auth\AuthController@refresh');
    // turn a legacy login into a new login
    $api->post('auth/{uid}/configure', 'App\Http\Controllers\Auth\AuthController@configure');
    // turn credentials into a new token
    $api->post('auth', 'App\Http\Controllers\Auth\AuthController@authenticate');
    // request a password reset code
    $api->post('password/email', 'App\Http\Controllers\Auth\PasswordController@email');
    // redeem a password reset code
    $api->post('password/reset', 'App\Http\Controllers\Auth\PasswordController@reset');
    // request a username reminder
    $api->post('username/email', 'App\Http\Controllers\Auth\PasswordController@username');

    $api->group(['prefix' => 'reminders'], function ($api)
    {
        $api->post('', 'App\Http\Controllers\ReminderController@store');
        $api->delete('{email}', 'App\Http\Controllers\ReminderController@destroy');
    });

    /*
        Remove from protected (Start)
    */
       $api->group(['prefix' => 'reports'], function ($api)
        {
            $api->get('cards', 'App\Http\Controllers\ReportController@cards');
        });
       
    
    /*
        Remove from protected (End)
    */
    /*
     * Referral code status
     */
        $api->post('referral/status', 'App\Http\Controllers\ReferralController@status');
        $api->post('referral/verify', 'App\Http\Controllers\ReferralController@verify');
        
    /**
     * Protected routes
     *
     */
    $api->group(['protected' => true], function ($api)
    {
        // checkout
        $api->post('checkout/{uid}', 'App\Http\Controllers\OrderController@store');
        $api->post('checkout', 'App\Http\Controllers\OrderController@store');
        $api->get('checkout/coupon', 'App\Http\Controllers\OrderController@verify');
        

        $api->get('profile', 'App\Http\Controllers\Auth\AuthController@profile');
        // alias for /users/{uid} but assumes your own profile
        $api->put('profile', 'App\Http\Controllers\UserController@update');
        $api->put('profile/password', 'App\Http\Controllers\Auth\PasswordController@update');

        $api->get('prices', 'App\Http\Controllers\PriceController@index');
        $api->post('prices', 'App\Http\Controllers\PriceController@store');
        $api->put('prices/{cid}', 'App\Http\Controllers\PriceController@update');

        /**
         * General user functionality
         */
        $api->group(['prefix' => 'users'], function ($api)
        {
            // user specific
            $api->group(['prefix' => '{uid}'], function ($api)
            {
                $api->post('activate', 'App\Http\Controllers\UserController@activate');
                $api->post('deactivate', 'App\Http\Controllers\UserController@deactivate');
                $api->post('resend', 'App\Http\Controllers\UserController@sendActivationLetter');
                
                $api->put('password', 'App\Http\Controllers\Auth\PasswordController@update');
                $api->put('', 'App\Http\Controllers\UserController@update');
                $api->get('', 'App\Http\Controllers\UserController@show');
            });

            // full text account search
            $api->get('', 'App\Http\Controllers\UserController@index');
        });

        /**
         * Customers
         */
        $api->group(['prefix' => 'customers'], function ($api)
        {
            // specific customers
            $api->group(['prefix' => '{cid}'], function ($api)
            {
                // passports for specific customers
                $api->group(['prefix' => 'passports'], function ($api)
                {
                    // specific passports for specific customers
                    $api->group(['prefix' => '{ppnum}'], function ($api)
                    {
                        // (manage) renew a passport for a customer // dupe of /passports/{ppnum}/renew
                        $api->put('renew', 'App\Http\Controllers\CustomerPassportsController@renew');
                        // (manage) activate for given customer
                        $api->post('activate', 'App\Http\Controllers\CustomerPassportsController@activate');
                    });
                });

                $api->put('billing', 'App\Http\Controllers\CustomerController@billing');
                $api->put('shipping', 'App\Http\Controllers\CustomerController@shipping');
                $api->put('', 'App\Http\Controllers\CustomerController@update');
            });

            $api->post('', 'App\Http\Controllers\CustomerController@store');
            $api->get('', 'App\Http\Controllers\CustomerController@index');
        });

        /**
         * Studios
         */
        $api->group(['prefix' => 'studios'], function ($api)
        {
            // use passport at studio
            $api->post('{sid}/passport/{num}', 'App\Http\Controllers\PassportController@update');
            // unuse passport at studio
            $api->delete('{sid}/passport/{num}', 'App\Http\Controllers\PassportController@undo');
            // add a type tag to a studio
            $api->post('{sid}/types/{num}', 'App\Http\Controllers\StudioController@addType');
            // remove a type tag from a studio
            $api->delete('{sid}/types/{num}', 'App\Http\Controllers\StudioController@removeType');
            // restore a disabled studio
            $api->put('{sid}/restore', 'App\Http\Controllers\StudioController@restore');
            // change a studio address
            $api->put('{sid}/address', 'App\Http\Controllers\StudioController@address');
            // change studio details
            $api->put('{sid}', 'App\Http\Controllers\StudioController@update');
            // disable a studio
            $api->delete('{sid}', 'App\Http\Controllers\StudioController@destroy');
            // create a new studio
            $api->post('', 'App\Http\Controllers\StudioController@store');            
        });

        /**
         * Shipping
         */
        $api->group(['prefix' => 'shipping'], function ($api)
        {
            $api->get('pending', 'App\Http\Controllers\MailroomController@index');
        });

        /**
         * Orders
         */
        $api->group(['prefix' => 'orders'], function ($api)
        {
            $api->put('{id}', 'App\Http\Controllers\OrderController@update');
            $api->put('{id}/shipping', 'App\Http\Controllers\OrderController@shipping');
            $api->delete('{id}', 'App\Http\Controllers\OrderController@destroy');
        });

        /*
         * New Order
         */  
        $api->post('neworder', 'App\Http\Controllers\NewOrderController@create');
        $api->get('neworder', 'App\Http\Controllers\NewOrderController@show');
        $api->post('neworder/update', 'App\Http\Controllers\NewOrderController@update');
        $api->post('neworder/cancel', 'App\Http\Controllers\NewOrderController@cancel');
        
        /*
         *Referral
         */
        $api->post('referral', 'App\Http\Controllers\ReferralController@store');

         //Route::post('referral', 'ReferralController@store');

        /** 
         * General passport functionality
         */
        $api->group(['prefix' => 'passports'], function ($api)
        {
            // (manage) manage passport details
            $api->put('{ppnum}/manage', 'App\Http\Controllers\PassportController@manage'); 
            // (manage) force renew // copied this to customer specific passports section
            $api->put('{ppnum}/renew', 'App\Http\Controllers\PassportController@renew');
            // activate for current user
            $api->post('{ppnum}/activate', 'App\Http\Controllers\PassportController@activate');
            // configure a card for an area and optionally activate it for a new user
            $api->post('{ppnum}/configure', 'App\Http\Controllers\PassportController@configure');
            // use the passport at a studio
            $api->put('{ppnum}', 'App\Http\Controllers\PassportController@update'); 
            // give up a card
            $api->delete('{ppnum}/abandon', 'App\Http\Controllers\PassportController@abandon'); 
            // (manage) reclaim
            $api->delete('{ppnum}', 'App\Http\Controllers\PassportController@destroy'); 
            // (manage) input an array of new card numbers
            $api->post('', 'App\Http\Controllers\PassportController@create'); 
        });

        /**
         * Tagging system
         */
        $api->group(['prefix' => 'tags'], function ($api)
        {
            $api->get('', 'App\Http\Controllers\TagController@index');
            $api->post('', 'App\Http\Controllers\TagController@store');
            $api->delete('{id}', 'App\Http\Controllers\TagController@destroy');
        });

        /**
         * City management, probably could have put this in with tags but this is more specific
         * and will make it easier to change later.
         */
        $api->group(['prefix' => 'cities'], function ($api)
        {
            // $api->get('') // is public
            $api->post('', 'App\Http\Controllers\CityController@store');
            $api->delete('{id}', 'App\Http\Controllers\CityController@destroy');
        });

        //$api->group(['prefix' => 'reports'], function ($api)
        //{
        //    $api->get('cards', 'App\Http\Controllers\ReportController@cards');
        //});
        
        
    });
});

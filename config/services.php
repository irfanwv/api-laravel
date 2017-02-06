<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => 'www.passporttoprana.com',
        'secret' => 'key-3353ca1096356fa3b843d4381ab3a89e',
    ],

    'mandrill' => [
        'secret' => '',
    ],

    'ses' => [
        'key'    => '',
        'secret' => '',
        'region' => 'us-east-1',
    ],

    'stripe' => [
        'model'  => App\Customers\Customer::class,
        'secret' => env('STRIPE_SECRET', 'sk_test_kI2KO6c1BCNNIU429jkMNm96'),
        // 'secret' => 'sk_live_KUyOasmtP2HFsIy1O4vC6AxD',
    ],

];

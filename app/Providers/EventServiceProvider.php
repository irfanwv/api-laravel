<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\CustomerHasRegistered' => [
            // 'App\Listeners\NotifyAdminAboutRegistration',
            'App\Listeners\SendCustomerWelcomeLetter',
        ],

        'App\Events\StudioWasCreated' => [
            // 'App\Listeners\NotifyAdminAboutNewStudio',
            'App\Listeners\SendStudioWelcomeLetter',
        ],

        'App\Events\OrderWasCreated' => [
            'App\Listeners\SendReceipt',
        ],

        'App\Events\OrderWasFulfilled'  =>  [
            'App\Listeners\NotifyCustomerAboutShipment',
        ],

        'App\Events\PassportWasActivated'   =>  [
            'App\Listeners\CardActivationNotice',
        ],

        'App\Events\PassportIsExpiring' =>  [
            'App\Listeners\RemindCustomerAboutExpiry',
        ],

        'App\Events\PassportHasExpired' =>  [
            'App\Listeners\NotifyCustomerAboutExpiry',
        ],

        'App\Events\LegacyLoginConverted' => [
            'App\Listeners\WelcomeLegacyUser'
        ]
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        //
        \Event::listen('tymon.jwt.invalid', function ($event)
        {
            throw $event;
        });
    }
}

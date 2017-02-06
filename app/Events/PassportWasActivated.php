<?php

namespace App\Events;

use App\Passports\Passport;

use Notifier;

class PassportWasActivated extends Event
{
    /**
     * 
     */
    public $passport;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct (Passport $passport)
    {
        //
        $this->passport = $passport;

        $user = $passport->owner->user;

        Notifier::notify ("Passport #$passport->number has been activated by $user->first_name $user->last_name.")
            -> via ('log')
            -> via ('slack');
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['events'];
    }
}

<?php

namespace App\Events;

use App\Passports\Passport;

class PassportHasExpired extends Event
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

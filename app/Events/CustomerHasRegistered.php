<?php

namespace App\Events;

use App\Users\User;

use Notifier;

class CustomerHasRegistered extends Event
{
    /**
     * 
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct (User $user)
    {
        //
        $this->user = $user;

        Notifier::notify ("$user->first_name $user->last_name has registered a new customer account.")
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

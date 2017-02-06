<?php

namespace App\Events;

use App\Studios\Studio;

class StudioWasCreated extends Event
{
    /**
     *
     */
    public $studio;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Studio $studio)
    {
        //
        $this->studio = $studio;
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

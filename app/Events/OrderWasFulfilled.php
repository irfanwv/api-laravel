<?php

namespace App\Events;

use App\Orders\Order;

class OrderWasFulfilled extends Event
{
    /**
     * 
     */
    public $order;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct (Order $order)
    {
        //
        $this->order = $order;
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

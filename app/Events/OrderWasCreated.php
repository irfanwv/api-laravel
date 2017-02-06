<?php

namespace App\Events;

use App\Orders\Order;

use Notifier;

class OrderWasCreated extends Event
{
    /**
     * 
     */
    public $order;

    /**
     * 
     */
    public $cart;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct (Order $order, $cart = [])
    {
        $this->order = $order;
        $this->cart = $cart;

        $user = $order->customer->user;

        Notifier::notify ("$user->first_name $user->last_name has checked out for $".number_format($order->total, 2).".")
            ->via ('log')
            ->via ('slack');
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

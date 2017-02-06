<?php

namespace App\Listeners;

use Log;
use App\Slacker;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Events\OrderWasFulfilled;
use App\Mailers\CustomerMailer;

class NotifyCustomerAboutShipment implements ShouldQueue
{
    /**
     * The selected Mailer instance
     */
    protected $mailer;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct (CustomerMailer $mailer)
    {
        //
        $this->mailer = $mailer;
    }

    /**
     * Handle the event.
     *
     * @param  OrderWasFulfilled  $event
     * @return void
     */
    public function handle(OrderWasFulfilled $event)
    {
        $order = $event->order;

        $this->mailer->orderShipped($order);

        $notice = 'Sent shipment notice to ' . $order->customer->user->email;

        \App\Notifier::notify ($notice)
            ->via ('log')
            ->via ('slack');
    }
}

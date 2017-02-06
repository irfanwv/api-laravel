<?php

namespace App\Listeners;

use Notifier;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Events\OrderWasCreated;
use App\Mailers\CustomerMailer;

class SendReceipt implements ShouldQueue
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
     * @param  OrderWasCreated  $event
     * @return void
     */
    public function handle (OrderWasCreated $event)
    {
        $order = $event->order;
        $cart = $event->cart;

        $this->mailer->receipt ($order, $cart);

        $user = $order->customer->user;

        Notifier::notify ("Mailed a receipt to $user->email.")
            ->via ('log')
            ->via ('slack');
    }
}

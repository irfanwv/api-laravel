<?php

namespace App\Listeners;

use Notifier;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Events\CustomerHasRegistered;
use App\Mailers\CustomerMailer;

class SendCustomerWelcomeLetter implements ShouldQueue
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
     * @param  CustomerHasRegistered  $event
     * @return void
     */
    public function handle (CustomerHasRegistered $event)
    {
        $user = $event->user;

        $this->mailer->welcome ($user);

        Notifier::notify ("Sent a welcome letter to $user->email")
            ->via ('log')
            ->via ('slack');
    }
}

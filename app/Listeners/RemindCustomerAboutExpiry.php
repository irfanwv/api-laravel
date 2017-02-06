<?php

namespace App\Listeners;

use Log;
use App\Slacker;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Events\PassportIsExpiring;
use App\Mailers\CustomerMailer;

class RemindCustomerAboutExpiry implements ShouldQueue
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
     * @param  PassportIsExpiring  $event
     * @return void
     */
    public function handle (PassportIsExpiring $event)
    {
        $passport = $event->passport;

        $this->mailer->cardExpiring($passport);

        $notice = 'Sent expiry reminder to ' . $passport->owner->user->email;

        \App\Notifier::notify ($notice)
            ->via ('log')
            ->via ('slack');
    }
}

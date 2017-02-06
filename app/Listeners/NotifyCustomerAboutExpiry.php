<?php

namespace App\Listeners;

use Log;
use App\Slacker;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Events\PassportHasExpired;
use App\Mailers\CustomerMailer;

class NotifyCustomerAboutExpiry implements ShouldQueue
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
     * @param  PassportHasExpired  $event
     * @return void
     */
    public function handle (PassportHasExpired $event)
    {
        $passport = $event->passport;

        $this->mailer->cardExpired($passport);

        $notice = 'Sent expired notification to ' . $passport->owner->user->email;

        \App\Notifier::notify ($notice)
            ->via ('log')
            ->via ('slack');
    }
}

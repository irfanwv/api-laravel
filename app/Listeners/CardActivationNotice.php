<?php

namespace App\Listeners;

use Notifier;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Events\PassportWasActivated;
use App\Mailers\CustomerMailer;

class CardActivationNotice implements ShouldQueue
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
     * @param  PassportWasActivated  $event
     * @return void
     */
    public function handle (PassportWasActivated $event)
    {
        $passport = $event->passport;

        $this->mailer->cardActivated ($passport);

        $user = $passport->owner->user;

        Notifier::notify ("Mailed an activation confirmation to $user->email.")
            ->via ('log')
            ->via ('slack');
    }
}

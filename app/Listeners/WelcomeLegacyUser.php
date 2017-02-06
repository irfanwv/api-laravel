<?php

namespace App\Listeners;

use Log;
use App\Notifier;

use App\Events\LegacyLoginConverted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Mailers\UserMailer;

class WelcomeLegacyUser
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
    public function __construct (UserMailer $mailer)
    {
        //
        $this->mailer = $mailer;
    }

    /**
     * Handle the event.
     *
     * @param  LegacyLoginConverted  $event
     * @return void
     */
    public function handle (LegacyLoginConverted $event)
    {
        $this->mailer->sendLegacyWelcome ($event->user);

        Notifier::notify ('Legacy login converted: ' . $event->user->email)
            ->via ('log')
            ->via ('slack');
    }
}

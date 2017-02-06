<?php

namespace App\Listeners;

use Log;
use App\Slacker;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Events\StudioWasCreated;
use App\Mailers\StudioMailer;

class SendStudioWelcomeLetter implements ShouldQueue
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
    public function __construct (StudioMailer $mailer)
    {
        //
        $this->mailer = $mailer;
    }

    /**
     * Handle the event.
     *
     * @param  StudioWasCreated  $event
     * @return void
     */
    public function handle (StudioWasCreated $event)
    {
        $studio = $event->studio;
        $user = $studio->owner;

        $this->mailer->activation($studio);

        $notice = 'Sent an activation letter to ' . $user->email;

        \App\Notifier::notify ($notice)
            ->via ('log')
            ->via ('slack');
    }
}

<?php

namespace App\Listeners;

use Log;
use App\Slacker;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Events\CustomerHasRegistered;
use App\Mailers\AdminMailer;

class NotifyAdminAboutRegistration implements ShouldQueue
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
    public function __construct (AdminMailer $mailer)
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
    public function handle(CustomerHasRegistered $event)
    {
        //
    }
}

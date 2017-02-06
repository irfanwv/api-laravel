<?php 
namespace App\Mailers;

use Log;

use App\Studios\Studio;
use App\Users\User;

class AdminMailer extends Mailer
{
    public function sendNewCustomerNotification (User $user)
    {
        $subject = 'A new customer has registered with us!';

        $view = 'emails.admin.registration';

        $data = $user->toArray();

        $this->sendTo($user, $subject, $view, $data);
    }
}

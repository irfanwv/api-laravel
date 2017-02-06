<?php 
namespace App\Mailers;

use Log;

use App\Users\User;
use App\Reminders\Reminder;

class UserMailer extends Mailer
{
    public function sendLegacyWelcome (User $user)
    {
        $subject = 'Welcome to the new Passport to Prana.';
        $view = 'emails.user.legacy';

        $params = [
            'name' => $user->name,
            'email' => $user->email,
            'subject' => $subject,
        ];

        $this->sendTo($user, $subject, $view, $params);
    }

    public function sendUsernameReminder (User $user)
    {
        $subject = 'Your Passport to Prana Login.';
        $view = 'emails.user.username';
        $params = [
            'name' => $user->first_name,
            'email' => $user->email,
            'subject' => $subject
        ];

        $this->sendTo ($user, $subject, $view, $params);
    }

    public function password (User $user)
    {
        $subject = 'Password reset requested.';
        $view = 'emails.user.password';
        
        $params = $user->toArray();
        $params['title'] = $subject;
        $params['password_reset_url'] = env('FRONT_DOMAIN') . '/password?reset=' . $user->getPasswordResetCode();

        $this->sendTo($user, $subject, $view, $params);
    }

    public function sendContactForm ($params)
    {
        $subject = 'You\'ve received a message.';
        $view = 'emails.contact';
        
        $params['title'] = $subject;

        $from = $params['email'];
        $this->sendTo($this->admin, $subject, $view, $params, null, $from);
    }

    public function sendFaqForm ($params)
    {
        $subject = 'You\'ve received a new question.';
        $view = 'emails.contact';
        
        $params['title'] = $subject;

        $from = $params['email'];
        $this->sendTo($this->admin, $subject, $view, $params, null, $from);
    }

    public function sendNewsletterForm ($params)
    {
        $subject = 'New newsletter subscriber';
        $view = 'emails.contact';
        
        $params['title'] = $subject;
        $params['content'] = 'I\'d like to subscribe to your newsletter.';

        $from = $params['email'];
        $this->sendTo($this->admin, $subject, $view, $params, null, $from);
    }

    public function sendMaintenanceForm ($params)
    {
        $subject = 'A studio wants to submit attendance while the site is in maintenance.';
        $view = 'emails.contact';

        $params['title'] = $subject;
        $params['email'] = $params['studio'];

        $from = $params['email'];
        $this->sendTo($this->admin, $subject, $view, $params, null, $from);
    }

    public function sendGiftReminder (Reminder $reminder)
    {
        $subject = 'A studio wants to submit attendance while the site is in maintenance.';
        $view = 'emails.gift_reminder';

        $params = [
            'title' => $subject,
            'name' => $reminder->first_name,
        ];

        // we actually are not typecasting this so any object with an email attribute should work
        $this->sendTo($reminder, $subject, $view, $params);
    }
}

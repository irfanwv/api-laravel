<?php

namespace App\Mailers;

use App;

use Illuminate\Mail\Mailer as Mail;

use App\Users\UserRepository;

abstract class Mailer
{
    /**
     * @var Mail
     */
    protected $mail;

    public function __construct(Mail $mail, UserRepository $users)
    {
        $this->mail = $mail;
    }

    /**
     * Bootstrap the email with common details
     *
     * @param $user
     * @param $subject
     * @param $view
     * @param $data
     */
    public function sendTo ($user, $subject, $view, $data = [], $cc = null, $from = null)
    {
        ini_set('xdebug.max_nesting_level', 200); // probably due to the sync queue driver

        if (env('APP_ENV') !== 'production') {
            $user = $users->find(env('ADMIN_ID', 1));
        }

        $this->mail->queue(
            $view,
            $data,
            function ($message) use ($user, $subject, $cc, $from)
            {
                $message->to($user->email)->subject($subject);

                if ($from) { $message->from($from); }

                if ($cc) { $message->cc($cc->email); }
            });
    }
} 

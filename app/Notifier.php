<?php

namespace App;

use Carbon;
// use Services_Twilio;
use Slack;

class Notifier {
    
    // protected $twilio;
    // protected $twilio_from = '6473607171';
    // protected $twilio_sid = 'ACa35217e4ed5fa6aa0bcf4e53f0c279a1';
    // protected $twilio_token = '1f3054e2a2806ff15750c82b1efcfb19';

    protected $logger;
    protected $log_path;

    public $to;
    public $message;

    public function __construct ()
    {
        $this->log_path = storage_path('logs/notifications/'.Carbon::today()->format('Y-m-d').'.log');
    }

    public static function notify ($message, $to = null)
    {
        $caller = new Notifier;

        $caller->to = $to;

        if (env('APP_ENV') !== 'production') {
            $message = '[' . strtoupper(env('APP_ENV')) . '] ' . $message;
        }

        $caller->message = $message;

        return $caller;
    }

    public function via ($method, &$results = [])
    {
        $results[] = $this->$method ($this->message, $this->to);

        return $this;
    }






    // public function sms ($message, $to)
    // {
    //     if (!$this->twilio) $this->twilio();

    //     // maybe verify that we were passed a phone number

    //     return $this->twilio
    //         ->account
    //         ->messages
    //         ->sendMessage ($this->twilio_from, $this->to, $this->message);
    // }

    public function slack ($message)
    {
        $message = '['. Carbon::now()->format("G:i:s") . '] ' . $message;

        if (env('APP_ENV') === 'adam') {
            // $to = '@adam';
            return true; // don't bug me about it, i'll figure it out
        } else {
            $to = '#app_alerts';
        }

        return Slack::to($to)->send($message);
    }

    public function log ($message)
    {
        if (!$this->logger) $this->logger();

        return $this->logger->addNotice($message);
    }








    public function logger ()
    {
        $this->logger = new \Monolog\Logger('Notifications');
        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler($this->log_path, \Monolog\Logger::NOTICE));
    }

    // public function twilio ()
    // {
    //     $this->twilio = new Services_Twilio($this->twilio_sid, $this->twilio_token);
    // }
}
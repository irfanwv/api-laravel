<?php

namespace App;

use Carbon;
use Slack;

class Slacker
{
    public static function send ($notice)
    {
        if (env('APP_ENV') !== 'production') {
            $notice = '[' . strtoupper(env('APP_ENV')) . '] ' . $notice;
        }

        if (env('APP_ENV') !== 'adam') {
            Slack::to('#app_alerts')->send('['. \Carbon::now()->format("G:i:s") . '] ' . $notice);
        } else {
            // Slack::to('@adam')->send('['. \Carbon::now()->format("G:i:s") . '] ' . $notice);
        }

        return true;
    }
}

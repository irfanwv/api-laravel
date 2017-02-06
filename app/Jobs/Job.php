<?php

namespace App\Jobs;

use Storage;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\DispatchesJobs;

abstract class Job
{
    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    |
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The trait included with the class
    | provides access to the "queueOn" and "delay" queue helper methods.
    |
    */

    use Queueable, DispatchesJobs;

    public function writeLog ($text, $log)
    {
        $write = function ($t, $l)
        {
            if (Storage::exists($l.'.log')) {
                Storage::append($l.'.log', $t);
            } else {
                Storage::put($l.'.log', $t);
            }
        };
        
        if (is_array ($text)) {
            foreach ($text as $value) {
                $write ($value, $log);
            }
        } else {
            $write ($text, $log);
        }

        $write ('', $log);
    }
}

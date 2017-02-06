<?php

namespace App\Jobs;

use DB;

use App\Jobs\Job;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;


class ImportPassportsJob extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  User  $user
     * @return void
     */
    public function __construct ()
    {
    }

    /**
     * Execute the job.
     *
     * @param  Mailer  $mailer
     * @return void
     */
    public function handle()
    {
        $pps = \Impulse\Pivot\Passport::with(['link.city', 'link.studios'])
            ->orderBy('pp_id', 'asc')
            ->get();

        return $pps->each(function ($passport)
        {
            $this->dispatch((new ImportPassportJob($passport))->onQueue('imports'));
        });
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed ()
    {
        $this->writeLog(json_encode($this->import), 'passport_jobs');
    }
}

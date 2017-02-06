<?php

namespace App\Console\Commands;

use Carbon\Carbon;

use Event;

use Artisan;
use Illuminate\Console\Command;

use App\Passports\Passport;
use App\Passports\PassportRepository;

use App\Events\PassportIsExpiring;
use App\Events\PassportHasExpired;

class Passwords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'p2p:passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset passwords.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct ()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (env('APP_ENV') === 'production') { throw new \Exception ('You must be nuts.'); }

        $users = \App\Users\User::orderBy('id', 'asc')
            ->whereHas('tags', function ($q)
            {
                $q->where(function ($qq)
                {
                    $qq->where('name', 'admin')
                      ->orWhere('name', 'studio');
                });
            })
            ->get();

        $bar = $this->output->createProgressBar($users->count());

        foreach ($users as $user) {
            $user->password = bcrypt('impulse1');
            $user->save();
            $bar->advance();
        }

        $bar->finish();
        $this->info('done.');
    }
}

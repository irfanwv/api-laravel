<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\ImportOldStudios::class,
        \App\Console\Commands\LegacyImport::class,
        \App\Console\Commands\Generate::class,
        \App\Console\Commands\RepositoryGenerator::class,
        \App\Console\Commands\TransformerGenerator::class,

        \App\Console\Commands\Reload::class,
        \App\Console\Commands\Passwords::class,

        \App\Console\Commands\PassportNotifications::class,
        \App\Console\Commands\SendReminders::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule (Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        $schedule->command('p2p:notifications')->daily();
    }
}

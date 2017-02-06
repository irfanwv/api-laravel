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

use App\Mailers\CustomerMailer;

class PassportNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'p2p:notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the expiration status of all active passports.';

    /**
     * The Passport Repository
     *
     * @var App\Passports\PassportRepository
     */
    protected $passports;

    protected $mailer;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct (PassportRepository $passports, CustomerMailer $mailer)
    {
        parent::__construct();

        $this->passports = $passports;
        $this->mailer = $mailer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tips = 0;
        $expiring = 0;
        $expired = 0;

        Passport::active()
            ->where('activated_at', '>=', Carbon::today()->subDays(2))
            ->where('activated_at', '<', Carbon::today()->subDays(1))
            ->get()
            ->each(function (Passport $passport) use (&$tips)
            {
                $this->mailer->tips($passport);
                $tips++;
            });

        Passport::active()
            ->where('expires_at', '<', Carbon::today()->addDays(31))
            ->where('expires_at', '>=', Carbon::today()->addDays(30))
            ->get()
            ->each(function (Passport $passport) use (&$expiring)
            {
                Event::fire(new PassportIsExpiring($passport));
                $expiring++;
            });

        Passport::where('expires_at', '<', Carbon::tomorrow())
            ->where('expires_at', '>=', Carbon::today())
            ->get()
            ->each(function (Passport $passport) use (&$expired)
            {
                Event::fire(new PassportHasExpired($passport));
                $expired++;
            });

        $this->info("$tips Tips letters, $expiring Expiring notices, $expired Expired notices.");
    }
}

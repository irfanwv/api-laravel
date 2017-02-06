<?php

namespace App\Console\Commands;

use Carbon\Carbon;

use Artisan;
use Illuminate\Console\Command;

use App\Reminders\ReminderRepository;
use App\Mailers\UserMailer;

class SendReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'p2p:reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Reminders';

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
    public function handle (ReminderRepository $reminders, UserMailer $mailer)
    {
        $notes = $reminders->search(null, ['forGifts', 'future']);
        
        $bar = $this->output->createProgressBar ($notes->count());

        $notes->each (function ($reminder) use ($bar, $mailer)
        {
            $mailer->sendGiftReminder ($reminder);
            $bar->advance();
        });

        $bar->finish();
    }
}

<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

class Reload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the database, run the seeders, import records';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
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
        $this->call('migrate:refresh');
        $this->call('db:seed');
        
        // $this->call('p2p:import');
        $this->call('p2p:importQueue');
    }
}

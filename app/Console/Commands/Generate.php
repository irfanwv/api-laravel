<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

class Generate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:gin 
                            { item : The name of your new stuff. }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Some Stuff';

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
        $item = ucfirst($this->argument('item'));

        $this->call('make:model', ['name' => $item.'s/'.$item]);
        $this->call('make:controller', ['name' => $item.'Controller']);
        $this->call('make:transformer', ['name' => $item.'s/'.$item.'Transformer']);
        $this->call('make:repository', ['name' => $item.'s/'.$item.'Repository']);
    }
}

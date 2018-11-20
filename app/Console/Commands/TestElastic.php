<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestElastic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test elastic search index and db consistency';

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
        $this->info($this->description .' started..');
    }
}

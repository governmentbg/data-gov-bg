<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class RefreshTNTIndexes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tnt:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh TNT indexes for correct search functionalities';

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
        $start = microtime(true);

        try {
            Artisan::call('tntsearch:import', ['model' => 'App\\Category']);
            $this->info(Artisan::output());
            Artisan::call('tntsearch:import', ['model' => 'App\\DataSet']);
            $this->info(Artisan::output());
            Artisan::call('tntsearch:import', ['model' => 'App\\Document']);
            $this->info(Artisan::output());
            Artisan::call('tntsearch:import', ['model' => 'App\\HelpPage']);
            $this->info(Artisan::output());
            Artisan::call('tntsearch:import', ['model' => 'App\\HelpSection']);
            $this->info(Artisan::output());
            Artisan::call('tntsearch:import', ['model' => 'App\\Organisation']);
            $this->info(Artisan::output());
            Artisan::call('tntsearch:import', ['model' => 'App\\Page']);
            $this->info(Artisan::output());
            Artisan::call('tntsearch:import', ['model' => 'App\\Resource']);
            $this->info(Artisan::output());
            Artisan::call('tntsearch:import', ['model' => 'App\\Signal']);
            $this->info(Artisan::output());
            Artisan::call('tntsearch:import', ['model' => 'App\\TermsOfUseRequest']);
            $this->info(Artisan::output());
            Artisan::call('tntsearch:import', ['model' => 'App\\User']);
            $this->info(Artisan::output());
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            $this->error($ex->getMessage());
        }

        $this->info('Time elapsed: '. (round(microtime(true) - $start, 2)) .'s');
    }
}

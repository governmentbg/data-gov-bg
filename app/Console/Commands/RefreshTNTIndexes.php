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
            $models = [
                'App\\Organisation',
                'App\\Category',
                'App\\DataSet',
                'App\\Document',
                'App\\HelpPage',
                'App\\HelpSection',
                'App\\Page',
                'App\\Resource',
                'App\\Signal',
                'App\\TermsOfUseRequest',
                'App\\User',
            ];
            
            foreach ($models as $model) {
                Artisan::call('scout:import', ['model' => $model]);
                $this->info(Artisan::output());
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            $this->error($ex->getMessage());
        }

        $this->info('Time elapsed: '. (round(microtime(true) - $start, 2)) .'s');
    }
}

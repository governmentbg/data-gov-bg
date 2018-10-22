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
            chmod(storage_path('categories.index'), 0755);
            $this->info(Artisan::output());

            Artisan::call('tntsearch:import', ['model' => 'App\\DataSet']);
            chmod(storage_path('data_sets.index'), 0755);
            $this->info(Artisan::output());

            Artisan::call('tntsearch:import', ['model' => 'App\\Document']);
            chmod(storage_path('documents.index'), 0755);
            $this->info(Artisan::output());

            Artisan::call('tntsearch:import', ['model' => 'App\\HelpPage']);
            chmod(storage_path('help_pages.index'), 0755);
            $this->info(Artisan::output());

            Artisan::call('tntsearch:import', ['model' => 'App\\HelpSection']);
            chmod(storage_path('help_sections.index'), 0755);
            $this->info(Artisan::output());

            Artisan::call('tntsearch:import', ['model' => 'App\\Organisation']);
            chmod(storage_path('organisations.index'), 0755);
            $this->info(Artisan::output());

            Artisan::call('tntsearch:import', ['model' => 'App\\Page']);
            chmod(storage_path('pages.index'), 0755);
            $this->info(Artisan::output());

            Artisan::call('tntsearch:import', ['model' => 'App\\Resource']);
            chmod(storage_path('resources.index'), 0755);
            $this->info(Artisan::output());

            Artisan::call('tntsearch:import', ['model' => 'App\\Signal']);
            chmod(storage_path('signals.index'), 0755);
            $this->info(Artisan::output());

            Artisan::call('tntsearch:import', ['model' => 'App\\TermsOfUseRequest']);
            chmod(storage_path('terms_of_use_requests.index'), 0755);
            $this->info(Artisan::output());

            Artisan::call('tntsearch:import', ['model' => 'App\\User']);
            chmod(storage_path('users.index'), 0755);
            $this->info(Artisan::output());
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            $this->error($ex->getMessage());
        }

        $this->info('Time elapsed: '. (round(microtime(true) - $start, 2)) .'s');
    }
}

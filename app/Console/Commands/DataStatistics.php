<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DataStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:statistics {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get data statistics';

    protected $migrationUserId;

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
        try {
            switch ($this->argument('type')) {
                case 1:
                    $this->init();
                    $this->getMigrated();
                    break;
                case 2:
                    $this->init();
                    $this->getOldPortalNewData();
                    break;
                case 3:
                    $this->init();
                    $this->getNewPortalNewData();
                    break;
                default:
                    $this->error('Wrong type!');
                    break;
            }
        } catch (\Exception $ex) {
            $this->error('Statistical collection failed!');

            Log::error($ex->getMessage());
        }
    }

    private function init()
    {
        $this->info('Statistical collection started..');

        $this->migrationUserId = DB::table('users')->where('username', 'migrate_data')->get()->pluck('id');
    }

    private function getMigrated()
    {
        $allDatasets = DB::table('data_sets')->where('data_sets.updated_by', $this->migrationUserId)->count();

        $brokenDatasets = DB::table('data_sets')
            ->select('data_sets.uri')
            ->where('data_sets.updated_by', $this->migrationUserId)
            ->whereNotIn(
                'data_sets.id',
                DB::table('resources')
                    ->rightJoin('elastic_data_set', 'resources.id', '=', 'elastic_data_set.resource_id')
                    ->get()
                    ->pluck('data_set_id')
            )->count();

        $allResources = DB::table('resources')->where('resources.updated_by', $this->migrationUserId)->count();
        $brokenResources = DB::table('resources')
            ->where('resources.updated_by', $this->migrationUserId)
            ->whereNotIn('id', DB::table('elastic_data_set')->get()->pluck('resource_id'))
            ->count();

        $correctDatasets = $allDatasets - $brokenDatasets;
        $correctResources = $allResources - $brokenResources;

        $this->line('All migrated datasets: '. $allDatasets);
        $this->line('Datasets that do not have resource or have resources without indexes in elastic table: '. $brokenDatasets);
        $this->line('Correct datasets: '. $correctDatasets);
        $this->line('All migrated resources: '. $allResources);
        $this->line('Resources without indexes in elastic table: '. $brokenResources);
        $this->line('Correct resources: '. $correctResources);
    }

    private function getOldPortalNewData()
    {
        $newPortalDatasets = DB::table('data_sets')->count();

        $this->info('Statistical collection started..');
    }

    private function getNewPortalNewData()
    {
        $lastUpdatedDataset = DB::table('data_sets')
            ->where('data_sets.updated_by', $this->migrationUserId)
            ->orderBy('data_sets.updated_at', 'desc')
            ->limit(1)
            ->first();
        $newDatasets = DB::table('data_sets')
            ->where('data_sets.created_at', '>', $lastUpdatedDataset->updated_at)
            ->count();
        $lastUpdatedResource = DB::table('resources')
            ->where('resources.updated_by', $this->migrationUserId)
            ->orderBy('resources.updated_at', 'desc')
            ->limit(1)
            ->first();
        $newResources = DB::table('resources')
            ->where('resources.created_at', '>', $lastUpdatedResource->updated_at)
            ->count();

        $this->line('All new datasets: '. $newDatasets);
        $this->line('All new resources: '. $newResources);
    }
}

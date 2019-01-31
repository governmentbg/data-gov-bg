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

        $this->line('1) Successfully migrated datasets.');
        $this->line('All migrated datasets: '. $allDatasets);
        $this->line('Datasets that do not have resource or have resources without indexes in elastic table: '. $brokenDatasets);
        $this->line('Correct datasets: '. $correctDatasets);
        $this->line('All migrated resources: '. $allResources);
        $this->line('Resources without indexes in elastic table: '. $brokenResources);
        $this->line('Correct resources: '. $correctResources);
    }

    private function getOldPortalNewData()
    {
        $newDatasetsCount = 0;
        $newResourcesCount = 0;

        $lastMigratedDataset = DB::table('data_sets')
            ->where('data_sets.updated_by', $this->migrationUserId)
            ->orderBy('updated_at', 'desc')
            ->limit(1)
            ->pluck('updated_at')
            ->first();

        $lastMigratedDataset = strtotime($lastMigratedDataset);

        $header[] = 'Authorization: '. config('app.MIGRATE_USER_API_KEY');
        $params = [
            'all_fields' => true
        ];

        $users = request_url('user_list', $params, $header);

        $bar = $this->output->createProgressBar(count($users['result']));

        foreach ($users['result'] as $user) {
            $prms = [];
            $response = [];

            if ($user['number_created_packages'] > 0) {
                $prms = [
                    'id'                => $user['id'],
                    'include_datasets'  => true
                ];

                $response = request_url('user_show', $prms);

                if ($result = $response['result']) {
                    $dataSets = [];
                    $dataSets = isset($result['datasets']) ? $result['datasets'] : [];

                    if ($dataSets) {
                        foreach ($dataSets as $ds) {
                            $dsDateCreated = strtotime($ds['metadata_created']);
                            $dsDateModified = isset($ds['metadata_modified'])
                                ? strtotime($ds['metadata_modified'])
                                : null;

                            if (
                                $dsDateCreated > $lastMigratedDataset
                                || ($dsDateModified && $dsDateModified > $lastMigratedDataset)
                            ) {
                                $newDatasetsCount ++;
                            }

                            if ($ds['resources']) {
                                foreach ($ds['resources'] as $resource) {
                                    $rsDateCreated = strtotime($resource['created']);
                                    $rsDateModified = isset($resource['last_modified'])
                                        ? strtotime($resource['last_modified'])
                                        : null;

                                    if (
                                        $rsDateCreated > $lastMigratedDataset
                                        || ($rsDateModified && $rsDateModified > $lastMigratedDataset)
                                    ) {
                                        $newResourcesCount ++;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line('');
        $this->line('2) How many datasets are uploaded on the old system and are not migrated to the new one.');
        $this->line('Last migrated dataset was on: '. date('Y-m-d H:i:s', $lastMigratedDataset));
        $this->line('New datasets: '. $newDatasetsCount);
        $this->line('New resources: '. $newResourcesCount);
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

        $this->line('3) How many datasets are uploaded directly on the new system.');
        $this->line('All new datasets: '. $newDatasets);
        $this->line('All new resources: '. $newResources);
    }
}

<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\Log;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\DataSet;
use App\Resource;
use App\UserFollow;
use App\DataSetTags;
use App\ElasticDataSet;

class MigrateSpecificData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:specificData {--org='. null .'} {--dset='. null .'} {--resource='. null .'} {--delete} {--convert}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Data migration';

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
        $org = $this->option('org');
        $dset = $this->option('dset');
        $delete = $this->option('delete');
        $resource = $this->option('resource');

        if (
            empty($org)
            && empty($dset)
            && empty($resource)
        ) {
            $this->error('One of these options should be set - org, dset, resource!');
        }

        $this->migrationUserId = DB::table('users')->where('username', 'migrate_data')->get()->pluck('id');

        //Login
        \Auth::loginUsingId($this->migrationUserId);

        $this->info('Data migration has started.');

        if (!empty($resource)) {
            $this->getResourceData($resource);
        }

        if (!empty($dset)) {
            if ($delete) {
                $this->deleteSavedData('dset', $dset);
            }

            $this->getDatasetData($dset);
        }

        if (!empty($org)) {
            if ($delete) {
                $this->deleteSavedData('org', $org);
            }

            $this->getOragnisationData($org);
        }
    }

    private function getOragnisationData($orgUri)
    {
        $params = [
            'id'                => $orgUri,
            'include_datasets'  => true,
        ];

        $successPackages = $failedPacgakes = 0;

        $response = request_url('organization_show', $params);

        if ($orgData = $response['result']) {
            $total = isset($orgData['package_count']) ? (int) $orgData['package_count'] : 0;
            $bar = $this->output->createProgressBar($total);
            $this->line('');

            if ($orgData['package_count'] > 0) {
                foreach ($orgData['packages'] as $dataset) {
                    $result = [];
                    $bar->advance();
                    $result = migrate_datasets($dataset['id'], $this->option('convert'));

                    if (isset($result['success'])) {
                        $successPackages++;

                        $this->line('');
                        $this->line('Resources total: '. $result['totalResources']);
                        $this->info('Resources successful: '. $result['successResources']);
                        $this->error('Resources failed: '. $result['failedResources']);
                        $this->line('Unsupported resource format count for the current dataset: '. $result['unsuportedResources']);

                        if (isset($result['followers']['success'])) {
                            $this->line('Followers total: '. $result['followersInfo']['totalFollowers']);
                            $this->info('Followers success: '. $result['followersInfo']['successFollowers']);
                        } else {
                            $this->info($result['followersInfo']['error_msg']);
                        }
                        $this->line('');
                    } else {
                        $failedPacgakes++;

                        if (isset($result['error'])) {
                            $this->line('');
                            $this->line($result['error_msg']);
                        }
                    }
                }
            } else {
                $this->error('Selected organisation has no datasets!');
            }

            $this->line('');
            $this->line('Organisation total datasets: '. $total);
            $this->info('Dataset success: '. $successPackages);
            $this->error('Dataset failed: '. $failedPacgakes);
            $this->line('');
            $bar->finish();
            $this->line('');
        } else {
            $this->error('The organisation was not found!');
        }
    }

    private function getDatasetData($dsetUri)
    {
        if ($dsetUri) {
            $result = migrate_datasets($dsetUri, $this->option('convert'));

            if (isset($result['success'])) {
                $this->line('');
                $this->line('Dataset total resources: '. $result['totalResources']);
                $this->info('Resources successful: '. $result['successResources']);
                $this->error('Resources failed: '. $result['failedResources']);
                $this->line('Unsuported resource format count for the current dataset: '. $result['unsuportedResources']);
                if (isset($result['followers']['success'])) {
                    $this->line('Followers total: '. $result['followersInfo']['totalFollowers']);
                    $this->info('Followers success: '. $result['followersInfo']['successFollowers']);
                } else {
                    $this->info($result['followersInfo']['error_msg']);
                }
                $this->line('');
            } elseif (isset($result['error'])) {
                $this->line($result['error_msg']);
                $this->line('');
            }
        } else {
            $this->error('The dataset was not found!');
        }
    }

    private function getResourceData($resourceUri)
    {
        $params = [
            'id' => $resourceUri
        ];

        $response = request_url('resource_show', $params);

        if (isset($response['result'])) {
            $resource = $response['result'];

            $dataSet = DB::table('data_sets')
                ->select('id', 'created_by')
                ->where('uri', $resource['package_id'])
                ->first();

            if ($dataSet) {
                $resource['created_by'] = $dataSet->created_by;

                if (migrate_datasets_resources($dataSet->id, $resource, $this->option('convert'))) {
                    $this->line('');
                    $this->info('Resources added successfully.');
                } else {
                    $this->error('Error adding resource.');
                }
            } else {
                $this->error('Parent dataset was not found.');
            }
        } else {
            $this->error('The resource was not found!');
        }
    }

    private function deleteSavedData($type, $data)
    {
        switch ($type) {
            case 'org':

                $orgId = DB::table('organisations')->where('uri', $data)->value('id');
                $dataSets = DataSet::where('org_id', $orgId)->get()->pluck('id');
                $this->deleteData($dataSets);
                break;
            case 'dset':

                $dataSets = DataSet::where('uri', $data)->get()->pluck('id');
                $this->deleteData($dataSets);
                break;
        }

    }

    private function deleteData($dataSets)
    {
        if ($this->confirm('Are you sure you want to delete saved data?')) {
            try {
                ElasticDataSet::whereIn('index', $dataSets)->forceDelete();
                Resource::whereIn('data_set_id', $dataSets)->forceDelete();
                DataSetTags::whereIn('data_set_id', $dataSets)->delete();
                UserFollow::whereIn('data_set_id', $dataSets)->delete();
                DataSet::whereIn('id', $dataSets)->forceDelete();

                foreach ($dataSets as $id) {
                    $indexParams['index'] = $id;
                    if (\Elasticsearch::indices()->exists($indexParams)) {
                        \Elasticsearch::indices()->delete(['index' => $id]);
                    }
                }
            } catch (\Exception $ex) {
                $this->error('Delete failed!');
                Log::error($ex->getMessage());
                die();
            }
        } else {
            $this->line('Command is aborted!');
            die();
        }
    }
}

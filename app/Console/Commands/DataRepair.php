<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\User;
use App\DataSet;
use App\Resource;
use App\Signal;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\ConversionController as ApiConversion;

class DataRepair extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:migratedData {direction}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repair migrated data';

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
            $this->info('Data migration repair has started.');
            $this->line('');

            if ($this->argument('direction') == 'up') {
                $this->up();
                $this->info('Repair migrated data finished successfully!');
            } else {
                $this->error('No direction given.');
            }
        } catch (\Exception $ex) {
            $this->error('Repair migrated data failed!');
            Log::error(print_r($ex->getMessage(), true));

            $this->up();
        }
    }

    private function up()
    {
        gc_enable();
        $migrateUserId = User::where('username', 'migrate_data')->value('id');
        \Auth::loginUsingId($migrateUserId);

        ini_set('memory_limit', '8G');

        $this->repairBrokenDatasets();
    }

    private function repairBrokenDatasets()
    {
        $brokenDatasets = DB::table('data_sets')
            ->select('data_sets.uri')
            ->whereNotIn('data_sets.id',
                DB::table('resources')
                ->rightJoin('elastic_data_set', 'resources.id', '=', 'elastic_data_set.resource_id')
                ->get()
                ->pluck('data_set_id')
            )->get();

        $brokenResources = DB::table('resources')
            ->whereNotIn('id', DB::table('elastic_data_set')->get()->pluck('resource_id'))
            ->get()
            ->pluck('id');

        $this->line('Datasets that do not have resource or have resources without indexes in elastic table are '. count($brokenDatasets));
        $this->line('Broken resource are '. count($brokenResources));


        if ($this->confirm('You are going to delete '. count($brokenResources) .' resources for which there are no indexes in elastic. Are you sure?')) {
            if (isset($brokenResources)) {
                $bar = $this->output->createProgressBar(count($brokenDatasets));

                Signal::whereIn('resource_id', $brokenResources)->forceDelete();
                Resource::whereIn('id', $brokenResources)->forceDelete();

                foreach ($brokenDatasets as $dataset) {
                    $addedResources = $failedResources = $total = 0;
                    $dataSetInfo = DataSet::where('uri', $dataset->uri)->first();

                    $params = [
                        'id' => $dataset->uri
                    ];

                    $response = request_url('package_show', $params);

                    if ($response['success'] && $response['result']['num_resources'] > 0) {
                        $total = $response['result']['num_resources'];
                        $dataSetResources = isset($response['result']['resources'])
                            ? $response['result']['resources']
                            : [];

                        foreach ($dataSetResources as $resource) {
                            $savedResource = Resource::where('uri', $resource['id'])->first();
                            $resource['created_by'] = $dataSetInfo->created_by;

                            if ($savedResource) {
                                continue;
                            }

                            if (migrate_datasets_resources($dataSetInfo->id, $resource, true)) {
                                $addedResources ++;
                            } else {
                                $failedResources ++;
                            }

                            unset($resource);
                        }
                    }

                    $this->line('Datasets Total Resource: '. $total);
                    $this->info('Resource success: '. $addedResources);
                    $this->error('Resource failed: '. $failedResources);
                    $this->line('');
                    $bar->advance();
                    $this->line('');
                }
            }

            $bar->finish();
        } else {
            $this->line('Command is aborted!');
        }
    }
}

<?php

namespace App\Console\Commands;

use App\User;
use App\DataSet;
use App\Resource;
use App\UserFollow;
use App\DataSetTags;
use App\DataSetGroup;
use App\ElasticDataSet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanMigratedAndDeleted extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:removeDeleted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Final removal of deleted migration data';

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
        $dataSets = DataSet::select('id')->where('deleted_by', 2)->where('is_migrated', 1)->withTrashed()->pluck('id');
        $this->info(count($dataSets). ' datasets were deleted during migration.');

        if (count($dataSets) == 0) {
            $this->info('Nothing to remove.');
            die();
        }

        if ($this->confirm('Delete them permanently?')) {
            try {
                $result = \DB::transaction(function () use ($dataSets) {
                    ElasticDataSet::whereIn('index', $dataSets)->forceDelete();
                    $resources = Resource::whereIn('data_set_id', $dataSets)->get();

                    foreach ($resources as $singleResource) {
                        $singleResource->signal()->delete();
                        $singleResource->customFields()->delete();
                        $singleResource->forceDelete();
                    }

                    DataSetTags::whereIn('data_set_id', $dataSets)->delete();
                    UserFollow::whereIn('data_set_id', $dataSets)->delete();
                    DataSetGroup::whereIn('data_set_id', $dataSets)->delete();
                    DataSet::whereIn('id', $dataSets)->forceDelete();

                    foreach ($dataSets as $id) {
                        if (\Elasticsearch::indices()->exists(['index' => $id])) {
                            if (\Elasticsearch::indices()->delete(['index' => $id])) {
                                $this->info('Index '. $id .' was deleted.');
                            } else {
                                $this->error('Index '. $id .' was not deleted.');
                            }
                        }
                    }
                }, config('app.TRANSACTION_ATTEMPTS'));

                return $result;
            } catch (\Exception $ex) {
                $this->error('Delete failed!');
                Log::error($ex->getMessage());
                die();
            }
        } else {
            $this->info('Command was aborted! Nothing was deleted.');
            die();
        }

        $this->info('Execution finished.');
    }
}

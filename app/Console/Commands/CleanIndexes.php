<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanIndexes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:indexClean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean elasticsearch indices';

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
        if (!\Elasticsearch::ping()) {
            $this->error('Elasticsearch is not running.');
            die();
        }

        $this->info($this->description .' started..');

        $stats = \Elasticsearch::indices()->stats();
        $indices = [];
        $datasetIds = [];
        $toBeDeleted = [];

        // Get index ids stored in elasticsearch
        if (!empty($stats['indices'])) {
            foreach ($stats['indices'] as $index => $stat) {
                $indices[] = $index;
            }
        }

        // Get ids of datasets stored in resource metadata
        $dIds = DB::select('select distinct data_set_id from resources');

        foreach ($dIds as $singleResource) {
            $datasetIds[] = $singleResource->data_set_id;
        }

        if (!empty($indices)) {
            foreach ($indices as $singleIndex) {
                // Check if each elasticsearch index belongs to a corresponding one in resource metadata
                if (!in_array($singleIndex, $datasetIds)) {
                    $this->info('Index '. $singleIndex.' has NO corresponding resource');
                    // If an index does not belong to a dataset add it for deletion
                    $toBeDeleted[] = $singleIndex;
                }
            }
        }

        if (!empty($toBeDeleted)) {
            $this->info(count($toBeDeleted). ' indices do not belong to a dataset.');

            if ($this->confirm('Delete indices?')) {
                foreach ($toBeDeleted as $singleIndex) {
                    if (\Elasticsearch::indices()->delete(['index' => $singleIndex])) {
                        $this->info('Index ' .$singleIndex. ' was removed from elasticsearch');
                    } else {
                        $this->info('Index ' .$singleIndex. ' was NOT removed from elasticsearch');
                    }
                }
            } else {
                $this->info('Command aborted. Nothing was deleted.');
            }
        } else {
            $this->info('All indices seem to be in order.');
        }

        $this->info($this->description .' finished.');
    }
}

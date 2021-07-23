<?php

namespace App\Console\Commands;

use App\ElasticDataSet;
use Illuminate\Console\Command;
use Elasticsearch as ES;

class ReindexDatasetInElasticsearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:reindex {--oldIndex=} {--newIndex=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "When moving the resources from one dataset to another reindex the resources in Elasticsearch";

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
        if(!ES::ping()) {
          $this->error('Elasticsearch is not running');
          die();
        }

        $status = ElasticDataSet::getElasticClusterParam('status');
        $nodes = ElasticDataSet::getElasticClusterParam('number_of_nodes');

        if($status != 'green' || $nodes != 6) {
          $this->error('Elasticsearch no in good health for operation');
          die();
        }

        $oldIndex = $this->option('oldIndex');
        $newIndex = $this->option('newIndex');

        $this->info('Starting reindexing of the resources in Elasticsearch..');

        $oldIndexSettings = ES::indices()->getSettings(['index' => $oldIndex])[$oldIndex]['settings']['index'];

        /*
         * Set the refresh_interval to -1 and the number_of_replicas to 0 for efficient reindexing
         * And after reindexing update them to the values of the old index
         */
        $params = [
          'index'     => $newIndex,
          'body'      => [
            'settings'  => [
              'index.mapping.total_fields.limit' => 10000,
              'index.number_of_shards' => $oldIndexSettings['number_of_shards'],
              'index.number_of_replicas' => 0,
              'index.refresh_interval' => '-1'
            ],
            'mappings' => [
              'default' => [
                'enabled' => false
              ]
            ]
          ],
        ];

        if (!ES::indices()->exists(['index' => $newIndex])) {
          ES::indices()->create($params);
        }

        /*
         * Set op_type to create will reindex document only if it not exist in the new index
         * Set version_type to external will preserve document versions
         */
        $params = [
          //'wait_for_completion' => false,
          'slices' => 10,
          'refresh' => true,
          'body' => [
            'source' => [
              'index'  => $oldIndex,
            ],
            'conflicts' => 'proceed',
            'dest' => [
              'index' => $newIndex,
              'op_type' => 'create',
              'version_type' => 'external'
            ]
          ]
        ];

        ES::reindex($params);

        /*
         * Reset the refresh_interval and number_of_replicas to the values used in the old index.
         */
        ES::indices()->putSettings([
          'index' => $newIndex,
          'body' => [
            'refresh_interval' => $oldIndexSettings['refresh_interval'],
            'number_of_replicas' => $oldIndexSettings['number_of_replicas']
          ]
        ]);

        //$this->info(json_decode($response, JSON_PRETTY_PRINT));
        $this->line('');
        $this->info('Reindexing has finished successfully');

    }
}

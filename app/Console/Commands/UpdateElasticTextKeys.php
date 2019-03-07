<?php

namespace App\Console\Commands;

use App\ElasticDataSet;
use Illuminate\Console\Command;

class UpdateElasticTextKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:updateTextKeys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update elastic text keys';

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
        $this->info($this->description .' started..');
        $start = microtime(true);
        $count = 0;

        try {
            $mappings = \Elasticsearch::indices()->getMapping(['index' => '_all']);

            foreach ($mappings as $index => $mapping) {
                $rebuildFlag = false;

                if (
                    isset($mapping['mappings']['default']['properties']['rows']['properties']['text'])
                    && count($mapping['mappings']['default']['properties']['rows']['properties']) == 1
                ) {
                    $rebuildFlag = true;
                }

                if ($rebuildFlag) {
                    $this->info('Index '. $index .' has wrong mapping');

                    $data = [
                        'index' => $index,
                        'type'  => 'default',
                        'body'  => json_encode([
                            'query' => ['match_all' => new \stdClass],
                            'stored_fields' => [],
                        ]),
                    ];

                    $search = \Elasticsearch::search($data);
                    $indexData = [];

                    if (!empty($search['hits']['hits'][0]['_id'])) {
                        foreach ($search['hits']['hits'] as $indexMetadata) {
                            $id = $indexMetadata['_id'];
                            $parts = explode('_', $id);
                            $indexData[$id] = ElasticDataSet::getElasticData($parts[0], $parts[1]);
                        }

                        $this->info('Index '. $index .' data collected');

                        \Elasticsearch::indices()->delete(['index' => $index]);

                        $this->info('Index '. $index .' deleted');

                        foreach ($indexData as $id => $data) {
                            \Elasticsearch::index([
                                'body'  => $data,
                                'index' => $index,
                                'type'  => ElasticDataSet::ELASTIC_TYPE,
                                'id'    => $id,
                            ]);
                        }

                        $count++;
                        $this->info('Index '. $index .' migrated successfully');
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $elaspsedTime = microtime(true) - $start;
        $hours = floor($elaspsedTime / 3600);
        $mins = floor($elaspsedTime / 60 % 60);
        $secs = floor($elaspsedTime % 60);
        $time = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

        if ($count) {
            $this->info($count .' indexes migrated successfully');
        } else {
            $this->info('No wrong indexes found');
        }

        $this->info($this->description .' finished in '. $time);
    }
}

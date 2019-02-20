<?php

namespace App\Console\Commands;

use App\ElasticDataSet;
use Illuminate\Console\Command;

class ElasticMapperFix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:mapper';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix elastic mapper settings';

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

        try {
            \Elasticsearch::indices()->putMapping([
                'index' => '_all',
                'type'  => ElasticDataSet::ELASTIC_TYPE,
                'body'  => ['date_detection' => false],
            ]);

            $this->info('Date detection set to false for old indices');

            \Elasticsearch::indices()->putTemplate([
                'name'                          => 'default',
                'body'                          => [
                    'index_patterns'                => ['*'],
                    'mappings'                      => [
                        ElasticDataSet::ELASTIC_TYPE    => [
                            'date_detection'                => false,
                        ],
                    ],
                ],
            ]);

            $this->info('Date detection set to false for new indices');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}

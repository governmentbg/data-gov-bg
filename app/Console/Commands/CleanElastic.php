<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanElastic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean elastic search indexes';

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

        $stats = \Elasticsearch::indices()->stats();
        $indices = [];

        if (!empty($stats['indices'])) {
            foreach ($stats['indices'] as $index => $stat) {
                $indices[] = $index;
            }
        }

        $dIds = DB::select('select d.id, count(r.id) as rescount from data_sets as d left join resources as r on d.id = r.data_set_id group by d.id');

        if (!empty($dIds)) {
            foreach ($dIds as $record) {
                if ($record->rescount == 0) {
                    $this->info('Dataset '. $record->id .' has no resources..');

                    if (in_array($record->id, $indices)) {
                        \Elasticsearch::indices()->delete(['index' => $record->id]);

                        $this->info('Dataset '. $record->id .' es index deleted');
                    } else {
                        $this->info('Dataset '. $record->id .' had no es index');
                    }
                }
            }
        } else {
            $this->info('No datasets with no resources found');
        }

        $this->info($this->description .' finished');
    }
}

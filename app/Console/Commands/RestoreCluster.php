<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RestoreCluster extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restore:cluster';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore cluster data from a given snapshot';

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
            $this->error('Elasticsearch is not running');
            die();
        }

        $repositories = \Elasticsearch::snapshot()->getRepository();

        if (empty($repositories)) {
            $this->error('No repositories exist');
            die();
        }

        $repoName = $this->ask('Enter repository name');

        if (!empty($repoName)) {
            $availableSnapshots = \Elasticsearch::cat()->snapshots(['repository' => $repoName]);

            if (!empty($availableSnapshots)) {

                foreach($availableSnapshots as $index => $snapshot) {
                    $snapshotIds[] = $availableSnapshots[$index]['id'];
                    $date[$index] = $availableSnapshots[$index]['end_epoch'];
                    $time[$index] = $availableSnapshots[$index]['end_time'];
                    $availableSnapshots[$index]['start_epoch'] = gmdate('d-m-Y', $availableSnapshots[$index]['start_epoch']);
                    $availableSnapshots[$index]['end_epoch'] = gmdate('d-m-Y', $availableSnapshots[$index]['end_epoch']);
                }

                $endEpoch = array_column($availableSnapshots, 'end_epoch');
                $endTime = array_column($availableSnapshots, 'end_time');
                array_multisort($endEpoch, SORT_ASC, $endTime, SORT_ASC, $availableSnapshots);
            }

            $headers = [
                'id',
                'status',
                'start_epoch',
                'start_time',
                'end_epoch',
                'end_time',
                'duration',
                'indices',
                'successful_shards',
                'failed_shards',
                'total_shards'
            ];

            $this->table($headers, $availableSnapshots);

            $selectedSnapshot = $this->ask('Select snapshot for restoration (id)');

            if (!empty($selectedSnapshot)) {
                if (!in_array($selectedSnapshot, $snapshotIds)) {
                    $this->error('Non-existent snapshot');
                    die();
                }

                $successRestore = \Elasticsearch::snapshot()->restore(['repository' => $repoName, 'snapshot' => $selectedSnapshot]);

                if ($successRestore) {
                    $this->info('Restoration success');
                } else {
                    $this->error('Restoration failure');
                }
            }
        }
    }
}

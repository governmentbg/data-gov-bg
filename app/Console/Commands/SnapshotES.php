<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SnapshotES extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snapshot:es {--repotype=} {--reponumber=} {--newsnapshot=} {--snapshotname=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a snapshot of elastic cluster';

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
        $repoName = '';
        $repoPath = '';
        $counter = 1;
        $repoNumbers = [];

        $repoType = $this->option('repotype');
        $selectedRepo = $this->option('reponumber');
        $newSnapshot = $this->option('newsnapshot');
        $snapshotName = $this->option('snapshotname');

        if (!empty($repositories)) {

            foreach ($repositories as $index => $repoData) {
                $repoNumbers[$counter] = $index;
                $counter++;
            }

            if (empty($repoType)) {
                $this->info('Available repositories: ');

                foreach ($repositories as $index => $repoData) {
                    $this->info($counter. '. ' .$index);
                    $repoNumbers[$counter] = $index;
                    $counter++;
                }

                $repoType = $this->confirm('Use existing repository(yes) or create a new one(no)');
            }

            if ($repoType) {
                if (empty($selectedRepo)) {
                    $selectedRepo = $this->ask('Enter repository number');
                }

                if (!in_array($selectedRepo, array_keys($repoNumbers))) {
                    $this->error('Non-existent repository');
                    die();
                }
            } else {
                $repoName = $this->ask('Enter a name for the repository');
                $repoPath = $this->ask('Enter a path for the repository (must be defined in path.repo in yml)');

                $bodyParams = [
                    'type'     => 'fs',
                    'settings' => [
                        'location' => $repoPath
                    ]
                ];

                if (!\Elasticsearch::snapshot()->createRepository(['repository' => $repoName, 'body' => $bodyParams])) {
                    $this->error('Repository creation failed');
                    die();
                }
            }
        } else {
            $this->info('No repositories found');

            if ($this->confirm('Create a new repository?')) {
                $repoName = $this->ask('Enter a name for the repository');
                $repoPath = $this->ask('Enter a path for the repository (must be defined in path.repo in yml)');

                $bodyParams = [
                    'type'     => 'fs',
                    'settings' => [
                        'location' => $repoPath
                    ]
                ];

                if (!\Elasticsearch::snapshot()->createRepository(['repository' => $repoName, 'body' => $bodyParams])) {
                    $this->error('Repository creation failed');
                    die();
                }
            } else {
                $this->info('Aborted');
                die();
            }
        }

        if (empty($repositories) || (count($repositories) != count(\Elasticsearch::snapshot()->getRepository()))) {
            $repositories = \Elasticsearch::snapshot()->getRepository();
            $this->info('Available repositories: ');
            $counter = 1;

            foreach ($repositories as $index => $repoData) {
                $this->info($counter. '. ' .$index);
                $repoNumbers[$counter] = $index;
                $counter++;
            }
        }
        if (empty($selectedRepo)) {
            $selectedRepo = $this->ask('Enter repository number');

            if (!in_array($selectedRepo, array_keys($repoNumbers))) {
                $this->error('Non-existent repository');
                die();
            }
        }

        if (!empty($selectedRepo)) {
            if (empty($newSnapshot)) {
                $newSnapshot = $this->confirm('Create a new snapshot?');
            }

            if ($newSnapshot) {
                if (empty($snapshotName)) {
                    $snapshotName = $this->ask('Enter snapshot name');
                }

                $snapshotParams = [
                    'repository' => $repoNumbers[$selectedRepo],
                    'snapshot'   => $snapshotName,
                    'body'       => [
                        'wait_for_completion' => true
                    ]
                ];

                $completedSnapshot = \Elasticsearch::snapshot()->create($snapshotParams);

                if ($completedSnapshot) {
                    $this->line('');
                    $this->info('Snapshot created');
                }
            } else {
                $this->error('Snapshot aborted');
                die();
            }
        }
    }
}

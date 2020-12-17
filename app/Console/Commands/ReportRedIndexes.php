<?php

namespace App\Console\Commands;

use App\DataSet;
use App\Resource;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReportRedIndexes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:redIndexReport {--delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Report Elasticsearch red indices';
    protected $failed = 0;

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

        $deleteOpt = $this->option('delete');
        $path = $this->ask('Enter full path to your csv file');

        if (strpos($path, '.csv') === false) {
            $this->error('Missing or wrong file extension!');
            die();
        }

        $this->info($this->description .' started..');
        $this->info('Extract indices stat from Elasticsearch..');
        $this->info("\n");

        $query = "select ds.id, ds.uri, ds.org_id, t.label as org_name, u.firstname as u_firstname,
            u.lastname as u_lastname, u.username as u_username, u.email as  u_email from data_sets as ds
            left join organisations as o on o.id = ds.org_id
            left join translations as t on t.group_id = o.name
            left join user_to_org_role as ur on ur.org_id = ds.org_id
            left join users as u on u.id = ur.user_id
            where ds.deleted_at is null and ur.role_id = (select id from roles where default_org_admin = 1 and for_org = 1) and t.locale = 'bg'
            order by ds.id asc";

        $dSets = [];
        $dSetsResult = \DB::select(\DB::raw($query));

        foreach ($dSetsResult as $dSet) {
            if (!isset($dSets[$dSet->id])) {
                $dSets[$dSet->id] = [];
            }

            $dSets[$dSet->id][] = $dSet;
        }

        $csvFile = fopen($path, 'w') or die('Unable to create new file :'. $path);
        $csvHead = $deleteOpt
            ? ['id', 'uri', 'org_id', 'org_name', 'firstname', 'lastname', 'username', 'email', 'deleted_dset_db', 'deleted_res_db', 'deleted_es']
            : ['id', 'uri', 'org_id', 'org_name', 'firstname', 'lastname', 'username', 'email'] ;

        fputcsv($csvFile, $csvHead, ',', "'", "\\");

        $indicesHealth = \Elasticsearch::cluster()->health(['level' => 'indices']);
        $indicesHealth = !empty($indicesHealth['indices']) ? $indicesHealth['indices'] : [];

        $progressBar = $this->output->createProgressBar(count($indicesHealth));
        $progressBar->start();

        if (!empty($indicesHealth)) {
            collect($indicesHealth)->map(function($indexData, $index) use($progressBar, $csvFile, $deleteOpt, $dSets) {
                $this->output->write('<info> index: '. $index .'...</info>');

                if ($indexData['status'] == 'red') {
                    $this->failed = $this->failed + 1;
                    $deletedResources = [];
                    $deletedDataSet = [];

                    if ($deleteOpt) {
                        $deletedIndex = \Elasticsearch::indices()->delete(['index' => $index]);

                        if (!empty($dSets[$index]) && !empty($deletedIndex['acknowledged'])) {
                            $deletedResources[$index] = Resource::where('data_set_id', $index)->delete();
                            $deletedDataSet[$index] = DataSet::where('id', $index)->delete();
                        }
                    }

                    if (!empty($dSets[$index])) {
                        foreach ($dSets[$index] as $row) {
                            $csvRow = (array) $row;

                            if ($deleteOpt) {
                                $csvRow['deleted_dset_db'] = !empty($deletedDataSet[$index]) ? 'yes' : 'no';
                                $csvRow['deleted_res_db'] = !empty($deletedResources[$index]) ? $deletedResources[$index] : 0;
                                $csvRow['deleted_es'] = !empty($deletedIndex['acknowledged']) ? 'yes' : 'no';
                            }

                            fputcsv($csvFile, $csvRow, ',', "'", "\\");
                        }
                    } else {
                        $csvRow = [$index, 'n/a', 'n/a', 'n/a', 'n/a', 'n/a', 'n/a', 'n/a'];
                        fputcsv($csvFile, $csvRow, ',', "'", "\\");
                    }
                }

                $progressBar->advance();
            });
        }

        fclose($csvFile);
        $progressBar->finish();
        $this->info("\n");

        if (!$this->failed) {
            $this->info('Red indices not found..');
        } else {
            $this->info($this->failed .' red indices found..');
        }
    }
}

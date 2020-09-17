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
    protected $failed = false;

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

        $dSets = \DB::select(\DB::raw($query));
        $progressBar = $this->output->createProgressBar(count($dSets));
        $progressBar->start();

        $csvFile = fopen($path, 'w') or die('Unable to create new file :'. $path);
        $csvHead = $deleteOpt
            ? ['id', 'uri', 'org_id', 'org_name', 'firstname', 'lastname', 'username', 'email', 'deleted_db', 'deleted_es']
            : ['id', 'uri', 'org_id', 'org_name', 'firstname', 'lastname', 'username', 'email'] ;

        fputcsv($csvFile, $csvHead, ',', "'", "\\");

        collect($dSets)->map(function($set) use($progressBar, $csvFile, $deleteOpt) {
            $this->output->write('<info> index: '. $set->id .'...</info>');

            $exists = \Elasticsearch::indices()->exists(['index' => $set->id]);

            if ($exists) {
                $indexStat = \Elasticsearch::indices()->stats(['index' => $set->id]);

                if (isset($indexStat['_all'])) {
                    if (isset($indexStat['_all']['primaries'])) {
                        if (isset($indexStat['_all']['primaries']['indexing'])) {
                            if ($indexStat['_all']['primaries']['indexing']['index_failed']) {
                                $this->failed = true;
                                $deletedResources = false;
                                $deletedDataSet = false;
                                $csvRow = (array) $set;

                                $deletedIndex = \Elasticsearch::indices()->delete(['index' => $set->id]);

                                if (!empty($deletedIndex['acknowledged'])) {
                                    DB::beginTransaction();

                                    $deletedResources = Resource::where('data_set_id', $set->id)->delete();
                                    $deletedDataSet = DataSet::where('id', $set->id)->delete();

                                    if ($deletedResources && $deletedDataSet) {
                                        DB::commit();
                                    } else {
                                        DB::rollBack();
                                    }
                                }

                                if ($deleteOpt) {
                                    $csvRow['deleted_db'] = $deletedResources && $deletedDataSet ? 'yes' : 'no';
                                    $csvRow['deleted_es'] = !empty($deletedIndex['acknowledged']) ? 'yes' : 'no';
                                }

                                fputcsv($csvFile, $csvRow, ',', "'", "\\");
                            }
                        }
                    }
                }
            }

            $progressBar->advance();
        });

        fclose($csvFile);
        $progressBar->finish();

        if (!$this->failed) {
            $this->info("\n");
            $this->info('Failed indices not found..');
        }
    }
}

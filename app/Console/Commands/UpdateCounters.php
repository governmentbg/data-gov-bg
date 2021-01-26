<?php

namespace App\Console\Commands;

use App\Module;
use App\ActionsHistory;
use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use \App\Http\Controllers\Api\UserController as ApiUser;
use \App\Http\Controllers\Api\DataSetController as ApiDataSet;
use \App\Http\Controllers\Api\OrganisationController as ApiOrganisation;
use \App\Http\Controllers\Api\ActionsHistoryController as ApiActionsHistory;
use App\Http\Controllers\Api\NewsController as ApiNews;

class UpdateCounters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:counters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update counters';

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

        $rq = Request::create('/api/listActionHistory', 'POST', [
            'records_per_page'  => 1,
            'criteria'          => [
                'action'            => ActionsHistory::TYPE_MOD,
                'module'            => [
                    Module::getModuleName(Module::DATA_SETS),
                    Module::getModuleName(Module::RESOURCES),
                ],
            ],
        ]);
        $api = new ApiActionsHistory($rq);
        $result = $api->listActionHistory($rq)->getData();
        $updates = $result->total_records;

        Cache::forever('home_updates', $updates);

        $rq = Request::create('/api/userCount', 'POST');
        $api = new ApiUser($rq);
        $result = $api->userCount($rq)->getData();
        $users = $result->count;

        Cache::forever('home_users', $users);

        $rq = Request::create('/api/listOrganisations', 'POST', [
            'records_per_page'  => 1,
            'criteria'          => [
                'active'            => true,
            ],
        ]);
        $api = new ApiOrganisation($rq);
        $result = $api->listOrganisations($rq)->getData();
        $organisations = $result->total_records;

        Cache::forever('home_organisations', $organisations);

        $rq = Request::create('/api/listDatasets', 'POST', ['records_per_page' => 1]);
        $api = new ApiDataSet($rq);
        $sets = $api->listDatasets($rq)->getData();
        $datasets = $sets->total_records;

        Cache::forever('home_datasets', $datasets);

        $rq = Request::create('/api/getMostActiveOrganisation', 'POST', [
            'locale'    => App::getLocale(),
        ]);
        $api = new ApiOrganisation($rq);
        $result = $api->getMostActiveOrganisation($rq)->getData();

        $mostActiveOrg = [];

        if ($result->success) {
            Cache::forever('home_active', $result->data);
        }


        # Get news count
        $newsRequest = Request::create('/api/listNews', 'POST', [
          'records_per_page'  => 1,
          'criteria'         => [
            'active'   => true,
            'order'    => [
              'type'     => 'desc',
              'field'    => 'created_at'
            ]
          ]
        ]);
        $apiNews = new ApiNews($newsRequest);
        $result = $apiNews->listNews($newsRequest)->getData();
        $news = $result->news;
        //$total_news = $result->total_records;
        Cache::forever('latest_news', $news);



        $elaspsedTime = microtime(true) - $start;
        $hours = floor($elaspsedTime / 3600);
        $mins = floor($elaspsedTime / 60 % 60);
        $secs = floor($elaspsedTime % 60);
        $time = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

        $this->info($this->description .' finished in '. $time);
    }
}

<?php

namespace App\Http\Controllers;

use App\User;
use App\Module;
use App\ActionsHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use App\Http\Controllers\Api\CategoryController as ApiCategories;
use App\Http\Controllers\Api\UserController as ApiUser;
use App\Http\Controllers\Api\DataSetController as ApiDataSet;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;
use App\Http\Controllers\Api\ActionsHistoryController as ApiActionsHistory;

class HomeController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $class = 'index';

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

        $rq = Request::create('/api/userCount', 'POST');
        $api = new ApiUser($rq);
        $result = $api->userCount($rq)->getData();
        $users = $result->count;

        $rq = Request::create('/api/listOrganisations', 'POST', [
            'records_per_page'  => 1,
            'criteria'          => [
                'active'            => true,
            ],
        ]);
        $api = new ApiOrganisation($rq);
        $result = $api->listOrganisations($rq)->getData();
        $organisations = $result->total_records;

        $rq = Request::create('/api/listDatasets', 'POST');
        $api = new ApiDataSet($rq);
        $sets = $api->listDatasets($rq)->getData();
        $datasets = $sets->total_records;

        $lastMonth = __('custom.'. strtolower(date('F', strtotime('last month'))));
        $lastMonth .= ' '. date('Y', strtotime('last month'));

        $rq = Request::create('/api/getMostActiveOrganisation', 'POST', [
            'locale'    => App::getLocale(),
        ]);
        $api = new ApiOrganisation($rq);
        $result = $api->getMostActiveOrganisation($rq)->getData();

        $mostActiveOrg = [];

        if ($result->success) {
            $mostActiveOrg = $result->data;
        }

        $params = [
            'criteria' => [
                'active' => 1
            ]
        ];

        $categoryReq = Request::create('/api/listMainCategories', 'POST', $params);
        $categoryApi = new ApiCategories($categoryReq);
        $resultCategories = $categoryApi->listMainCategories($categoryReq)->getData();
        $categories = $resultCategories->categories;

        return view('/home/index', compact(
            'class',
            'updates',
            'users',
            'organisations',
            'datasets',
            'lastMonth',
            'mostActiveOrg',
            'categories'
        ));
    }
}

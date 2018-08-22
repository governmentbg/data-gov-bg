<?php

namespace App\Http\Controllers;

use App\User;
use App\ActionsHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
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
            'page_number'       => 1,
            'criteria' => [
                'module'    => ['Resource', 'Dataset'],
                'action'    => ActionsHistory::TYPE_MOD,
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
            'page_number'       => 1,
            'criteria'          => [
                'active'          => true,
            ],
        ]);
        $api = new ApiOrganisation($rq);
        $result = $api->listOrganisations($rq)->getData();
        $organisations = $result->total_records;

        $rq = Request::create('/api/listDataSets', 'POST', [
            'records_per_page'  => 1,
            'page_number'       => 1,
            'criteria'          => [
                'active'            => true,
                'approved'          => true,
            ],
        ]);
        $api = new ApiDataSet($rq);
        $result = $api->listDataSets($rq)->getData();
        $datasets = $result->total_records;


        return view('/home/index', compact('class', 'updates', 'users', 'organisations', 'datasets'));
    }
}

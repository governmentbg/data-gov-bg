<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Api\DataSetController as ApiDataSets;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisations;

class UserController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {


        return view('user/newsFeed', ['class' => 'user']);
    }

    public function datasets(Request $request) {
        $params['api_key'] = \Auth::user()->api_key;
        $params['criteria']['created_by'] = \Auth::user()->id;
        $params['records_per_page'] = '10';
        $params['page_number'] = '1';

        $request = Request::create('/api/listDataSets', 'POST', $params);
        $api = new ApiDataSets($request);
        $datasets = $api->listDataSets($request)->getData();

        return view('user/datasets', ['class' => 'user', 'datasets' => $datasets->datasets]);
    }

    public function datasetView(Request $request) {
        $params['dataset_uri'] = $request->uri;

        $request = Request::create('/api/getDataSetDetails', 'POST', $params);
        $api = new ApiDataSets($request);
        $dataset = $api->getDataSetDetails($request)->getData();

        return view('user/datasetView', ['class' => 'user', 'dataset' => $dataset->data]);
    }

    public function deleteDataset(Request $request)
    {
        $params['api_key'] = \Auth::user()->api_key;
        $params['dataset_uri'] = $request->input('dataset_uri');

        $request = Request::create('/api/deleteDataSet', 'POST', $params);
        $api = new ApiDataSets($request);
        $datasets = $api->deleteDataSet($request)->getData();

        return redirect('user/datasets');
    }

    public function create() {
    }

    public function translate() {
    }

    public function settigns() {
    }

    public function registration() {
    }

    public function orgRegistration() {
    }

    public function createLicense() {
    }

    public function organisations(Request $request)
    {
        $perPage = 6;
        $params = [
            'api_key'        => \Auth::user()->api_key,
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];

        $request = Request::create('/api/getOrganisations', 'POST', $params);
        $api = new ApiOrganisations($request);
        $result = $api->getOrganisations($request)->getData();

        $paginationData = $this->getPaginationData($result->organisations, $result->total_records, [], $perPage);

        return view(
            'user/organisations',
            [
                'class'         => 'user',
                'organisations' => $paginationData['items'],
                'pagination'    => $paginationData['paginate']
            ]
        );
    }

    public function deleteOrg(Request $request)
    {
        $params = [
            'api_key' => \Auth::user()->api_key,
            'org_id'  => $request->id,
        ];

        $request = Request::create('/api/deleteOrganisation', 'POST', $params);
        $api = new ApiOrganisations($request);
        $result = $api->deleteOrganisation($request)->getData();

        return redirect('/user/organisations');
    }

    public function searchOrg(Request $request)
    {
        $search = $request->q;

        if (empty(trim($search))) {
            return redirect('/user/organisations');
        }

        $perPage = 6;
        $params = [
            'api_key'          => \Auth::user()->api_key,
            'criteria'         => ['keywords' => $search],
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];

        $request = Request::create('/api/searchOrganisations', 'POST', $params);
        $api = new ApiOrganisations($request);
        $result = $api->searchOrganisations($request)->getData();
        $organisations = !empty($result->organisations) ? $result->organisations : [];
        $count = !empty($result->total_records) ? $result->total_records : 0;

        $getParams = [
            'q' => $search
        ];

        $paginationData = $this->getPaginationData($organisations, $count, $getParams, $perPage);

        return view(
            'user/organisations',
            [
                'class'         => 'user',
                'organisations' => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'search'        => $search
            ]
        );
    }
}

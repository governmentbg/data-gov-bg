<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\DataSetController as ApiDataSets;

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

}

<?php

namespace App\Http\Controllers;

use App\User;
use App\UserSetting;
use App\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Input;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Api\UserController as ApiUser;
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

    public function registration(Request $request) {
        $class = 'user';
        $message = 'Пратено е съобщение за потвърждение, на посоченият от вас адрес';
        $params = [];
        $error = [];

        $digestFreq = UserSetting::getDigestFreq();

        if ($request->isMethod('post')) {
            $params = $request->all();
            error_log('params: '. print_r($params, true));

            $req = Request::create('/register', 'POST', ['data' => $params]);
            $api = new ApiUser($req);
            $result = $api->register($req)->getData();

            if ($result->success) {
                $user = User::where('api_key', $result->api_key)->first();

                $mailData = [
                    'user'  => $user->firstname,
                    'hash'  => $user->hash_id,
                ];

                Mail::send('mail/confirmationMail', $mailData, function ($m) use ($params) {
                    $m->from('info@finite-soft.com', 'Open Data');
                    $m->to($params['email'], $params['firstname'])->subject('Your account has been created!');
                });

                if (count(Mail::failures()) > 0) {
                    $error['mail'] = 'Failed to send mail';
                } else {
                    if ($request->has('add_org')) {
                        $key = $user->username;

                        return redirect()->route(
                            'orgRegistration', compact('key', 'message')
                        );
                    }
                    $class = 'index';

                    return view('/home/login', compact('message', 'class'));
                }
            } else {
                $error = $result->errors;
            }
        }

        return view('user/registration', compact('class', 'error', 'digestFreq'));
    }

    public function orgRegistration(Request $request) {
        $class = 'user';
        $params = [];
        $error = [];
        $username = $request->offsetGet('key');
        $orgTypes = Organisation::getPublicTypes();

        if (!empty($username)) {
            if ($request->isMethod('post')) {
                $user = User::where('username', $username)->first();
                $params = $request->all();
                $apiKey = $user->api_key;

                if (!empty($params['logo'])) {
                    try {
                        $img = \Image::make($params['logo']);
                    } catch (NotReadableException $ex) {
                        Log::error($ex->getMessage());
                    }

                    if (!empty($img)) {
                        $img->resize(300, 200);
                        $params['logo_filename'] = $params['logo']->getClientOriginalName();
                        $params['logo_mimetype'] = $img->mime();
                        $params['logo_data'] = $img->encode('data-url');

                        unset($params['logo']);
                    }
                }

                $params['locale'] = \LaravelLocalization::getCurrentLocale();

                if (empty($params['type'])) {
                    $params['type'] = Organisation::TYPE_CIVILIAN;
                }

                $req = Request::create('/addOrganisation', 'POST', ['api_key' => $apiKey,'data' => $params]);
                $api = new ApiOrganisations($req);
                $result = $api->addOrganisation($req)->getData();

                if ($result->success) {
                    $class = 'index';
                    $message = 'Успешно създадена организация!';

                    return view('home/login', compact('message', 'class'));
                } else {
                    $error = $result->errors;
                }
            }
        }

        return view('user/orgRegistration', compact('class', 'error', 'orgTypes'));
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

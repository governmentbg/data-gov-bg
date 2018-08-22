<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;
use App\Http\Controllers\Api\UserController as ApiUser;
use App\Http\Controllers\Api\UserFollowController as ApiFollow;

class OrganisationController extends Controller {

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
    public function index() {

    }

    /**
     * Loads a view for browsing organisations
     *
     * @param Request $request
     *
     * @return view for browsing organisations
     */
    public function list(Request $request)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        $rq = Request::create('/api/listOrganisationTypes', 'GET', ['locale' => $locale]);
        $api = new ApiOrganisation($rq);
        $result = $api->listOrganisationTypes($rq)->getData();
        $orgTypes = $result->success ? $result->types : [];

        if ($request->filled('type')) {
            $type = $request->type;
        } else {
            $type = !empty($orgTypes) ? array_first($orgTypes)->id : '';
        }

        $perPage = 6;
        $params = [
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
            'criteria'         => [
                'active'   => true,
                'approved' => true,
                'type'     => $type,
                'locale'   => $locale
            ]
        ];

        if ($request->has('sort')) {
            $params['criteria']['order']['field'] = $request->sort;
            if ($request->has('order')) {
                $params['criteria']['order']['type'] = $request->order;
            }
        }

        $rq = Request::create('/api/listOrganisations', 'POST', $params);
        $api = new ApiOrganisation($rq);
        $result = $api->listOrganisations($rq)->getData();

        $paginationData = $this->getPaginationData(
            $result->success ? $result->organisations : [],
            $result->success ? $result->total_records : 0,
            array_except(app('request')->input(), ['q', 'page',]),
            $perPage
        );

        return view(
            'organisation.list',
            [
                'class'         => 'organisation',
                'organisations' => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'orgTypes'      => $orgTypes,
                'type'          => $type
            ]
        );
    }

    /**
     * Loads a view for searching organisations
     *
     * @param Request $request
     *
     * @return view with a list of organisations or
     * a list of filtered organisations if search string is provided
     */
    public function search(Request $request)
    {
        $search = $request->q;

        if (empty(trim($search))) {
            return redirect()->route('orgList', array_except(app('request')->query(), ['q']));
        }

        $locale = \LaravelLocalization::getCurrentLocale();

        $rq = Request::create('/api/listOrganisationTypes', 'GET', ['locale' => $locale]);
        $api = new ApiOrganisation($rq);
        $result = $api->listOrganisationTypes($rq)->getData();
        $orgTypes = $result->success ? $result->types : [];

        if ($request->filled('type')) {
            $type = $request->type;
        } else {
            $type = !empty($orgTypes) ? array_first($orgTypes)->id : '';
        }

        $perPage = 6;
        $params = [
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
            'criteria'         => [
                'active'   => true,
                'approved' => true,
                'type'     => $type,
                'keywords' => $search,
                'locale'   => $locale
            ]
        ];

        if (isset($request->sort)) {
            $params['criteria']['order']['field'] = $request->sort;
            if (isset($request->order)) {
                $params['criteria']['order']['type'] = $request->order;
            }
        }

        $rq = Request::create('/api/searchOrganisations', 'POST', $params);
        $api = new ApiOrganisation($rq);
        $result = $api->listOrganisations($rq)->getData();
        $organisations = !empty($result->organisations) ? $result->organisations : [];
        $count = !empty($result->total_records) ? $result->total_records : 0;

        $getParams = ['q' => $search, 'type' => $type];
        if ($request->has('sort')) {
            $getParams['sort'] = $request->sort;
        }
        if ($request->has('order')) {
            $getParams['order'] = $request->order;
        }

        $paginationData = $this->getPaginationData(
            $organisations,
            $count,
            $getParams,
            $perPage
        );

        return view(
            'organisation.list',
            [
                'class'         => 'organisation',
                'organisations' => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'search'        => $search,
                'orgTypes'      => $orgTypes,
                'type'          => $type
            ]
        );
    }

    public function view(Request $request, $uri)
    {
        $params = [
            'org_uri' => $uri,
            'locale'  => \LaravelLocalization::getCurrentLocale()
        ];
        $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
        $api = new ApiOrganisation($rq);
        $result = $api->getOrganisationDetails($rq)->getData();

        if ($result->success && !empty($result->data) && $result->data->active && $result->data->approved) {
            $params = [
                'criteria'     => [
                    'org_id'   => $result->data->id,
                    'active'   => true,
                    'approved' => true,
                    'locale'   => \LaravelLocalization::getCurrentLocale()
                ]
            ];
            $rq = Request::create('/api/listOrganisations', 'POST', $params);
            $api = new ApiOrganisation($rq);
            $res = $api->listOrganisations($rq)->getData();
            $childOrgs = $res->success ? $res->organisations : [];

            $parentOrg = null;
            if (isset($result->data->parent_org_id)) {
                $params = [
                    'org_id' => $result->data->parent_org_id,
                    'locale'    => \LaravelLocalization::getCurrentLocale()
                ];
                $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
                $api = new ApiOrganisation($rq);
                $res = $api->getOrganisationDetails($rq)->getData();
                if ($res->success) {
                    $parentOrg = $res->data;
                }
            }

            $followed = false;
            if ($user = \Auth::user()) {
                $params = [
                    'api_key' => $user->api_key,
                    'id'      => $user->id
                ];
                $rq = Request::create('/api/getUserSettings', 'POST', $params);
                $api = new ApiUser($rq);
                $res = $api->getUserSettings($rq)->getData();
                if (!empty($res->user) && !empty($res->user->follows)) {
                    $followedOgrs = array_where(array_pluck($res->user->follows, 'org_id'), function ($value, $key) {
                        return !is_null($value);
                    });
                    if (in_array($result->data->id, $followedOgrs)) {
                        $followed = true;
                    }
                }
            }

            if (!$followed) {
                if ($request->has('follow')) {
                    $followRq = Request::create('api/addFollow', 'POST', [
                        'api_key' => $user->api_key,
                        'user_id' => $user->id,
                        'org_id'  => $result->data->id,
                    ]);
                    $apiFollow = new ApiFollow($followRq);
                    $followResult = $apiFollow->addFollow($followRq)->getData();
                    if ($followResult->success) {
                        return back();
                    }
                }
            } else {
                if ($request->has('unfollow')) {
                    $followRq = Request::create('api/unFollow', 'POST', [
                        'api_key' => $user->api_key,
                        'user_id' => $user->id,
                        'org_id'  => $result->data->id,
                    ]);
                    $apiFollow = new ApiFollow($followRq);
                    $followResult = $apiFollow->unFollow($followRq)->getData();
                    if ($followResult->success) {
                        return back();
                    }
                }
            }

            return view(
                'organisation/profile',
                [
                    'class'        => 'organisation',
                    'organisation' => $result->data,
                    'childOrgs'    => $childOrgs,
                    'parentOrg'    => $parentOrg,
                    'followed'     => $followed
                ]
            );
        }

        return redirect('organisation.list');
    }

    public function follow(Request $request, $uri)
    {
        if ($user = \Auth::user()) {
            $params = [
                'org_uri' => $uri,
                'locale'    => \LaravelLocalization::getCurrentLocale()
            ];
            $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
            $api = new ApiOrganisation($rq);
            $result = $api->getOrganisationDetails($rq)->getData();

            if ($result->success && !empty($result->data) && $result->data->active && $result->data->approved) {
                if ($request->has('follow')) {
                    $follow = Request::create('api/addFollow', 'POST', [
                        'api_key' => $user->api_key,
                        'user_id' => $user->id,
                        'org_id'  => $result->data->id,
                    ]);

                    $followResult = $apiFollow->addFollow($follow)->getData();

                    if ($followResult->success) {

                        return back();
                    }
                }
            }
        }

        return redirect()->back()->withInput($request-all());
    }

    public function datasets() {

    }

    public function viewDataset() {

    }

    public function chronology() {

    }
}

<?php

namespace App\Http\Controllers;

use App\Role;
use App\ActionsHistory;
use App\Module;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;
use App\Http\Controllers\Api\UserController as ApiUser;
use App\Http\Controllers\Api\UserFollowController as ApiFollow;
use App\Http\Controllers\Api\DataSetController as ApiDataSet;
use App\Http\Controllers\Api\ActionsHistoryController as ApiActionsHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $locale = \LaravelLocalization::getCurrentLocale();

        $params = [
            'org_uri' => $uri,
            'locale'  => $locale
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
                    'locale'   => $locale
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
                    'locale' => $locale
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

        return redirect('organisation');
    }

    public function datasets(Request $request, $uri)
    {
        return view(
            'organisation/datasets',
            [
                'class' => 'organisation',
                'uri'   => $uri
            ]
        );

    }

    public function viewDataset()
    {

    }

    public function chronology(Request $request, $uri)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        $params = [
            'org_uri' => $uri,
            'locale'  => $locale
        ];

        $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
        $api = new ApiOrganisation($rq);
        $result = $api->getOrganisationDetails($rq)->getData();

        if (isset($result->success) && $result->success && !empty($result->data) &&
            $result->data->active && $result->data->approved) {

            $params = [
                'criteria' => [
                    'org_id' => $result->data->id,
                    'locale' => $locale
                ]
            ];
            $rq = Request::create('/api/listDatasets', 'POST', $params);
            $api = new ApiDataSet($rq);
            $res = $api->listDatasets($rq)->getData();

            $criteria = [
                'org_ids' => [$result->data->id]
            ];
            $objType = Module::getModules(Module::ORGANISATIONS);
            $actObjData[$objType] = [];
            $actObjData[$objType][$result->data->id] = [
                'obj_id'        => $result->data->uri,
                'obj_name'      => $result->data->name,
                'obj_module'    => Str::lower(utrans('custom.organisations')),
                'obj_type'      => 'org',
                'obj_view'      => '/organisation/profile/'. $result->data->uri,
                'parent_obj_id' => ''
            ];

            if (isset($res->success) && $res->success && !empty($res->datasets)) {
                $objType = Module::getModules(Module::DATA_SETS);
                $objTypeRes = Module::getModules(Module::RESOURCES);
                $actObjData[$objType] = [];

                foreach ($res->datasets as $dataset) {
                    $criteria['dataset_ids'][] = $dataset->id;
                    $actObjData[$objType][$dataset->id] = [
                        'obj_id'        => $dataset->uri,
                        'obj_name'      => $dataset->name,
                        'obj_module'    => Str::lower(__('custom.dataset')),
                        'obj_type'      => 'dataset',
                        'obj_view'      => '/data/view/'. $dataset->uri,
                        'parent_obj_id' => ''
                    ];

                    if (!empty($dataset->resource)) {
                        foreach ($dataset->resource as $resource) {
                            $criteria['resource_uris'][] = $resource->uri;
                            $actObjData[$objTypeRes][$resource->uri] = [
                                'obj_id'            => $resource->uri,
                                'obj_name'          => $resource->name,
                                'obj_module'        => Str::lower(__('custom.resource')),
                                'obj_type'          => 'resource',
                                'obj_view'          => '/data/resourceView/'. $resource->uri,
                                'parent_obj_id'     => $dataset->uri,
                                'parent_obj_name'   => $dataset->name,
                                'parent_obj_module' => Str::lower(__('custom.dataset')),
                                'parent_obj_type'   => 'dataset',
                                'parent_obj_view'   => '/data/view/'. $dataset->uri
                            ];
                        }
                    }
                }
            }

            $paginationData = [];
            $actTypes = [];

            if (!empty($criteria)) {
                $rq = Request::create('/api/listActionTypes', 'GET', ['locale' => $locale, 'publicOnly' => true]);
                $api = new ApiActionsHistory($rq);
                $res = $api->listActionTypes($rq)->getData();

                if ($res->success && !empty($res->types)) {
                    $linkWords = ActionsHistory::getTypesLinkWords();
                    foreach ($res->types as $type) {
                        $actTypes[$type->id] = [
                            'name'     => $type->name,
                            'linkWord' => $linkWords[$type->id]
                        ];
                    }

                    $criteria['actions'] = array_keys($actTypes);
                    $perPage = 10;
                    $params = [
                        'criteria'         => $criteria,
                        'records_per_page' => $perPage,
                        'page_number'      => !empty($request->page) ? $request->page : 1,
                    ];

                    $rq = Request::create('/api/listActionHistory', 'POST', $params);
                    $api = new ApiActionsHistory($rq);
                    $res = $api->listActionHistory($rq)->getData();
                    $res->actions_history = isset($res->actions_history) ? $res->actions_history : [];
                    $paginationData = $this->getPaginationData($res->actions_history, $res->total_records, [], $perPage);
                }
            }

            return view(
                'organisation/chronology',
                [
                    'class'          => 'organisation',
                    'organisation'   => $result->data,
                    'chronology'     => !empty($paginationData) ? $paginationData['items'] : [],
                    'pagination'     => !empty($paginationData) ? $paginationData['paginate'] : [],
                    'actionObjData'  => $actObjData,
                    'actionTypes'    => $actTypes,
                ]
            );
        }

        return redirect('organisation');
    }
}

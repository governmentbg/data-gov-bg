<?php

namespace App\Http\Controllers;

use App\Role;
use App\RoleRight;
use App\ActionsHistory;
use App\Module;
use App\Organisation;
use App\DataSet;
use App\Resource;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;
use App\Http\Controllers\Api\UserController as ApiUser;
use App\Http\Controllers\Api\UserFollowController as ApiFollow;
use App\Http\Controllers\Api\DataSetController as ApiDataSet;
use App\Http\Controllers\Api\ActionsHistoryController as ApiActionsHistory;
use App\Http\Controllers\Api\CategoryController as ApiCategory;
use App\Http\Controllers\Api\TagController as ApiTag;
use App\Http\Controllers\Api\TermsOfUseController as ApiTermsOfUse;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\ConversionController as ApiConversion;
use App\Http\Controllers\Api\SignalController as ApiSignal;
use Illuminate\Http\Request;

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
    public function index()
    {

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

        // list organisation types
        $rq = Request::create('/api/listOrganisationTypes', 'GET', ['locale' => $locale]);
        $api = new ApiOrganisation($rq);
        $result = $api->listOrganisationTypes($rq)->getData();
        $orgTypes = $result->success ? $result->types : [];

        $getParams = [];

        // apply type filter
        if ($request->filled('type')) {
            $getParams['type'] = $request->type;
        } else {
            $getParams['type'] = !empty($orgTypes) ? array_first($orgTypes)->id : '';
        }

        $perPage = 6;
        $params = [
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
            'criteria'         => [
                'active'   => Organisation::ACTIVE_TRUE,
                'approved' => Organisation::APPROVED_TRUE,
                'type'     => $getParams['type'],
                'locale'   => $locale
            ]
        ];

        // apply search
        if ($request->filled('q') && !empty(trim($request->q))) {
            $getParams['q'] = trim($request->q);
            $params['criteria']['keywords'] = $getParams['q'];
        }

        // apply sort parameters
        if ($request->has('sort')) {
            $params['criteria']['order']['field'] = $request->sort;
            $getParams['sort'] = $request->sort;
        } else {
            $params['criteria']['order']['field'] = 'name';
        }
        if ($request->has('order')) {
            $params['criteria']['order']['type'] = $request->order;
            $getParams['order'] = $request->order;
        } else {
            $params['criteria']['order']['type'] = 'asc';
        }

        // list organisations
        $rq = Request::create('/api/listOrganisations', 'POST', $params);
        $api = new ApiOrganisation($rq);
        $result = $api->listOrganisations($rq)->getData();

        $organisations = !empty($result->organisations) ? $result->organisations : [];
        $count = !empty($result->total_records) ? $result->total_records : 0;

        $paginationData = $this->getPaginationData($organisations, $count, $getParams, $perPage);

        $buttons = [];
        if (\Auth::check()) {
            // check rights for add button
            $rightCheck = RoleRight::checkUserRight(Module::ORGANISATIONS, RoleRight::RIGHT_EDIT);
            $buttons['add'] = $rightCheck;

            foreach ($paginationData['items'] as $organisation) {
                $checkData = [
                    'org_id' => $organisation->id
                ];
                $objData = [
                    'org_id'      => $organisation->id,
                    'created_by'  => $organisation->created_by
                ];

                // check rights for edit button
                $rightCheck = RoleRight::checkUserRight(Module::ORGANISATIONS, RoleRight::RIGHT_EDIT, $checkData, $objData);
                $buttons[$organisation->id]['edit'] = $rightCheck;

                // check rights for delete button
                $rightCheck = RoleRight::checkUserRight(Module::ORGANISATIONS, RoleRight::RIGHT_ALL, $checkData, $objData);
                $buttons[$organisation->id]['delete'] = $rightCheck;
            }

            $buttons['rootUrl'] = Role::isAdmin() ? 'admin' : 'user';
        }

        return view(
            'organisation.list',
            [
                'class'         => 'organisation',
                'organisations' => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'orgTypes'      => $orgTypes,
                'getParams'     => $getParams,
                'buttons'       => $buttons
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
        $res = $api->getOrganisationDetails($rq)->getData();
        $organisation = !empty($res->data) ? $res->data : [];

        if (!empty($organisation) &&
            $organisation->active == Organisation::ACTIVE_TRUE &&
            $organisation->approved == Organisation::APPROVED_TRUE) {

            // get child organisations
            $params = [
                'criteria'     => [
                    'org_id'   => $organisation->id,
                    'active'   => Organisation::ACTIVE_TRUE,
                    'approved' => Organisation::APPROVED_TRUE,
                    'locale'   => $locale
                ]
            ];
            $rq = Request::create('/api/listOrganisations', 'POST', $params);
            $api = new ApiOrganisation($rq);
            $res = $api->listOrganisations($rq)->getData();
            $childOrgs = !empty($res->organisations) ? $res->organisations : [];

            $parentOrg = null;
            if (isset($organisation->parent_org_id)) {
                // get parent organisation details
                $params = [
                    'org_id' => $organisation->parent_org_id,
                    'locale' => $locale
                ];
                $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
                $api = new ApiOrganisation($rq);
                $res = $api->getOrganisationDetails($rq)->getData();
                if ($res->success) {
                    $parentOrg = $res->data;
                }
            }

            $buttons = [];
            if ($authUser = \Auth::user()) {
                $objData = ['object_id' => $authUser->id];
                $rightCheck = RoleRight::checkUserRight(Module::USERS, RoleRight::RIGHT_EDIT, [], $objData);
                if ($rightCheck) {
                    $userData = [
                        'api_key' => $authUser->api_key,
                        'id'      => $authUser->id
                    ];

                    // get followed organisations
                    $followed = [];
                    if ($this->getFollowed($userData, 'org_id', $followed)) {
                        if (!in_array($organisation->id, $followed)) {
                            $buttons['follow'] = true;
                        } else {
                            $buttons['unfollow'] = true;
                        }

                        // follow / unfollow organisation
                        $followReq = $request->only(['follow', 'unfollow']);
                        $followResult = $this->followObject($followReq, $userData, $followed, 'org_id', [$organisation->id]);
                        if (!empty($followResult) && $followResult->success) {
                            return back();
                        }
                    }
                }

                $checkData = [
                    'org_id' => $organisation->id
                ];
                $objData = [
                    'org_id'      => $organisation->id,
                    'created_by'  => $organisation->created_by
                ];

                // check rights for edit button
                $rightCheck = RoleRight::checkUserRight(Module::ORGANISATIONS, RoleRight::RIGHT_EDIT, $checkData, $objData);
                $buttons['edit'] = $rightCheck;

                // check rights for delete button
                $rightCheck = RoleRight::checkUserRight(Module::ORGANISATIONS, RoleRight::RIGHT_ALL, $checkData, $objData);
                $buttons['delete'] = $rightCheck;

                $buttons['rootUrl'] = Role::isAdmin() ? 'admin' : 'user';
            }

            return view(
                'organisation/profile',
                [
                    'class'        => 'organisation',
                    'organisation' => $organisation,
                    'childOrgs'    => $childOrgs,
                    'parentOrg'    => $parentOrg,
                    'buttons'      => $buttons
                ]
            );
        }

        return redirect()->back();
    }

    private function getFollowed($userData, $followType, &$followed)
    {
        $followed = [];

        $rq = Request::create('/api/getUserSettings', 'POST', $userData);
        $api = new ApiUser($rq);
        $res = $api->getUserSettings($rq)->getData();

        if (isset($res->user) && !empty($res->user)) {
            if (!empty($res->user->follows)) {
                $followed = array_where(array_pluck($res->user->follows, $followType), function ($value, $key) {
                    return !is_null($value);
                });
            }

            return true;
        }

        return false;
    }

    private function followObject($followReq, $userData, $followed, $followType, $objIds)
    {
        $followResult = null;

        if (isset($followReq['follow'])) {
            // follow object
            if (in_array($followReq['follow'], $objIds) && !in_array($followReq['follow'], $followed)) {
                $followRq = Request::create('api/addFollow', 'POST', [
                    'api_key'    => $userData['api_key'],
                    'user_id'    => $userData['id'],
                    $followType  => $followReq['follow'],
                ]);
                $apiFollow = new ApiFollow($followRq);
                $followResult = $apiFollow->addFollow($followRq)->getData();
            }
        } elseif (isset($followReq['unfollow'])) {
            // unfollow object
            if (in_array($followReq['unfollow'], $objIds) && in_array($followReq['unfollow'], $followed)) {
                $followRq = Request::create('api/unFollow', 'POST', [
                    'api_key'    => $userData['api_key'],
                    'user_id'    => $userData['id'],
                    $followType  => $followReq['unfollow'],
                ]);
                $apiFollow = new ApiFollow($followRq);
                $followResult = $apiFollow->unFollow($followRq)->getData();
            }
        }

        return $followResult;
    }

    /**
     * Deletes an organisation
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view to previous page
     */
    public function delete(Request $request)
    {
        if (\Auth::check() && $request->has('delete') && $request->has('org_uri')) {
            $rq = Request::create('/api/getOrganisationDetails', 'POST', ['org_uri' => $request->org_uri]);
            $api = new ApiOrganisation($rq);
            $res = $api->getOrganisationDetails($rq)->getData();
            $organisation = !empty($res->data) ? $res->data : [];

            if (empty($organisation)) {
                return redirect()->back();
            }

            // check delete rights
            $checkData = [
                'org_id' => $organisation->id
            ];
            $objData = [
                'org_id'      => $organisation->id,
                'created_by'  => $organisation->created_by
            ];
            $rightCheck = RoleRight::checkUserRight(Module::ORGANISATIONS, RoleRight::RIGHT_ALL, $checkData, $objData);

            if (!$rightCheck) {
                return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
            }

            $params = [
                'api_key'   => \Auth::user()->api_key,
                'org_id'    => $organisation->id,
            ];

            $delReq = Request::create('/api/deleteOrganisation', 'POST', $params);
            $api = new ApiOrganisation($delReq);
            $result = $api->deleteOrganisation($delReq)->getData();

            if (isset($result->success) && $result->success) {
                $request->session()->flash('alert-success', __('custom.delete_success'));

                return redirect()->route('organisations', array_except($request->query(), ['page']));
            }

            $request->session()->flash('alert-danger', isset($result->error) ? $result->error->message : __('custom.delete_error'));

            return redirect()->back();
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    public function datasets(Request $request, $uri)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        // get organisation details
        $params = [
            'org_uri' => $uri,
            'locale'  => $locale
        ];
        $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
        $api = new ApiOrganisation($rq);
        $res = $api->getOrganisationDetails($rq)->getData();
        $organisation = !empty($res->data) ? $res->data : [];

        if (!empty($organisation) &&
            $organisation->active == Organisation::ACTIVE_TRUE &&
            $organisation->approved == Organisation::APPROVED_TRUE) {

            $criteria = [
                'org_ids' => [$organisation->id]
            ];

            // filters
            $categories = [];
            $tags = [];
            $formats = [];
            $termsOfUse = [];
            $getParams = [];
            $display = [];

            // main categories filter
            if ($request->filled('category') && is_array($request->category)) {
                $criteria['category_ids'] = $request->category;
                $getParams['category'] = $request->category;
            } else {
                $getParams['category'] = [];
            }

            // tags filter
            if ($request->filled('tag') && is_array($request->tag)) {
                $criteria['tag_ids'] = $request->tag;
                $getParams['tag'] = $request->tag;
            } else {
                $getParams['tag'] = [];
            }

            // data formats filter
            if ($request->filled('format') && is_array($request->format)) {
                $criteria['formats'] = array_map('strtoupper', $request->format);
                $getParams['format'] = $request->format;
            } else {
                $getParams['format'] = [];
            }

            // terms of use filter
            if ($request->filled('license') && is_array($request->license)) {
                $criteria['terms_of_use_ids'] = $request->license;
                $getParams['license'] = $request->license;
            } else {
                $getParams['license'] = [];
            }

            // prepare datasets parameters
            $perPage = 6;
            $params = [
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
                'criteria'         => $criteria,
            ];
            $params['criteria']['locale'] = $locale;
            $params['criteria']['status'] = DataSet::STATUS_PUBLISHED;
            $params['criteria']['visibility'] = DataSet::VISIBILITY_PUBLIC;

            // apply search
            if ($request->filled('q') && !empty(trim($request->q))) {
                $getParams['q'] = trim($request->q);
                $params['criteria']['keywords'] = $getParams['q'];
            }

            // apply sort parameters
            if ($request->has('sort')) {
                $getParams['sort'] = $request->sort;
                if ($request->sort != 'relevance') {
                    $params['criteria']['order']['field'] = $request->sort;
                    if ($request->has('order')) {
                        $params['criteria']['order']['type'] = $request->order;
                    }
                    $getParams['order'] = $request->order;
                }
            }

            // list datasets
            $rq = Request::create('/api/listDatasets', 'POST', $params);
            $api = new ApiDataSet($rq);
            $res = $api->listDatasets($rq)->getData();

            $datasets = !empty($res->datasets) ? $res->datasets : [];
            $count = !empty($res->total_records) ? $res->total_records : 0;

            $paginationData = $this->getPaginationData($datasets, $count, $getParams, $perPage);

            $buttons = [];

            if (!empty($paginationData['items'])) {
                $recordsLimit = 10;

                // check for category records limit
                $hasLimit = !($request->filled('category_limit') && $request->category_limit == 0);

                // list data categories
                $params = [
                    'criteria' => [
                        'dataset_criteria' => $criteria,
                        'locale' => $locale
                    ],
                ];
                if ($hasLimit) {
                    $params['criteria']['records_limit'] = $recordsLimit;
                }

                $rq = Request::create('/api/listDataCategories', 'POST', $params);
                $api = new ApiCategory($rq);
                $res = $api->listDataCategories($rq)->getData();

                $categories = !empty($res->categories) ? $res->categories : [];
                $getParams['category'] = array_intersect($getParams['category'], array_pluck($categories, 'id'));

                $this->prepareDisplayParams(count($categories), $hasLimit, $recordsLimit, 'category', $display);

                // check for tag records limit
                $hasLimit = !($request->filled('tag_limit') && $request->tag_limit == 0);

                // list data tags
                $params = [
                    'criteria' => [
                        'dataset_criteria' => $criteria
                    ],
                ];
                if ($hasLimit) {
                    $params['criteria']['records_limit'] = $recordsLimit;
                }

                $rq = Request::create('/api/listDataTags', 'POST', $params);
                $api = new ApiTag($rq);
                $res = $api->listDataTags($rq)->getData();

                $tags = !empty($res->tags) ? $res->tags : [];
                $getParams['tag'] = array_intersect($getParams['tag'], array_pluck($tags, 'id'));

                $this->prepareDisplayParams(count($tags), $hasLimit, $recordsLimit, 'tag', $display);

                // check for format records limit
                $hasLimit = !($request->filled('format_limit') && $request->format_limit == 0);

                // list data formats
                $params = [
                    'criteria' => [
                        'dataset_criteria' => $criteria,
                    ],
                ];
                if ($hasLimit) {
                    $params['criteria']['records_limit'] = $recordsLimit;
                }

                $rq = Request::create('/api/listDataFormats', 'POST', $params);
                $api = new ApiResource($rq);
                $res = $api->listDataFormats($rq)->getData();

                $formats = !empty($res->data_formats) ? $res->data_formats : [];
                $getParams['format'] = array_intersect($getParams['format'], array_map('strtolower', array_pluck($formats, 'format')));

                $this->prepareDisplayParams(count($formats), $hasLimit, $recordsLimit, 'format', $display);

                // check for terms of use records limit
                $hasLimit = !($request->filled('license_limit') && $request->license_limit == 0);

                // list data terms of use
                $params = [
                    'criteria' => [
                        'dataset_criteria' => $criteria,
                        'locale' => $locale
                    ],
                ];
                if ($hasLimit) {
                    $params['criteria']['records_limit'] = $recordsLimit;
                }

                $rq = Request::create('/api/listDataTermsOfUse', 'POST', $params);
                $api = new ApiTermsOfUse($rq);
                $res = $api->listDataTermsOfUse($rq)->getData();

                $termsOfUse = !empty($res->terms_of_use) ? $res->terms_of_use : [];
                $getParams['license'] = array_intersect($getParams['license'], array_pluck($termsOfUse, 'id'));

                $this->prepareDisplayParams(count($termsOfUse), $hasLimit, $recordsLimit, 'license', $display);

                if ($authUser = \Auth::user()) {
                    $objData = ['object_id' => $authUser->id];
                    $rightCheck = RoleRight::checkUserRight(Module::USERS, RoleRight::RIGHT_EDIT, [], $objData);
                    if ($rightCheck) {
                        $userData = [
                            'api_key' => $authUser->api_key,
                            'id'      => $authUser->id
                        ];

                        // get followed categories
                        $followed = [];
                        if ($this->getFollowed($userData, 'category_id', $followed)) {
                            foreach ($getParams['category'] as $selCategory) {
                                if (!in_array($selCategory, $followed)) {
                                    $buttons[$selCategory]['followCategory'] = true;
                                } else {
                                    $buttons[$selCategory]['unfollowCategory'] = true;
                                }
                            }

                            // follow / unfollow category
                            $followReq = [];
                            if ($request->has('followCategory')) {
                                $followReq['follow'] = $request->followCategory;
                            } elseif ($request->has('unfollowCategory')) {
                                $followReq['unfollow'] = $request->unfollowCategory;
                            }
                            $followResult = $this->followObject($followReq, $userData, $followed, 'category_id', $getParams['category']);
                            if (!empty($followResult) && $followResult->success) {
                                return back();
                            }
                        }

                        // get followed tags
                        $followed = [];
                        if ($this->getFollowed($userData, 'tag_id', $followed)) {
                            foreach ($getParams['tag'] as $selTag) {
                                if (!in_array($selTag, $followed)) {
                                    $buttons[$selTag]['followTag'] = true;
                                } else {
                                    $buttons[$selTag]['unfollowTag'] = true;
                                }
                            }

                            // follow / unfollow tag
                            $followReq = [];
                            if ($request->has('followTag')) {
                                $followReq['follow'] = $request->followTag;
                            } elseif ($request->has('unfollowTag')) {
                                $followReq['unfollow'] = $request->unfollowTag;
                            }
                            $followResult = $this->followObject($followReq, $userData, $followed, 'tag_id', $getParams['tag']);
                            if (!empty($followResult) && $followResult->success) {
                                return back();
                            }
                        }

                        // get followed datasets
                        $followed = [];
                        if ($this->getFollowed($userData, 'dataset_id', $followed)) {
                            $datasetIds = array_pluck($paginationData['items'], 'id');
                            foreach ($datasetIds as $datasetId) {
                                $buttons[$datasetId] = [
                                    'follow'   => false,
                                    'unfollow' => false,
                                ];
                                if (!in_array($datasetId, $followed)) {
                                    $buttons[$datasetId]['follow'] = true;
                                } else {
                                    $buttons[$datasetId]['unfollow'] = true;
                                }
                            }

                            // follow / unfollow dataset
                            $followReq = $request->only(['follow', 'unfollow']);
                            $followResult = $this->followObject($followReq, $userData, $followed, 'data_set_id', $datasetIds);
                            if (!empty($followResult) && $followResult->success) {
                                return back();
                            }
                        }
                    }
                }
            }

            if  (\Auth::check()) {
                // check rights for add button
                $rightCheck = RoleRight::checkUserRight(Module::DATA_SETS, RoleRight::RIGHT_EDIT);
                $buttons['add'] = $rightCheck;

                $buttons['addUrl'] = Role::isAdmin() ? '/admin/dataset/add' : '/user/dataset/create';
            }

            return view(
                'organisation/datasets',
                [
                    'class'              => 'organisation',
                    'organisation'       => $organisation,
                    'approved'           => ($organisation->type == Organisation::TYPE_COUNTRY),
                    'datasets'           => $paginationData['items'],
                    'resultsCount'       => $count,
                    'pagination'         => $paginationData['paginate'],
                    'categories'         => $categories,
                    'tags'               => $tags,
                    'formats'            => $formats,
                    'termsOfUse'         => $termsOfUse,
                    'getParams'          => $getParams,
                    'display'            => $display,
                    'buttons'            => $buttons
                ]
            );
        }

        return redirect()->back();
    }

    private function prepareDisplayParams($count, $hasLimit, $recordsLimit, $type, &$display)
    {
        if ($hasLimit && $count >= $recordsLimit) {
            $display['show_all'][$type] = true;
            $display['only_popular'][$type] = false;
        } elseif ($count > $recordsLimit) {
            $display['show_all'][$type] = false;
            $display['only_popular'][$type] = true;
        } else {
            $display['show_all'][$type] = false;
            $display['only_popular'][$type] = false;
        }
    }

    public function viewDataset(Request $request, $uri)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        // get dataset details
        $params = [
            'dataset_uri' => $uri,
            'locale'  => $locale
        ];
        $rq = Request::create('/api/getDataSetDetails', 'POST', $params);
        $api = new ApiDataSet($rq);
        $res = $api->getDataSetDetails($rq)->getData();
        $dataset = !empty($res->data) ? $res->data : [];

        if (!empty($dataset) && isset($dataset->org_id) &&
            $dataset->status == DataSet::STATUS_PUBLISHED &&
            $dataset->visibility == DataSet::VISIBILITY_PUBLIC) {

            // get organisation details
            $params = [
                'org_id' => $dataset->org_id,
                'locale'  => $locale
            ];
            $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
            $api = new ApiOrganisation($rq);
            $res = $api->getOrganisationDetails($rq)->getData();
            $organisation = !empty($res->data) ? $res->data : [];

            if (!empty($organisation) &&
                $organisation->active == Organisation::ACTIVE_TRUE &&
                $organisation->approved == Organisation::APPROVED_TRUE) {

                if (\Auth::check() && $request->has('delete')) {
                    // check delete rights
                    $checkData = [
                        'org_id' => $dataset->org_id
                    ];
                    $objData = [
                        'org_id'      => $dataset->org_id,
                        'created_by'  => $dataset->created_by
                    ];
                    $rightCheck = RoleRight::checkUserRight(Module::DATA_SETS, RoleRight::RIGHT_ALL, $checkData, $objData);

                    if ($rightCheck) {
                        $params = [
                            'api_key'      => \Auth::user()->api_key,
                            'dataset_uri'  => $dataset->uri,
                        ];

                        $delReq = Request::create('/api/deleteDataset', 'POST', $params);
                        $api = new ApiDataSet($delReq);
                        $result = $api->deleteDataset($delReq)->getData();

                        if (isset($result->success) && $result->success) {
                            $request->session()->flash('alert-success', __('custom.success_dataset_delete'));

                            return redirect()->route('orgDatasets', array_merge(array_except($request->query(), ['page']), ['uri' => $organisation->uri]));
                        }

                        $request->session()->flash('alert-danger', isset($result->error) ? $result->error->message : __('custom.fail_dataset_delete'));
                    }
                }

                $params = [
                    'criteria' => [
                        'dataset_uri' => $uri
                    ]
                ];
                $rq = Request::create('/api/listResources', 'POST', $params);
                $apiResources = new ApiResource($rq);
                $res = $apiResources->listResources($rq)->getData();
                $resources = !empty($res->resources) ? $res->resources : [];

                // get category details
                if (!empty($dataset->category_id)) {
                    $params = [
                        'category_id' => $dataset->category_id,
                        'locale'  => $locale
                    ];
                    $rq = Request::create('/api/getMainCategoryDetails', 'POST', $params);
                    $api = new ApiCategory($rq);
                    $res = $api->getMainCategoryDetails($rq)->getData();

                    $dataset->category_name = isset($res->category) && !empty($res->category) ? $res->category->name : '';
                }

                // get terms of use details
                if (!empty($dataset->terms_of_use_id)) {
                    $params = [
                        'terms_id' => $dataset->terms_of_use_id,
                        'locale'  => $locale
                    ];
                    $rq = Request::create('/api/getTermsOfUseDetails', 'POST', $params);
                    $api = new ApiTermsOfUse($rq);
                    $res = $api->getTermsOfUseDetails($rq)->getData();

                    $dataset->terms_of_use_name = isset($res->data) && !empty($res->data) ? $res->data->name : '';
                }

                $buttons = [];
                if ($authUser = \Auth::user()) {
                    $objData = ['object_id' => $authUser->id];
                    $rightCheck = RoleRight::checkUserRight(Module::USERS, RoleRight::RIGHT_EDIT, [], $objData);
                    if ($rightCheck) {
                        $userData = [
                            'api_key' => $authUser->api_key,
                            'id'      => $authUser->id
                        ];

                        // get followed datasets
                        $followed = [];
                        if ($this->getFollowed($userData, 'dataset_id', $followed)) {
                            if (!in_array($dataset->id, $followed)) {
                                $buttons['follow'] = true;
                            } else {
                                $buttons['unfollow'] = true;
                            }

                            // follow / unfollow dataset
                            $followReq = $request->only(['follow', 'unfollow']);
                            $followResult = $this->followObject($followReq, $userData, $followed, 'data_set_id', [$dataset->id]);
                            if (!empty($followResult) && $followResult->success) {
                                return back();
                            }
                        }
                    }

                    $checkData = [
                        'org_id' => $dataset->org_id
                    ];
                    $objData = [
                        'org_id'      => $dataset->org_id,
                        'created_by'  => $dataset->created_by
                    ];

                    // check rights for add resource button
                    $rightCheck = RoleRight::checkUserRight(Module::RESOURCES, RoleRight::RIGHT_EDIT, $checkData, $objData);
                    $buttons['addResource'] = $rightCheck;

                    // check rights for edit button
                    $rightCheck = RoleRight::checkUserRight(Module::DATA_SETS, RoleRight::RIGHT_EDIT, $checkData, $objData);
                    $buttons['edit'] = $rightCheck;

                    // check rights for delete button
                    $rightCheck = RoleRight::checkUserRight(Module::DATA_SETS, RoleRight::RIGHT_ALL, $checkData, $objData);
                    $buttons['delete'] = $rightCheck;

                    $buttons['rootUrl'] = Role::isAdmin() ? 'admin' : 'user';
                }

                $dataset = $this->getModelUsernames($dataset);

                return view(
                    'organisation/viewDataset',
                    [
                        'class'          => 'organisation',
                        'organisation'   => $organisation,
                        'approved'       => ($organisation->type == Organisation::TYPE_COUNTRY),
                        'dataset'        => $dataset,
                        'resources'      => $resources,
                        'buttons'        => $buttons
                    ]
                );
            }
        }

        return redirect()->back();
    }

    public function resourceView(Request $request, $uri, $version = null)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        $params = [
            'resource_uri' => $uri,
            'locale'  => $locale
        ];
        $rq = Request::create('/api/getResourceMetadata', 'POST', $params);
        $api = new ApiResource($rq);
        $res = $api->getResourceMetadata($rq)->getData();
        $resource = !empty($res->resource) ? $res->resource : [];

        if (!empty($resource) && isset($resource->dataset_uri)) {
            // get dataset details
            $params = [
                'dataset_uri' => $resource->dataset_uri,
                'locale'  => $locale
            ];
            $rq = Request::create('/api/getDataSetDetails', 'POST', $params);
            $api = new ApiDataSet($rq);
            $res = $api->getDataSetDetails($rq)->getData();
            $dataset = !empty($res->data) ? $res->data : [];

            if (!empty($dataset) && isset($dataset->org_id) &&
                $dataset->status == DataSet::STATUS_PUBLISHED &&
                $dataset->visibility == DataSet::VISIBILITY_PUBLIC) {

                // get organisation details
                $params = [
                    'org_id' => $dataset->org_id,
                    'locale'  => $locale
                ];
                $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
                $api = new ApiOrganisation($rq);
                $res = $api->getOrganisationDetails($rq)->getData();
                $organisation = !empty($res->data) ? $res->data : [];

                if (!empty($organisation) &&
                    $organisation->active == Organisation::ACTIVE_TRUE &&
                    $organisation->approved == Organisation::APPROVED_TRUE) {

                    // set resource format code
                    $resource->format_code = Resource::getFormatsCode($resource->file_format);

                    if (empty($version)) {
                        $version = $resource->version;
                    }

                    if (\Auth::check() && $request->has('delete')) {
                        // check delete rights
                        $checkData = [
                            'org_id' => $dataset->org_id
                        ];
                        $objData = [
                            'org_id'      => $dataset->org_id,
                            'created_by'  => $resource->created_by
                        ];
                        $rightCheck = RoleRight::checkUserRight(Module::RESOURCES, RoleRight::RIGHT_ALL, $checkData, $objData);

                        if ($rightCheck) {
                            $params = [
                                'api_key'       => \Auth::user()->api_key,
                                'resource_uri'  => $resource->uri,
                            ];

                            $delReq = Request::create('/api/deleteResource', 'POST', $params);
                            $api = new ApiResource($delReq);
                            $result = $api->deleteResource($delReq)->getData();

                            if (isset($result->success) && $result->success) {
                                $request->session()->flash('alert-success', __('custom.delete_success'));

                                return redirect()->route('orgViewDataset', array_merge($request->query(), ['uri' => $dataset->uri]));
                            }

                            $request->session()->flash('alert-danger', isset($result->error) ? $result->error->message : __('custom.delete_error'));
                        }
                    }

                    // get resource data
                    $rq = Request::create('/api/getResourceData', 'POST', ['resource_uri' => $resource->uri, 'version' => $version]);
                    $api = new ApiResource($rq);
                    $res = $api->getResourceData($rq)->getData();
                    $data = !empty($res->data) ? $res->data : [];

                    if ($resource->format_code == Resource::FORMAT_XML) {
                        $reqConvert = Request::create('/json2xml', 'POST', ['data' => $data]);
                        $apiConvert = new ApiConversion($reqConvert);
                        $resultConvert = $apiConvert->json2xml($reqConvert)->getData();
                        $data = isset($resultConvert->data) ? $resultConvert->data : [];
                    }

                    $userData = [];
                    $buttons = [];
                    if ($authUser = \Auth::user()) {
                        $userData['firstname'] = $authUser->firstname;
                        $userData['lastname'] = $authUser->lastname;
                        $userData['email'] = $authUser->email;

                        $checkData = [
                            'org_id' => $dataset->org_id
                        ];
                        $objData = [
                            'org_id'      => $dataset->org_id,
                            'created_by'  => $resource->created_by
                        ];

                        // check rights for update / edit buttons
                        $rightCheck = RoleRight::checkUserRight(Module::RESOURCES, RoleRight::RIGHT_EDIT, $checkData, $objData);
                        $buttons['update'] = $rightCheck;
                        $buttons['edit'] = $rightCheck;

                        // check rights for delete button
                        $rightCheck = RoleRight::checkUserRight(Module::RESOURCES, RoleRight::RIGHT_ALL, $checkData, $objData);
                        $buttons['delete'] = $rightCheck;

                        $buttons['rootUrl'] = Role::isAdmin() ? 'admin' : 'user';
                    }

                    $dataset = $this->getModelUsernames($dataset);
                    $resource = $this->getModelUsernames($resource);

                    return view(
                        'organisation/resourceView',
                        [
                            'class'          => 'organisation',
                            'organisation'   => $organisation,
                            'approved'       => ($organisation->type == Organisation::TYPE_COUNTRY),
                            'dataset'        => $dataset,
                            'resource'       => $resource,
                            'data'           => $data,
                            'versionView'    => $version,
                            'userData'       => $userData,
                            'buttons'        => $buttons
                        ]
                    );
                }
            }
        }

        return redirect()->back();
    }

    /**
     * Send signal for resource
     *
     * @param Request $request
     *
     * @return json response with result
     */
    public function sendSignal(Request $request)
    {
        $params = [
            'data' => $request->only(['resource_id', 'firstname', 'lastname', 'email', 'description'])
        ];
        $sendRequest = Request::create('api/sendSignal', 'POST', $params);
        $api = new ApiSignal($sendRequest);
        $result = $api->sendSignal($sendRequest)->getData();

        return json_encode($result);
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
        $res = $api->getOrganisationDetails($rq)->getData();
        $organisation = !empty($res->data) ? $res->data : [];

        if (!empty($organisation) &&
            $organisation->active == Organisation::ACTIVE_TRUE &&
            $organisation->approved == Organisation::APPROVED_TRUE) {

            // set object owner
            $objOwner = [
                'id' => $organisation->id,
                'name' => $organisation->name,
                'logo' => $organisation->logo,
                'view' => '/organisation/profile/'. $organisation->uri
            ];

            $params = [
                'criteria' => [
                    'org_ids' => [$organisation->id],
                    'locale' => $locale
                ]
            ];
            $rq = Request::create('/api/listDatasets', 'POST', $params);
            $api = new ApiDataSet($rq);
            $res = $api->listDatasets($rq)->getData();

            $criteria = [
                'org_ids' => [$organisation->id]
            ];

            $objType = Module::getModuleName(Module::ORGANISATIONS);
            $actObjData[$objType] = [];
            $actObjData[$objType][$organisation->id] = [
                'obj_id'         => $organisation->uri,
                'obj_name'       => $organisation->name,
                'obj_module'     => ultrans('custom.organisations'),
                'obj_type'       => 'org',
                'obj_view'       => '/organisation/profile/'. $organisation->uri,
                'parent_obj_id'  => '',
                'obj_owner_id'   => isset($objOwner['id']) ? $objOwner['id'] : '',
                'obj_owner_name' => isset($objOwner['name']) ? $objOwner['name'] : '',
                'obj_owner_logo' => isset($objOwner['logo']) ? $objOwner['logo'] : '',
                'obj_owner_view' => isset($objOwner['view']) ? $objOwner['view'] : ''
            ];

            if (isset($res->success) && $res->success && !empty($res->datasets)) {
                $objType = Module::getModuleName(Module::DATA_SETS);
                $objTypeRes = Module::getModuleName(Module::RESOURCES);
                $actObjData[$objType] = [];

                foreach ($res->datasets as $dataset) {
                    $criteria['dataset_ids'][] = $dataset->id;
                    $actObjData[$objType][$dataset->id] = [
                        'obj_id'         => $dataset->uri,
                        'obj_name'       => $dataset->name,
                        'obj_module'     => ultrans('custom.dataset'),
                        'obj_type'       => 'dataset',
                        'obj_view'       => '/data/view/'. $dataset->uri,
                        'parent_obj_id'  => '',
                        'obj_owner_id'   => isset($objOwner['id']) ? $objOwner['id'] : '',
                        'obj_owner_name' => isset($objOwner['name']) ? $objOwner['name'] : '',
                        'obj_owner_logo' => isset($objOwner['logo']) ? $objOwner['logo'] : '',
                        'obj_owner_view' => isset($objOwner['view']) ? $objOwner['view'] : ''
                    ];

                    if (!empty($dataset->resource)) {
                        foreach ($dataset->resource as $resource) {
                            $criteria['resource_uris'][] = $resource->uri;
                            $actObjData[$objTypeRes][$resource->uri] = [
                                'obj_id'            => $resource->uri,
                                'obj_name'          => $resource->name,
                                'obj_module'        => ultrans('custom.resource'),
                                'obj_type'          => 'resource',
                                'obj_view'          => '/data/resourceView/'. $resource->uri,
                                'parent_obj_id'     => $dataset->uri,
                                'parent_obj_name'   => $dataset->name,
                                'parent_obj_module' => ultrans('custom.dataset'),
                                'parent_obj_type'   => 'dataset',
                                'parent_obj_view'   => '/data/view/'. $dataset->uri,
                                'obj_owner_id'      => isset($objOwner['id']) ? $objOwner['id'] : '',
                                'obj_owner_name'    => isset($objOwner['name']) ? $objOwner['name'] : '',
                                'obj_owner_logo'    => isset($objOwner['logo']) ? $objOwner['logo'] : '',
                                'obj_owner_view'    => isset($objOwner['view']) ? $objOwner['view'] : ''
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
                    'organisation'   => $organisation,
                    'chronology'     => $paginationData['items'],
                    'pagination'     => $paginationData['paginate'],
                    'actionObjData'  => $actObjData,
                    'actionTypes'    => $actTypes
                ]
            );
        }

        return redirect()->back();
    }

    public function datasetChronology(Request $request, $uri)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        // get dataset details
        $params = [
            'dataset_uri' => $uri,
            'locale'      => $locale
        ];
        $rq = Request::create('/api/getDataSetDetails', 'POST', $params);
        $api = new ApiDataSet($rq);
        $res = $api->getDataSetDetails($rq)->getData();
        $dataset = !empty($res->data) ? $this->getModelUsernames($res->data) : [];

        if (!empty($dataset) && isset($dataset->org_id) &&
            $dataset->status == DataSet::STATUS_PUBLISHED &&
            $dataset->visibility == DataSet::VISIBILITY_PUBLIC) {

            // get organisation details
            $params = [
                'org_id' => $dataset->org_id,
                'locale'  => $locale
            ];
            $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
            $api = new ApiOrganisation($rq);
            $res = $api->getOrganisationDetails($rq)->getData();
            $organisation = !empty($res->data) ? $res->data : [];

            if (!empty($organisation) &&
                $organisation->active == Organisation::ACTIVE_TRUE &&
                $organisation->approved == Organisation::APPROVED_TRUE) {

                // set object owner
                $objOwner = [
                    'id' => $organisation->id,
                    'name' => $organisation->name,
                    'logo' => $organisation->logo,
                    'view' => '/organisation/profile/'. $organisation->uri
                ];

                $objType = Module::getModuleName(Module::DATA_SETS);
                $objTypeRes = Module::getModuleName(Module::RESOURCES);
                $actObjData[$objType] = [];

                $criteria = [];
                $criteria['dataset_ids'][] = $dataset->id;
                $actObjData[$objType][$dataset->id] = [
                    'obj_id'         => $dataset->uri,
                    'obj_name'       => $dataset->name,
                    'obj_module'     => ultrans('custom.dataset'),
                    'obj_type'       => 'dataset',
                    'obj_view'       => '/data/view/'. $dataset->uri,
                    'parent_obj_id'  => '',
                    'obj_owner_id'   => isset($objOwner['id']) ? $objOwner['id'] : '',
                    'obj_owner_name' => isset($objOwner['name']) ? $objOwner['name'] : '',
                    'obj_owner_logo' => isset($objOwner['logo']) ? $objOwner['logo'] : '',
                    'obj_owner_view' => isset($objOwner['view']) ? $objOwner['view'] : ''
                ];

                if (!empty($dataset->resource)) {
                    foreach ($dataset->resource as $resource) {
                        $criteria['resource_uris'][] = $resource->uri;
                        $actObjData[$objTypeRes][$resource->uri] = [
                            'obj_id'            => $resource->uri,
                            'obj_name'          => $resource->name,
                            'obj_module'        => ultrans('custom.resource'),
                            'obj_type'          => 'resource',
                            'obj_view'          => '/data/resourceView/'. $resource->uri,
                            'parent_obj_id'     => $dataset->uri,
                            'parent_obj_name'   => $dataset->name,
                            'parent_obj_module' => ultrans('custom.dataset'),
                            'parent_obj_type'   => 'dataset',
                            'parent_obj_view'   => '/data/view/'. $dataset->uri,
                            'obj_owner_id'      => isset($objOwner['id']) ? $objOwner['id'] : '',
                            'obj_owner_name'    => isset($objOwner['name']) ? $objOwner['name'] : '',
                            'obj_owner_logo'    => isset($objOwner['logo']) ? $objOwner['logo'] : '',
                            'obj_owner_view'    => isset($objOwner['view']) ? $objOwner['view'] : ''
                        ];
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
                    'organisation/datasetChronology',
                    [
                        'class'          => 'organisation',
                        'organisation'   => $organisation,
                        'dataset'        => $dataset,
                        'chronology'     => $paginationData['items']
                        'pagination'     =>  $paginationData['paginate'],
                        'actionObjData'  => $actObjData,
                        'actionTypes'    => $actTypes
                    ]
                );
            }
        }

        return redirect()->back();
    }
}

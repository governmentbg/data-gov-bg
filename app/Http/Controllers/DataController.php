<?php

namespace App\Http\Controllers;

use App\DataSet;
use App\Resource;
use App\Organisation;
use App\Role;
use App\RoleRight;
use App\Module;
use App\ActionsHistory;
use App\Http\Controllers\Api\DataSetController as ApiDataSet;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\ConversionController as ApiConversion;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;
use App\Http\Controllers\Api\CategoryController as ApiCategory;
use App\Http\Controllers\Api\TagController as ApiTag;
use App\Http\Controllers\Api\TermsOfUseController as ApiTermsOfUse;
use App\Http\Controllers\Api\UserController as ApiUser;
use App\Http\Controllers\Api\UserFollowController as ApiFollow;
use App\Http\Controllers\Api\SignalController as ApiSignal;
use App\Http\Controllers\Api\ActionsHistoryController as ApiActionsHistory;
use Illuminate\Http\Request;

class DataController extends Controller {
    /**
     * List datasets
     *
     * @param Request $request
     *
     * @return view for browsing datasets
     */
    public function list(Request $request)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        $criteria = [];

        // filters
        $organisations = [];
        $users = [];
        $groups = [];
        $categories = [];
        $tags = [];
        $formats = [];
        $termsOfUse = [];
        $getParams = [];
        $display = [];

        // organisations / users filter
        $userDatasetsOnly = false;
        if ($request->filled('org') && is_array($request->org)) {
            $criteria['org_ids'] = $request->org;
            $getParams['org'] = $request->org;
            $getParams['user'] = [];
        } else {
            $getParams['org'] = [];
            if ($request->filled('user') && is_array($request->user)) {
                $criteria['user_ids'] = $request->user;
                $userDatasetsOnly = true;
                $getParams['user'] = $request->user;
            } else {
                $getParams['user'] = [];
            }
        }

        // groups filter
        if ($request->filled('group') && is_array($request->group)) {
            $criteria['group_ids'] = $request->group;
            $getParams['group'] = $request->group;
        } else {
            $getParams['group'] = [];
        }

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
        $params['criteria']['user_datasets_only'] = $userDatasetsOnly;

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

        $datasetOrgs = [];
        $buttons = [];

        if (!empty($paginationData['items'])) {
            // get organisation ids
            $orgIds = array_where(array_pluck($paginationData['items'], 'org_id'), function ($value, $key) {
                return !is_null($value);
            });

            // list organisations
            $params = [
                'criteria'  => [
                    'org_ids'  => array_unique($orgIds),
                    'locale'   => $locale
                ]
            ];
            $rq = Request::create('/api/listOrganisations', 'POST', $params);
            $api = new ApiOrganisation($rq);
            $res = $api->listOrganisations($rq)->getData();
            $datasetOrgs = !empty($res->organisations) ? $res->organisations : [];

            $recordsLimit = 10;

            if (empty($getParams['user'])) {
                // check for organisation records limit
                $hasLimit = !($request->filled('org_limit') && $request->org_limit == 0);

                // list data organisations
                $params = [
                    'criteria' => [
                        'dataset_criteria' => $criteria,
                        'locale' => $locale
                    ],
                ];
                if ($hasLimit) {
                    $params['criteria']['records_limit'] = $recordsLimit;
                }

                $rq = Request::create('/api/listDataOrganisations', 'POST', $params);
                $api = new ApiOrganisation($rq);
                $res = $api->listDataOrganisations($rq)->getData();

                $organisations = !empty($res->organisations) ? $res->organisations : [];
                $getParams['org'] = array_intersect($getParams['org'], array_pluck($organisations, 'id'));

                $this->prepareDisplayParams(count($organisations), $hasLimit, $recordsLimit, 'org', $display);
            }

            if (empty($getParams['org'])) {
                // check for user records limit
                $hasLimit = !($request->filled('user_limit') && $request->user_limit == 0);

                // list data users
                $params = [
                    'criteria' => [
                        'dataset_criteria' => $criteria
                    ],
                ];
                if ($hasLimit) {
                    $params['criteria']['records_limit'] = $recordsLimit;
                }

                $rq = Request::create('/api/listDataUsers', 'POST', $params);
                $api = new ApiUser($rq);
                $res = $api->listDataUsers($rq)->getData();

                $users = !empty($res->users) ? $res->users : [];
                $getParams['user'] = array_intersect($getParams['user'], array_pluck($users, 'id'));

                $this->prepareDisplayParams(count($users), $hasLimit, $recordsLimit, 'user', $display);
            }

            // check for group records limit
            $hasLimit = !($request->filled('group_limit') && $request->group_limit == 0);

            // list data groups
            $params = [
                'criteria' => [
                    'dataset_criteria' => $criteria,
                    'locale' => $locale
                ],
            ];
            if ($hasLimit) {
                $params['criteria']['records_limit'] = $recordsLimit;
            }

            $rq = Request::create('/api/listDataGroups', 'POST', $params);
            $api = new ApiOrganisation($rq);
            $res = $api->listDataGroups($rq)->getData();

            $groups = !empty($res->groups) ? $res->groups : [];
            $getParams['group'] = array_intersect($getParams['group'], array_pluck($groups, 'id'));

            $this->prepareDisplayParams(count($groups), $hasLimit, $recordsLimit, 'group', $display);

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

        if ($request['page'] > $paginationData['paginate']->lastPage()) {
            $request['page'] = $paginationData['paginate']->lastPage();

            return redirect()->route('data', $request->query());
        }

        if (\Auth::check()) {
            // check rights for add button
            $rightCheck = RoleRight::checkUserRight(Module::DATA_SETS, RoleRight::RIGHT_EDIT);
            $buttons['add'] = $rightCheck;

            $buttons['addUrl'] = Role::isAdmin() ? '/admin/dataset/add' : '/user/dataset/create';
        }

        return view(
            'data/list',
            [
                'class'              => 'data',
                'datasetOrgs'        => $datasetOrgs,
                'datasets'           => $paginationData['items'],
                'resultsCount'       => $count,
                'pagination'         => $paginationData['paginate'],
                'organisations'      => $organisations,
                'users'              => $users,
                'groups'             => $groups,
                'categories'         => $categories,
                'tags'               => $tags,
                'formats'            => $formats,
                'termsOfUse'         => $termsOfUse,
                'getParams'          => $getParams,
                'display'            => $display,
                'buttons'            => $buttons,
            ]
        );
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

    public function view(Request $request, $uri)
    {
        $locale = \LaravelLocalization::getCurrentLocale();
        $groups = [];

        // get dataset details
        $params = [
            'dataset_uri' => $uri,
            'locale'  => $locale
        ];
        $rq = Request::create('/api/getDataSetDetails', 'POST', $params);
        $api = new ApiDataSet($rq);
        $res = $api->getDataSetDetails($rq)->getData();
        $dataset = !empty($res->data) ? $res->data : [];

        if (
            !empty($dataset) &&
            $dataset->status == DataSet::STATUS_PUBLISHED &&
            $dataset->visibility == DataSet::VISIBILITY_PUBLIC
        ) {
            $setGroups = [];

            if (!empty($dataset->groups)) {
                foreach ($dataset->groups as $record) {
                    $setGroups[] = (int) $record->id;
                }
            }

            $organisation = [];
            $user = [];
            if (!is_null($dataset->org_id)) {
                // get organisation details
                $params = [
                    'org_id'  => $dataset->org_id,
                    'locale'  => $locale
                ];
                $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
                $api = new ApiOrganisation($rq);
                $res = $api->getOrganisationDetails($rq)->getData();
                $organisation = !empty($res->data) ? $res->data : [];
            } else {
                // get user details
                $params = [
                    'criteria' => ['id' => $dataset->created_by]
                ];
                $rq = Request::create('/api/listUsers', 'POST', $params);
                $api = new ApiUser($rq);
                $res = $api->listUsers($rq)->getData();
                $user = !empty($res->users) ? $res->users[0] : [];
            }

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

                        return redirect()->route('data', array_except($request->query(), ['page']));
                    }

                    $request->session()->flash('alert-danger', isset($result->error) ? $result->error->message : __('custom.fail_dataset_delete'));
                }
            }

            // list resources
            $params = [
                'criteria' => [
                    'dataset_uri' => $uri
                ]
            ];
            $resPerPage = 10;
            $pageNumber = !empty($request->rpage) ? $request->rpage : 1;
            $params['records_per_page'] = $resPerPage;
            $params['page_number'] = $pageNumber;

            if (isset($request->order)) {
                $params['criteria']['order']['field'] = $request->order;
            }

            if (isset($request->order_type)) {
                $params['criteria']['order']['type'] = $request->order_type;
            }

            $rq = Request::create('/api/listResources', 'POST', $params);
            $apiResources = new ApiResource($rq);
            $res = $apiResources->listResources($rq)->getData();
            $resources = !empty($res->resources) ? $res->resources : [];
            $resCount = $res->total_records;

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

                $rightCheck = RoleRight::checkUserRight(
                    Module::GROUPS,
                    RoleRight::RIGHT_EDIT
                );

                $buttons['addGroup'] = $rightCheck;

                if ($buttons['addGroup']) {
                    $groups = $this->prepareGroups();
                }

                if ($request->has('save')) {
                    $groupId = $request->offsetGet('group_id');
                    $post = [
                        'api_key'       => $authUser->api_key,
                        'data_set_uri'  => $dataset->uri,
                        'group_id'      => $groupId,
                    ];

                    if (count($setGroups) && is_null($groupId)) {
                        $post['group_id'] = $setGroups;
                        $removeGroup = Request::create('/api/removeDatasetFromGroup', 'POST', $post);
                        $api = new ApiDataSet($removeGroup);
                        $remove = $api->removeDatasetFromGroup($removeGroup)->getData();

                        if (!$remove->success) {
                            session()->flash('alert-danger', __('custom.edit_error'));
                        } else {
                            session()->flash('alert-success', __('custom.edit_success'));
                        }

                        $setGroups = [];

                        return redirect()->back();
                    }

                    if (!is_null($groupId)) {
                        $post['group_id'] = $groupId;
                        $addGroup = Request::create('/api/addDataSetToGroup', 'POST', $post);
                        $api = new ApiDataSet($addGroup);
                        $added = $api->addDataSetToGroup($addGroup)->getData();

                        if (!$added->success) {
                            session()->flash('alert-danger', __('custom.edit_error'));
                        } else {
                            session()->flash('alert-success', __('custom.edit_success'));
                        }

                        return redirect()->back();
                    }
                }
            }

            $paginationData = $this->getPaginationData(
                $resources,
                $resCount,
                array_except(app('request')->input(), ['rpage']),
                $resPerPage,
                'rpage'
            );

            $dataset = $this->getModelUsernames($dataset);
            $discussion = $this->getForumDiscussion($dataset->forum_link);

            $viewParams = [
                'class'         => 'data',
                'organisation'  => $organisation,
                'user'          => $user,
                'approved'      => (!empty($organisation) && $organisation->type == Organisation::TYPE_COUNTRY),
                'dataset'       => $dataset,
                'resources'     => $paginationData['items'],
                'buttons'       => $buttons,
                'groups'        => $groups,
                'setGroups'     => isset($setGroups) ? $setGroups : [],
                'pagination'    => $paginationData['paginate'],
                'uri'           => $uri,
                'sorting'       => 'dataView',
            ];

            return view(
                'data/view',
                !empty($discussion)
                    ? array_merge($viewParams, $discussion)
                    : $viewParams
            );
        }

        return redirect()->back();
    }

    public function resourceView(Request $request, $uri, $version = null)
    {
        $locale = \LaravelLocalization::getCurrentLocale();
        $versionsPerPage = 10;

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

            if (!empty($dataset) &&
                $dataset->status == DataSet::STATUS_PUBLISHED &&
                $dataset->visibility == DataSet::VISIBILITY_PUBLIC) {

                $organisation = [];
                $user = [];
                if (!is_null($dataset->org_id)) {
                    // get organisation details
                    $params = [
                        'org_id'  => $dataset->org_id,
                        'locale'  => $locale
                    ];
                    $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
                    $api = new ApiOrganisation($rq);
                    $res = $api->getOrganisationDetails($rq)->getData();
                    $organisation = !empty($res->data) ? $res->data : [];
                } else {
                    // get user details
                    $params = [
                        'criteria' => ['id' => $dataset->created_by]
                    ];
                    $rq = Request::create('/api/listUsers', 'POST', $params);
                    $api = new ApiUser($rq);
                    $res = $api->listUsers($rq)->getData();
                    $user = !empty($res->users) ? $res->users[0] : [];
                }

                // set resource format code
                $resource->format_code = Resource::getFormatsCode($resource->file_format);
                $formats = Resource::getFormats(true);

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

                            return redirect()->route('dataView', array_merge($request->query(), ['uri' => $dataset->uri]));
                        }

                        $request->session()->flash('alert-danger', isset($result->error) ? $result->error->message : __('custom.delete_error'));
                    }
                }

                // get resource data
                $rq = Request::create('/api/getResourceData', 'POST', ['resource_uri' => $resource->uri, 'version' => $version]);
                $api = new ApiResource($rq);
                $res = $api->getResourceData($rq)->getData();
                $data = !empty($res->data) ? $res->data : [];

                if (
                    $resource->format_code == Resource::FORMAT_XML
                    || $resource->format_code == Resource::FORMAT_RDF
                ) {
                    $convertData = [
                        'data'      => $data,
                    ];
                    $method = 'json2'. strtolower($resource->file_format);
                    $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                    $apiConvert = new ApiConversion($reqConvert);
                    $resultConvert = $apiConvert->$method($reqConvert)->getData();
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

                if (!empty($resource->versions_list)) {
                    usort($resource->versions_list, function($a, $b) {
                        if ($a == $b) {
                            return 0;
                        }

                        return ($a > $b) ? -1 : 1;
                    });
                }

                $count = count($resource->versions_list);
                $verData = collect($resource->versions_list)->paginate($versionsPerPage);

                $paginationData = $this->getPaginationData(
                    $verData,
                    $count,
                    array_except(app('request')->input(), ['page']),
                    $versionsPerPage
                );

                $pageNumber = !empty($request->rpage) ? $request->rpage : 1;
                $resourcePaginationData = $this->getResourcePaginationData($data, $resource, $pageNumber);

                return view(
                    'data/resourceView',
                    [
                        'class'         => 'data',
                        'organisation'  => $organisation,
                        'versions'      => $verData,
                        'pagination'    => $paginationData['paginate'],
                        'user'          => $user,
                        'approved'      => (!empty($organisation) && $organisation->type == Organisation::TYPE_COUNTRY),
                        'dataset'       => $dataset,
                        'resource'      => $resource,
                        'data'          => $resourcePaginationData['data'],
                        'versionView'   => $version,
                        'userData'      => $userData,
                        'buttons'       => $buttons,
                        'formats'       => $formats,
                        'resPagination' => $resourcePaginationData['resPagination'],
                    ]
                );
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

    public function linkedData(Request $request)
    {
        $formats = Resource::getFormats(true);
        $formats = array_only($formats, [Resource::FORMAT_JSON, Resource::FORMAT_XML]);

        $selFormat = $request->input('format', '');

        $searchResultsUrl = '';

        if ($request->filled('query') && !empty($selFormat)) {
            $format = strtolower($formats[$selFormat]);

            if (($searchQuery = json_decode($request->input('query'))) && isset($searchQuery->query)) {
                $params = [
                    'query'  => json_encode($searchQuery->query),
                    'format' => $selFormat
                ];

                if (isset($searchQuery->sort)) {
                    if (is_array($searchQuery->sort) && is_object(array_first($searchQuery->sort))) {
                        foreach (array_first($searchQuery->sort) as $field => $type) {
                            $params['order']['field'] = $field;

                            if (isset($type->order)) {
                                $params['order']['type'] = $type->order;
                            }

                            break;
                        }
                    } else {
                        return back()->withInput()->withErrors(['query' => [__('custom.invalid_search_query_sort')]]);
                    }
                }

                if ($request->filled('limit_results')) {
                    $params['records_per_page'] = intval($request->limit_results);
                }

                if ($request->has('search_results_url')) {
                    $rq = Request::create('/api/getLinkedData', 'GET', $params);
                    $searchResultsUrl = $request->root() .'/'. $rq->path() .'?'. http_build_query($rq->query());
                } else {
                    $rq = Request::create('/api/getLinkedData', 'POST', $params);
                    $api = new ApiResource($rq);
                    $result = $api->getLinkedData($rq)->getData();

                    if (isset($result->success) && $result->success && isset($result->data)) {
                        $filename = time() .'.'. $format;

                        if (strtolower($selFormat) != Resource::FORMAT_JSON) {
                            $method = 'json2'. strtolower($format);
                            $convertReq = Request::create('/api/'. $method, 'POST', ['data' => $result->data]);
                            $apiResources = new ApiConversion($convertReq);
                            $resource = $apiResources->$method($convertReq)->getData();

                            if (!$resource->success) {
                                return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.converse_unavailable')));
                            }

                            $fileData = $resource->data;
                        } else {
                            $fileData = json_encode($result->data, JSON_UNESCAPED_UNICODE);
                        }

                        return \Response::make($fileData, '200', array(
                            'Content-Type' => 'application/octet-stream',
                            'Content-Disposition' => 'attachment; filename="'. $filename .'"'
                        ));
                    } else {
                        $errors = $result->errors ?: ['common' => $result->error->message];
                        return back()->withInput()->withErrors($errors);
                    }
                }
            } else {
                return back()->withInput()->withErrors(['query' => [__('custom.invalid_search_query')]]);
            }
        }

        return view(
            'data/linkedData',
            [
                'class'            => 'data',
                'formats'          => $formats,
                'selectedFormat'   => $selFormat,
                'searchResultsUrl' => $searchResultsUrl
            ]
        );
    }

    /**
     * List reported datasets
     *
     * @param Request $request
     *
     * @return view for browsing reported datasets
     */
    public function reportedList(Request $request)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        $criteria = [
            'reported' => Resource::REPORTED_TRUE
        ];

        // filters
        $organisations = [];
        $users = [];
        $groups = [];
        $categories = [];
        $tags = [];
        $formats = [];
        $termsOfUse = [];
        $getParams = [];
        $display = [];

        // organisations / users filter
        $userDatasetsOnly = false;
        if ($request->filled('org') && is_array($request->org)) {
            $criteria['org_ids'] = $request->org;
            $getParams['org'] = $request->org;
            $getParams['user'] = [];
        } else {
            $getParams['org'] = [];
            if ($request->filled('user') && is_array($request->user)) {
                $criteria['user_ids'] = $request->user;
                $userDatasetsOnly = true;
                $getParams['user'] = $request->user;
            } else {
                $getParams['user'] = [];
            }
        }

        // groups filter
        if ($request->filled('group') && is_array($request->group)) {
            $criteria['group_ids'] = $request->group;
            $getParams['group'] = $request->group;
        } else {
            $getParams['group'] = [];
        }

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
        $params['criteria']['user_datasets_only'] = $userDatasetsOnly;

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

        $datasetOrgs = [];
        $buttons = [];

        if (!empty($paginationData['items'])) {
            // get organisation ids
            $orgIds = array_where(array_pluck($paginationData['items'], 'org_id'), function ($value, $key) {
                return !is_null($value);
            });

            // list organisations
            $params = [
                'criteria'  => [
                    'org_ids'  => array_unique($orgIds),
                    'locale'   => $locale
                ]
            ];
            $rq = Request::create('/api/listOrganisations', 'POST', $params);
            $api = new ApiOrganisation($rq);
            $res = $api->listOrganisations($rq)->getData();
            $datasetOrgs = !empty($res->organisations) ? $res->organisations : [];

            $recordsLimit = 10;

            if (empty($getParams['user'])) {
                // check for organisation records limit
                $hasLimit = !($request->filled('org_limit') && $request->org_limit == 0);

                // list data organisations
                $params = [
                    'criteria' => [
                        'dataset_criteria' => $criteria,
                        'locale' => $locale
                    ],
                ];
                if ($hasLimit) {
                    $params['criteria']['records_limit'] = $recordsLimit;
                }

                $rq = Request::create('/api/listDataOrganisations', 'POST', $params);
                $api = new ApiOrganisation($rq);
                $res = $api->listDataOrganisations($rq)->getData();

                $organisations = !empty($res->organisations) ? $res->organisations : [];
                $getParams['org'] = array_intersect($getParams['org'], array_pluck($organisations, 'id'));

                $this->prepareDisplayParams(count($organisations), $hasLimit, $recordsLimit, 'org', $display);
            }

            if (empty($getParams['org'])) {
                // check for user records limit
                $hasLimit = !($request->filled('user_limit') && $request->user_limit == 0);

                // list data users
                $params = [
                    'criteria' => [
                        'dataset_criteria' => $criteria
                    ],
                ];
                if ($hasLimit) {
                    $params['criteria']['records_limit'] = $recordsLimit;
                }

                $rq = Request::create('/api/listDataUsers', 'POST', $params);
                $api = new ApiUser($rq);
                $res = $api->listDataUsers($rq)->getData();

                $users = !empty($res->users) ? $res->users : [];
                $getParams['user'] = array_intersect($getParams['user'], array_pluck($users, 'id'));

                $this->prepareDisplayParams(count($users), $hasLimit, $recordsLimit, 'user', $display);
            }

            // check for group records limit
            $hasLimit = !($request->filled('group_limit') && $request->group_limit == 0);

            // list data groups
            $params = [
                'criteria' => [
                    'dataset_criteria' => $criteria,
                    'locale' => $locale
                ],
            ];
            if ($hasLimit) {
                $params['criteria']['records_limit'] = $recordsLimit;
            }

            $rq = Request::create('/api/listDataGroups', 'POST', $params);
            $api = new ApiOrganisation($rq);
            $res = $api->listDataGroups($rq)->getData();

            $groups = !empty($res->groups) ? $res->groups : [];
            $getParams['group'] = array_intersect($getParams['group'], array_pluck($groups, 'id'));

            $this->prepareDisplayParams(count($groups), $hasLimit, $recordsLimit, 'group', $display);

            // check for category records limit
            $hasLimit = !($request->filled('category_limit') && $request->category_limit == 0);

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
            'data/reportedList',
            [
                'class'              => 'data-attention',
                'datasetOrgs'        => $datasetOrgs,
                'datasets'           => $paginationData['items'],
                'resultsCount'       => $count,
                'pagination'         => $paginationData['paginate'],
                'organisations'      => $organisations,
                'users'              => $users,
                'groups'             => $groups,
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

    public function reportedView(Request $request, $uri)
    {
        $locale = \LaravelLocalization::getCurrentLocale();
        $groups = [];

        // get dataset details
        $params = [
            'dataset_uri' => $uri,
            'locale'  => $locale
        ];
        $rq = Request::create('/api/getDataSetDetails', 'POST', $params);
        $api = new ApiDataSet($rq);
        $res = $api->getDataSetDetails($rq)->getData();
        $dataset = !empty($res->data) ? $res->data : [];

        if (
            !empty($dataset) && $dataset->reported &&
            $dataset->status == DataSet::STATUS_PUBLISHED &&
            $dataset->visibility == DataSet::VISIBILITY_PUBLIC
        ) {
            $setGroups = [];

            if (!empty($dataset->groups)) {
                foreach ($dataset->groups as $record) {
                    $setGroups[] = (int) $record->id;
                }
            }

            $organisation = [];
            $user = [];
            if (!is_null($dataset->org_id)) {
                // get organisation details
                $params = [
                    'org_id'  => $dataset->org_id,
                    'locale'  => $locale
                ];
                $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
                $api = new ApiOrganisation($rq);
                $res = $api->getOrganisationDetails($rq)->getData();
                $organisation = !empty($res->data) ? $res->data : [];
            } else {
                // get user details
                $params = [
                    'criteria' => ['id' => $dataset->created_by]
                ];
                $rq = Request::create('/api/listUsers', 'POST', $params);
                $api = new ApiUser($rq);
                $res = $api->listUsers($rq)->getData();
                $user = !empty($res->users) ? $res->users[0] : [];
            }

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

                        return redirect()->route('reportedData', array_except($request->query(), ['page']));
                    }

                    $request->session()->flash('alert-danger', isset($result->error) ? $result->error->message : __('custom.fail_dataset_delete'));
                }
            }

            // list resources
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

                $rightCheck = RoleRight::checkUserRight(
                    Module::GROUPS,
                    RoleRight::RIGHT_EDIT
                );

                $buttons['addGroup'] = $rightCheck;

                if ($buttons['addGroup']) {
                    $groups = $this->prepareGroups();
                }

                if ($request->has('save')) {
                    $groupId = $request->offsetGet('group_id');
                    $post = [
                        'api_key'       => $authUser->api_key,
                        'data_set_uri'  => $dataset->uri,
                        'group_id'      => $groupId,
                    ];

                    if (count($setGroups) && is_null($groupId)) {
                        $post['group_id'] = $setGroups;
                        $removeGroup = Request::create('/api/removeDatasetFromGroup', 'POST', $post);
                        $api = new ApiDataSet($removeGroup);
                        $remove = $api->removeDatasetFromGroup($removeGroup)->getData();

                        if (!$remove->success) {
                            session()->flash('alert-danger', __('custom.edit_error'));
                        } else {
                            session()->flash('alert-success', __('custom.edit_success'));
                        }

                        $setGroups = [];

                        return redirect()->back();
                    }

                    if (!is_null($groupId)) {
                        $post['group_id'] = $groupId;
                        $addGroup = Request::create('/api/addDataSetToGroup', 'POST', $post);
                        $api = new ApiDataSet($addGroup);
                        $added = $api->addDataSetToGroup($addGroup)->getData();

                        if (!$added->success) {
                            session()->flash('alert-danger', __('custom.edit_error'));
                        } else {
                            session()->flash('alert-success', __('custom.edit_success'));
                        }

                        return redirect()->back();
                    }
                }
            }

            $dataset = $this->getModelUsernames($dataset);

            return view(
                'data/reportedView',
                [
                    'class'         => 'data-attention',
                    'organisation'  => $organisation,
                    'user'          => $user,
                    'approved'      => (!empty($organisation) && $organisation->type == Organisation::TYPE_COUNTRY),
                    'dataset'       => $dataset,
                    'resources'     => $resources,
                    'buttons'       => $buttons,
                    'groups'        => $groups,
                    'setGroups'     => isset($setGroups) ? $setGroups : [],
                ]
            );
        }

        return redirect()->back();
    }

    public function reportedResourceView(Request $request, $uri, $version = null)
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

            if (!empty($dataset) && $dataset->reported &&
                $dataset->status == DataSet::STATUS_PUBLISHED &&
                $dataset->visibility == DataSet::VISIBILITY_PUBLIC) {

                $organisation = [];
                $user = [];
                if (!is_null($dataset->org_id)) {
                    // get organisation details
                    $params = [
                        'org_id'  => $dataset->org_id,
                        'locale'  => $locale
                    ];
                    $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
                    $api = new ApiOrganisation($rq);
                    $res = $api->getOrganisationDetails($rq)->getData();
                    $organisation = !empty($res->data) ? $res->data : [];
                } else {
                    // get user details
                    $params = [
                        'criteria' => ['id' => $dataset->created_by]
                    ];
                    $rq = Request::create('/api/listUsers', 'POST', $params);
                    $api = new ApiUser($rq);
                    $res = $api->listUsers($rq)->getData();
                    $user = !empty($res->users) ? $res->users[0] : [];
                }

                // set resource format code
                $resource->format_code = Resource::getFormatsCode($resource->file_format);
                $formats = Resource::getFormats(true);

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

                            return redirect()->route('reportedView', array_merge($request->query(), ['uri' => $dataset->uri]));
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
                    'data/reportedResourceView',
                    [
                        'class'          => 'data-attention',
                        'organisation'   => $organisation,
                        'user'           => $user,
                        'approved'       => (!empty($organisation) && $organisation->type == Organisation::TYPE_COUNTRY),
                        'dataset'        => $dataset,
                        'resource'       => $resource,
                        'data'           => $data,
                        'versionView'    => $version,
                        'userData'       => $userData,
                        'buttons'        => $buttons,
                        'formats'        => $formats
                    ]
                );
            }
        }

        return redirect()->back();
    }

    public function chronology(Request $request, $uri)
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

        $typeChecked = true;
        $class = 'data';
        if ($reported = ($request->filled('type') && $request->type == 'reported')) {
            $typeChecked = $dataset->reported;
            $class = 'data-attention';
        }

        if (!empty($dataset) && $typeChecked &&
            $dataset->status == DataSet::STATUS_PUBLISHED &&
            $dataset->visibility == DataSet::VISIBILITY_PUBLIC) {

            $objOwner = [];
            if (!is_null($dataset->org_id)) {
                // get organisation details
                $params = [
                    'org_id'  => $dataset->org_id,
                    'locale'  => $locale
                ];
                $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
                $api = new ApiOrganisation($rq);
                $res = $api->getOrganisationDetails($rq)->getData();
                $organisation = !empty($res->data) ? $res->data : [];

                // set object owner
                if (!empty($organisation)) {
                    $objOwner = [
                        'id' => $organisation->id,
                        'name' => $organisation->name,
                        'logo' => $organisation->logo,
                        'view' => '/organisation/profile/'. $organisation->uri
                    ];
                }
            }

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
                        'obj_owner_id'   => isset($objOwner['id']) ? $objOwner['id'] : '',
                        'obj_owner_name' => isset($objOwner['name']) ? $objOwner['name'] : '',
                        'obj_owner_logo' => isset($objOwner['logo']) ? $objOwner['logo'] : '',
                        'obj_owner_view' => isset($objOwner['view']) ? $objOwner['view'] : ''
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
                'data/chronology',
                [
                    'class'          => $class,
                    'reported'       => $reported,
                    'dataset'        => $dataset,
                    'chronology'     => !empty($paginationData['items']) ? $paginationData['items'] : [],
                    'pagination'     => !empty($paginationData['paginate']) ? $paginationData['paginate'] : [],
                    'actionObjData'  => $actObjData,
                    'actionTypes'    => $actTypes
                ]
            );
        }

        return redirect()->back();
    }
}

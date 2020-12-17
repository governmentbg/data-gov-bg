<?php

namespace App\Http\Controllers;

use App\Role;
use App\RoleRight;
use App\ActionsHistory;
use App\Module;
use App\DataSet;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;
use App\Http\Controllers\Api\UserController as ApiUser;
use App\Http\Controllers\Api\UserFollowController as ApiFollow;
use App\Http\Controllers\Api\DataSetController as ApiDataSet;
use App\Http\Controllers\Api\ActionsHistoryController as ApiActionsHistory;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Loads a view for browsing groups
     *
     * @param Request $request
     *
     * @return view for browsing groups
     */
    public function list(Request $request)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        $perPage = 6;
        $params = [
            'criteria'         => ['locale'   => $locale],
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1
        ];

        $getParams = [];

        // apply dataset filter
        $listGroups = true;
        if ($request->filled('dataset')) {
            $rq = Request::create('/api/getDataSetDetails', 'POST', ['dataset_uri' => $request->dataset]);
            $api = new ApiDataSet($rq);
            $res = $api->getDataSetDetails($rq)->getData();
            $dataset = !empty($res->data) ? $res->data : [];

            if (!empty($dataset) &&
                $dataset->status == DataSet::STATUS_PUBLISHED &&
                $dataset->visibility == DataSet::VISIBILITY_PUBLIC) {

                $params['criteria']['dataset_id'] = $dataset->id;
                $getParams['dataset'] = $request->dataset;
            } else {
                $listGroups = false;
            }
        }

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

        // list groups
        $groups = [];
        $count = 0;
        if ($listGroups) {
            $rq = Request::create('/api/listGroups', 'POST', $params);
            $api = new ApiOrganisation($rq);
            $result = $api->listGroups($rq)->getData();

            $groups = !empty($result->groups) ? $result->groups : [];
            $count = !empty($result->total_records) ? $result->total_records : 0;
        }

        $paginationData = $this->getPaginationData($groups, $count, $getParams, $perPage);

        $buttons = [];
        if (\Auth::check()) {
            // check rights for add button
            $rightCheck = RoleRight::checkUserRight(Module::GROUPS, RoleRight::RIGHT_EDIT);
            $buttons['add'] = $rightCheck;

            foreach ($paginationData['items'] as $group) {
                $checkData = [
                    'group_id' => $group->id
                ];
                $objData = [
                    'group_ids'   => [$group->id],
                    'created_by'  => $group->created_by
                ];

                // check rights for edit & delete button
                $rightCheck = RoleRight::checkUserRight(Module::GROUPS, RoleRight::RIGHT_ALL, $checkData, $objData);
                $buttons[$group->id]['edit'] = $rightCheck;
                $buttons[$group->id]['delete'] = $rightCheck;
            }

            $buttons['rootUrl'] = Role::isAdmin() ? 'admin' : 'user';
        }

        return view(
            'group.list',
            [
                'class'       => 'user',
                'groups'      => $paginationData['items'],
                'pagination'  => $paginationData['paginate'],
                'getParams'   => $getParams,
                'buttons'     => $buttons
            ]
        );
    }

    public function view(Request $request, $uri)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        $params = [
            'group_uri' => $uri,
            'locale'  => $locale
        ];

        $rq = Request::create('/api/getGroupDetails', 'POST', $params);
        $api = new ApiOrganisation($rq);
        $res = $api->getGroupDetails($rq)->getData();
        $group = !empty($res->data) ? $res->data : [];

        if (!empty($group)) {
            $buttons = [];
            if ($authUser = \Auth::user()) {
                $objData = ['object_id' => $authUser->id];
                $rightCheck = RoleRight::checkUserRight(Module::USERS, RoleRight::RIGHT_EDIT, [], $objData);
                if ($rightCheck) {
                    $userData = [
                        'api_key' => $authUser->api_key,
                        'id'      => $authUser->id
                    ];

                    // get followed groups
                    $followed = [];
                    if ($this->getFollowedGroups($userData, $followed)) {
                        if (!in_array($group->id, $followed)) {
                            $buttons['follow'] = true;
                        } else {
                            $buttons['unfollow'] = true;
                        }

                        // follow / unfollow group
                        $followResult = $this->followGroup($request, $userData, $followed, [$group->id]);
                        if (!empty($followResult) && $followResult->success) {
                            return back();
                        }
                    }
                }

                $checkData = [
                    'group_id' => $group->id
                ];
                $objData = [
                    'group_ids' => [$group->id]
                ];

                // check rights for edit & delete button
                $rightCheck = RoleRight::checkUserRight(Module::GROUPS, RoleRight::RIGHT_ALL, $checkData, $objData);
                $buttons['edit'] = $rightCheck;
                $buttons['delete'] = $rightCheck;

                $buttons['rootUrl'] = Role::isAdmin() ? 'admin' : 'user';
            }

            return view(
                'group.view',
                [
                    'class'    => 'user',
                    'group'    => $group,
                    'buttons'  => $buttons
                ]
            );
        }

        return redirect()->back();
    }

    private function getFollowedGroups($userData, &$followed)
    {
        $followed = [];

        $rq = Request::create('/api/getUserSettings', 'POST', $userData);
        $api = new ApiUser($rq);
        $res = $api->getUserSettings($rq)->getData();

        if (isset($res->user) && !empty($res->user)) {
            if (!empty($res->user->follows)) {
                $followed = array_where(array_pluck($res->user->follows, 'group_id'), function ($value, $key) {
                    return !is_null($value);
                });
            }

            return true;
        }

        return false;
    }

    private function followGroup(Request $request, $userData, $followed, $groupIds)
    {
        $followResult = null;

        if ($request->has('follow')) {
            // follow group
            if (in_array($request->follow, $groupIds) && !in_array($request->follow, $followed)) {
                $followRq = Request::create('api/addFollow', 'POST', [
                    'api_key'  => $userData['api_key'],
                    'user_id'  => $userData['id'],
                    'group_id' => $request->follow,
                ]);
                $apiFollow = new ApiFollow($followRq);
                $followResult = $apiFollow->addFollow($followRq)->getData();
            }
        } elseif ($request->has('unfollow')) {
            // unfollow group
            if (in_array($request->unfollow, $groupIds) && in_array($request->unfollow, $followed)) {
                $followRq = Request::create('api/unFollow', 'POST', [
                    'api_key'  => $userData['api_key'],
                    'user_id'  => $userData['id'],
                    'group_id' => $request->unfollow,
                ]);
                $apiFollow = new ApiFollow($followRq);
                $followResult = $apiFollow->unFollow($followRq)->getData();
            }
        }

        return $followResult;
    }

    /**
     * Deletes a group
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view to previous page
     */
    public function delete(Request $request)
    {
        if (\Auth::check() && $request->has('delete') && $request->has('group_uri')) {
            $rq = Request::create('/api/getGroupDetails', 'POST', ['group_uri' => $request->group_uri]);
            $api = new ApiOrganisation($rq);
            $res = $api->getGroupDetails($rq)->getData();
            $group = !empty($res->data) ? $res->data : [];

            if (empty($group)) {
                return redirect()->back();
            }

            // check delete rights
            $checkData = [
                'group_id' => $group->id
            ];
            $objData = [
                'group_ids' => [$group->id]
            ];
            $rightCheck = RoleRight::checkUserRight(Module::GROUPS, RoleRight::RIGHT_ALL, $checkData, $objData);

            if (!$rightCheck) {
                return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
            }

            $params = [
                'api_key'   => \Auth::user()->api_key,
                'group_id'  => $group->id,
            ];

            $delReq = Request::create('/api/deleteGroup', 'POST', $params);
            $api = new ApiOrganisation($delReq);
            $result = $api->deleteGroup($delReq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.delete_success'));

                return redirect()->route('groups', array_except($request->query(), ['page']));
            }

            $request->session()->flash('alert-danger', isset($result->error) ? $result->error->message : __('custom.delete_error'));

            return redirect()->back();
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    public function chronology(Request $request, $uri)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        $params = [
            'group_uri' => $uri,
            'locale'  => $locale
        ];

        $rq = Request::create('/api/getGroupDetails', 'POST', $params);
        $api = new ApiOrganisation($rq);
        $res = $api->getGroupDetails($rq)->getData();
        $group = !empty($res->data) ? $res->data : [];

        if (!empty($group)) {
            $params = [
                'criteria' => [
                    'group_ids' => [$group->id],
                    'locale' => $locale
                ]
            ];
            $rq = Request::create('/api/listDatasets', 'POST', $params);
            $api = new ApiDataSet($rq);
            $res = $api->listDatasets($rq)->getData();

            $criteria = [
                'group_ids' => [$group->id]
            ];

            $objType = Module::getModuleName(Module::GROUPS);
            $actObjData[$objType] = [];
            $actObjData[$objType][$group->id] = [
                'obj_id'        => $group->uri,
                'obj_name'      => $group->name,
                'obj_module'    => ultrans('custom.groups'),
                'obj_type'      => 'group',
                'obj_view'      => '/groups/view/'. $group->uri,
                'parent_obj_id' => ''
            ];

            if (isset($res->success) && $res->success && !empty($res->datasets)) {
                $objType = Module::getModuleName(Module::DATA_SETS);
                $objTypeRes = Module::getModuleName(Module::RESOURCES);
                $actObjData[$objType] = [];

                foreach ($res->datasets as $dataset) {
                    $objOwner = [];
                    if ($dataset->org_id) {
                        // get organisation details
                        $params = [
                            'org_id' => $dataset->org_id,
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

                    $criteria['dataset_ids'][] = $dataset->id;
                    $actObjData[$objType][$dataset->id] = [
                        'obj_id'        => $dataset->uri,
                        'obj_name'      => $dataset->name,
                        'obj_module'    => ultrans('custom.dataset'),
                        'obj_type'      => 'dataset',
                        'obj_view'      => '/data/view/'. $dataset->uri,
                        'parent_obj_id' => '',
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
                'group.chronology',
                [
                    'class'          => 'user',
                    'group'          => $group,
                    'chronology'     => !empty($paginationData['items']) ? $paginationData['items'] : [],
                    'pagination'     => !empty($paginationData['paginate']) ? $paginationData['paginate'] : [],
                    'actionObjData'  => $actObjData,
                    'actionTypes'    => $actTypes,
                ]
            );
        }

        return redirect()->back();
    }
}

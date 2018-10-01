<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Module;
use App\UserSetting;
use App\Organisation;
use App\CustomSetting;
use App\ActionsHistory;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\RoleController as ApiRole;
use App\Http\Controllers\Api\UserController as ApiUser;
use App\Http\Controllers\Api\DataSetController as ApiDataSet;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;
use App\Http\Controllers\Api\ActionsHistoryController as ApiActionsHistory;

class GroupController extends AdminController
{
    /**
     * Lists the groups in which the user is a member of
     *
     * @param Request $request
     *
     * @return view with list of groups
     */
    public function list(Request $request)
    {
        $class = 'user';
        $perPage = 6;
        $params = [
            'api_key'          => \Auth::user()->api_key,
            'criteria'         => [],
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];

        $orgReq = Request::create('/api/listGroups', 'POST', $params);
        $api = new ApiOrganisation($orgReq);
        $result = $api->listGroups($orgReq)->getData();

        $groups = !empty($result->groups) ? $result->groups : [];
        $count = !empty($result->total_records) ? $result->total_records : 0;

        $paginationData = $this->getPaginationData($groups, $count, [], $perPage);

        return view('/admin/groups', [
            'class'         => 'user',
            'groups'        => $paginationData['items'],
            'pagination'    => $paginationData['paginate']
        ]);
    }

    /**
     * Registers a group
     *
     * @param Request $request
     *
     * @return view with registered group
     */
    public function register(Request $request)
    {
        if ($request->has('back')) {
            return redirect()->route('adminGroups');
        }

        $class = 'user';
        $fields = $this->getGroupTransFields();

        if ($request->has('create')) {
            $data = $request->all();
            $data['description'] = $data['descript'];

            if (!empty($data['logo'])) {
                $data['logo_filename'] = $data['logo']->getClientOriginalName();
                $data['logo'] = $data['logo']->getPathName();
            }

            $params = [
                'api_key'   => \Auth::user()->api_key,
                'data'      => $data,
            ];

            $groupReq = Request::create('api/addGroup', 'POST', $params);
            $orgApi = new ApiOrganisation($groupReq);
            $result = $orgApi->addGroup($groupReq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.successful_group_creation'));

                return redirect('/admin/groups/view/'. Organisation::where('id', $result->id)->value('uri'));
            } else {
                $request->session()->flash('alert-danger', __('custom.failed_group_creation'));

                return back()->withErrors($result->errors)->withInput(Input::all());
            }
        }

        return view('/admin/groupRegistration', compact('class', 'fields'));
    }

    /**
     * Displays information for a given group
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view on success on failure redirect to homepage
     */
    public function view(Request $request, $uri)
    {
        if ($request->has('back')) {
            return redirect()->route('adminGroups');
        }

        $orgId = Organisation::where('uri', $uri)
            ->where('type', Organisation::TYPE_GROUP)
            ->value('id');

        $request = Request::create('/api/getGroupDetails', 'POST', [
            'group_id'  => $orgId,
            'locale'    => \LaravelLocalization::getCurrentLocale(),
        ]);
        $api = new ApiOrganisation($request);
        $result = $api->getGroupDetails($request)->getData();

        if ($result->success) {
            return view('admin/groupView', ['class' => 'user', 'group' => $result->data, 'id' => $orgId]);
        }

        return redirect('/admin/groups');
    }

    /**
     * Deletes a group
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view to previous page
     */
    public function delete(Request $request, $id)
    {
        $orgId = Organisation::where('id', $id)
            ->where('type', Organisation::TYPE_GROUP)
            ->value('id');

        $delArr = [
            'api_key'   => \Auth::user()->api_key,
            'group_id'  => $id,
        ];

        $delReq = Request::create('/api/deleteGroup', 'POST', $delArr);
        $api = new ApiOrganisation($delReq);
        $result = $api->deleteGroup($delReq)->getData();

        if ($result->success) {
            $request->session()->flash('alert-success', __('custom.delete_success'));

            return redirect('/admin/groups');
        }

        $request->session()->flash('alert-danger', __('custom.delete_error'));

        return redirect('/admin/groups');
    }

    /**
     * Edit a group based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function edit(Request $request, $uri)
    {
        $org = Organisation::where('uri', $uri)
            ->where('type', Organisation::TYPE_GROUP)
            ->first();

        if (empty($org)) {
            return redirect('/admin/groups');
        }

        $class = 'user';
        $fields = $this->getGroupTransFields();
        $root = 'admin';

        $model = Organisation::find($org->id)->loadTranslations();
        $withModel = CustomSetting::where('org_id', $org->id)->get()->loadTranslations();
        $model->logo = $this->getImageData($model->logo_data, $model->logo_mime_type, 'group');

        if ($request->has('edit')) {
            $data = $request->all();

            $data['description'] = isset($data['descript']) ? $data['descript'] : null;

            if (!empty($data['logo'])) {
                $data['logo_filename'] = $data['logo']->getClientOriginalName();
                $data['logo'] = $data['logo']->getPathName();
            }

            $params = [
                'api_key'   => \Auth::user()->api_key,
                'group_id'  => $org->id,
                'data'      => $data,
            ];

            $editReq = Request::create('/api/editGroup', 'POST', $params);
            $api = new ApiOrganisation($editReq);
            $result = $api->editGroup($editReq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.edit_success'));

                if (!empty($params['data']['uri'])) {
                    return redirect(url('/admin/groups/edit/'. $params['data']['uri']));
                }
            } else {
                $request->session()->flash('alert-danger', __('custom.edit_error'));
            }

            return back()->withErrors(isset($result->errors) ? $result->errors : []);
        }

        return view('admin/groupEdit', compact('class', 'fields', 'model', 'withModel', 'root'));
    }

    /**
     * Filters groups based on search string
     *
     * @param Request $request
     *
     * @return view with filtered group list
     */
    public function search(Request $request)
    {
        $perPage = 6;
        $search = $request->offsetGet('q');

        if (empty($search)) {
            return redirect('admin/groups');
        }

        $params = [
            'records_per_page'  => $perPage,
            'criteria'          => [
                'keywords'          => $search,
            ]
        ];

        $searchRq = Request::create('/api/searchGroups', 'POST', $params);
        $api = new ApiOrganisation($searchRq);
        $grpData = $api->searchGroups($searchRq)->getData();

        $groups = !empty($grpData->groups) ? $grpData->groups : [];
        $count = !empty($grpData->total_records) ? $grpData->total_records : 0;

        $getParams = [
            'search' => $search
        ];

        $paginationData = $this->getPaginationData($groups, $count, $getParams, $perPage);

        return view('admin/groups', [
            'class'         => 'user',
            'groups'        => $paginationData['items'],
            'pagination'    => $paginationData['paginate'],
            'search'        => $search,
        ]);
    }

    public function viewMembers(Request $request, $uri)
    {
        $perPage = 6;
        $filter = $request->offsetGet('filter');
        $userId = $request->offsetGet('user_id');
        $roleId = $request->offsetGet('role_id');
        $keywords = $request->offsetGet('keywords');
        $group = Organisation::where('uri', $uri)->first();

        if ($group) {
            if ($request->has('edit_member')) {
                if(empty($roleId)) {
                    return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.empty_role')));
                }

                $rq = Request::create('/api/editMember', 'POST', [
                    'org_id'    => $group->id,
                    'user_id'   => $userId,
                    'role_id'   => $roleId,
                ]);
                $api = new ApiOrganisation($rq);
                $result = $api->editMember($rq)->getData();

                if (!empty($result->success)) {
                    $request->session()->flash('alert-success', __('custom.edit_success'));
                } else {
                    $request->session()->flash('alert-danger', __('custom.edit_error'));
                }

                return back();
            }

            if ($request->has('delete')) {
                if (app('App\Http\Controllers\UserController')->delMember($userId, $group->id)) {
                    $request->session()->flash('alert-success', __('custom.delete_success'));
                } else {
                    $request->session()->flash('alert-danger', __('custom.delete_error'));
                }

                return back();
            }

            if ($request->has('invite_existing')) {
                $newUser = $request->offsetGet('user');
                $newRole = $request->offsetGet('role');

                $rq = Request::create('/api/addMember', 'POST', [
                    'org_id'    => $group->id,
                    'user_id'   => $newUser,
                    'role_id'   => $newRole,
                ]);
                $api = new ApiOrganisation($rq);
                $result = $api->addMember($rq)->getData();

                if (!empty($result->success)) {
                    $request->session()->flash('alert-success', __('custom.add_success'));
                } else {
                    $request->session()->flash('alert-danger', __('custom.add_error'));
                }

                return back();
            }

            if ($request->has('invite')) {
                $email = $request->offsetGet('email');
                $role = $request->offsetGet('role');

                $rq = Request::create('/inviteUser', 'POST', [
                    'api_key'   => Auth::user()->api_key,
                    'data'      => [
                        'email'     => $email,
                        'org_id'    => $group->id,
                        'role_id'   => $role,
                        'generate'  => true,
                    ],
                ]);
                $api = new ApiUser($rq);
                $result = $api->inviteUser($rq)->getData();

                if (!empty($result->success)) {
                    $request->session()->flash('alert-success', __('custom.confirm_mail_sent'));
                } else {
                    $request->session()->flash('alert-danger', __('custom.add_error'));
                }

                return back();
            }

            $group->logo = $this->getImageData($group->logo_data, $group->logo_mime_type, 'group');

            $criteria = ['org_id' => $group->id];

            if ($filter == 'for_approval') {
                $criteria['for_approval'] = true;
            }

            if (is_numeric($filter)) {
                $criteria['role_id'] = $filter;
            }

            if (!empty($keywords)) {
                $criteria['keywords'] = $keywords;
            }

            $criteria['records_per_page'] = $perPage;
            $criteria['page_number'] = $request->offsetGet('page', 1);

            $rq = Request::create('/api/getMembers', 'POST', $criteria);
            $api = new ApiOrganisation($rq);
            $result = $api->getMembers($rq)->getData();
            $paginationData = $this->getPaginationData(
                $result->members,
                $result->total_records,
                $request->except('page'),
                $perPage
            );

            $rq = Request::create('/api/listRoles', 'POST', ['criteria' => ['for_group' => 1]]);
            $api = new ApiRole($rq);
            $result = $api->listRoles($rq)->getData();
            $roles = isset($result->roles) ? $result->roles : [];

            return view('admin/groupMembers', [
                'class'         => 'user',
                'members'       => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'group'         => $group,
                'roles'         => $roles,
                'filter'        => $filter,
                'keywords'      => $keywords,
                'isAdmin'       => true
            ]);
        }

        return redirect('/admin/groups');
    }

    public function addMembersNew(Request $request, $uri)
    {
        $group = Organisation::where('uri', $uri)->first();
        $class = 'user';

        if ($group) {
            $rq = Request::create('/api/listRoles', 'POST', ['criteria' => ['for_group' => 1]]);
            $api = new ApiRole($rq);
            $result = $api->listRoles($rq)->getData();
            $roles = isset($result->roles) ? $result->roles : [];
            $digestFreq = UserSetting::getDigestFreq();

            if ($request->has('save')) {
                $post = [
                    'api_key'   => Auth::user()->api_key,
                    'data'      => [
                        'firstname'         => $request->offsetGet('firstname'),
                        'lastname'          => $request->offsetGet('lastname'),
                        'username'          => $request->offsetGet('username'),
                        'email'             => $request->offsetGet('email'),
                        'password'          => $request->offsetGet('password'),
                        'password_confirm'  => $request->offsetGet('password_confirm'),
                        'role_id'           => $request->offsetGet('role_id'),
                        'org_id'            => $group->id,
                        'invite'            => true,
                    ],
                ];

                $rq = Request::create('/api/register', 'POST', $post);
                $api = new ApiUser($rq);
                $result = $api->register($rq)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.confirm_mail_sent'));

                    return redirect('/admin/groups/members/'. $uri);
                } else {
                    $request->session()->flash('alert-danger', __('custom.add_error'));

                    return redirect()->back()->withInput()->withErrors($result->errors);
                }
            }
        }

        return view('admin/addGroupMembersNew', compact('class', 'error', 'digestFreq', 'invMail', 'roles', 'group'));
    }

    public function chronology(Request $request, $uri)
    {
        $class = 'user';
        $group = Organisation::where('uri', $uri)->first();
        $group->logo = $this->getImageData($group->logo_data, $group->logo_mime_type, 'group');
        $locale = \LaravelLocalization::getCurrentLocale();

        $params = [
            'group_uri' => $uri,
            'locale'    => $locale
        ];

        $rq = Request::create('/api/getGroupDetails', 'POST', $params);
        $api = new ApiOrganisation($rq);
        $result = $api->getGroupDetails($rq)->getData();

        if ($result->success && !empty($result->data)) {
            $params = [
                'api_key'   => \Auth::user()->api_key,
                'criteria'  => [
                    'group_ids' => [$group->id],
                    'locale'    => $locale
                ]
            ];

            $rq = Request::create('/api/listDataSets', 'POST', $params);
            $api = new ApiDataSet($rq);
            $res = $api->listDataSets($rq)->getData();

            $criteria = [
                'group_ids' => [$group->id]
            ];

            $objType = Module::getModules()[Module::GROUPS];
            $actObjData[$objType] = [];
            $actObjData[$objType][$group->id] = [
                'obj_id'        => $group->uri,
                'obj_name'      => $group->name,
                'obj_module'    => Str::lower(utrans('custom.organisations')),
                'obj_type'      => 'org',
                'obj_view'      => '/admin/groups/view/'. $group->uri,
                'parent_obj_id' => ''
            ];

            if (isset($res->success) && $res->success && !empty($res->datasets)) {
                $objType = Module::getModules()[Module::DATA_SETS];;
                $objTypeRes =Module::getModules()[Module::RESOURCES];;
                $actObjData[$objType] = [];

                foreach ($res->datasets as $dataset) {
                    $criteria['dataset_ids'][] = $dataset->id;
                    $actObjData[$objType][$dataset->id] = [
                        'obj_id'        => $dataset->uri,
                        'obj_name'      => $dataset->name,
                        'obj_module'    => Str::lower(__('custom.dataset')),
                        'obj_type'      => 'dataset',
                        'obj_view'      => '/user/group/'. $group->uri .'/dataset/'. $dataset->uri,
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
                                'obj_view'          => '/user/group/'. $group->uri .'/resource/'. $resource->uri,
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
                    $perPage = 6;
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
                'user/groupChronology',
                [
                    'class'          => $class,
                    'organisation'   => $group,
                    'chronology'     => !empty($paginationData['items']) ? $paginationData['items'] : [],
                    'pagination'     => !empty($paginationData['paginate']) ? $paginationData['paginate'] : [],
                    'actionObjData'  => $actObjData,
                    'actionTypes'    => $actTypes,
                ]
            );
        }

        return redirect('admin/groups');
    }
}

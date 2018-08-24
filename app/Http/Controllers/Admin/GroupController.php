<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\UserSetting;
use App\Organisation;
use App\CustomSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\UserController as ApiUser;
use App\Http\Controllers\Api\RoleController as ApiRole;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;

class GroupController extends AdminController
{
    /**
     * Function for getting an array of translatable fields for groups
     *
     * @return array of fields
     */
    public static function getGroupTransFields()
    {
        return [
            [
                'label'    => 'custom.label_name',
                'name'     => 'name',
                'type'     => 'text',
                'view'     => 'translation',
                'required' => true,
            ],
            [
                'label'    => 'custom.description',
                'name'     => 'descript',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
            [
                'label'    => ['custom.title', 'custom.value'],
                'name'     => 'custom_fields',
                'type'     => 'text',
                'view'     => 'translation_custom',
                'val'      => ['key', 'value'],
                'required' => false,
            ],
        ];
    }

    /**
     * Lists the groups in which the user is a member of
     *
     * @param Request $request
     *
     * @return view with list of groups
     */
    public function list(Request $request)
    {
        if (Role::isAdmin()) {
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

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
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
        if (Role::isAdmin()) {
            $class = 'user';
            $fields = self::getGroupTransFields();

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

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
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
        $orgId = Organisation::where('uri', $uri)
            ->where('type', Organisation::TYPE_GROUP)
            ->value('id');

        if (Role::isAdmin($orgId)) {
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

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
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

        if (Role::isAdmin($orgId)) {
            $delArr = [
                'api_key'   => \Auth::user()->api_key,
                'group_id'  => $id,
            ];

            $delReq = Request::create('/api/deleteGroup', 'POST', $delArr);
            $api = new ApiOrganisation($delReq);
            $result = $api->deleteGroup($delReq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.delete_success'));

                return back();
            }


            $request->session()->flash('alert-danger', __('custom.delete_error'));

            return back();
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
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
        $orgId = Organisation::where('uri', $uri)
            ->where('type', Organisation::TYPE_GROUP)
            ->value('id');

        if (Role::isAdmin($orgId)) {
            $class = 'user';
            $fields = self::getGroupTransFields();

            $model = Organisation::find($orgId)->loadTranslations();
            $withModel = CustomSetting::where('org_id', $orgId)->get()->loadTranslations();
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
                    'group_id'  => $orgId,
                    'data'      => $data,
                ];

                $editReq = Request::create('/api/editGroup', 'POST', $params);
                $api = new ApiOrganisation($editReq);
                $result = $api->editGroup($editReq)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.edit_success'));
                } else {
                    $request->session()->flash('alert-danger', __('custom.edit_error'));
                }

                return back()->withErrors(isset($result->errors) ? $result->errors : []);
            }

            return view('admin/groupEdit', compact('class', 'fields', 'model', 'withModel'));
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
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
        if (Role::isAdmin()) {
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

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    public function viewMembers(Request $request, $uri)
    {
        $perPage = 6;
        $filter = $request->offsetGet('filter');
        $userId = $request->offsetGet('user_id');
        $roleId = $request->offsetGet('role_id');
        $keywords = $request->offsetGet('keywords');
        $group = Organisation::where('uri', $uri)->first();
        $isAdmin = Role::isAdmin($group->id);

        if ($isAdmin) {
            if ($group) {
                if ($request->has('edit_member')) {
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

                $group->logo = $this->getImageData($group->logo_data, $group->logo_mime_type);

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

                $rq = Request::create('/api/listRoles', 'POST');
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
                    'isAdmin'       => $isAdmin
                ]);
            }

            return redirect('/admin/groups');
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    public function addMembersNew(Request $request, $uri)
    {
        $group = Organisation::where('uri', $uri)->first();
        $class = 'user';

        if ($group) {
            if (Role::isAdmin($group->id)) {
                $rq = Request::create('/api/listRoles', 'POST');
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
                        ],
                    ];

                    $rq = Request::create('/api/addUser', 'POST', $post);
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
            } else {
                return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
            }
        }

        return view('admin/addGroupMembersNew', compact('class', 'error', 'digestFreq', 'invMail', 'roles', 'group'));
    }
}

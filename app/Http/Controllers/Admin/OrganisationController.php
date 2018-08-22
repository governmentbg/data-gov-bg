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
use App\Http\Controllers\Api\RoleController as ApiRole;
use App\Http\Controllers\Api\UserController as ApiUser;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;

class OrganisationController extends AdminController
{
    /**
     * Function for getting an array of translatable fields
     *
     * @return array of fields
     */
    public static function getTransFields()
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
                'label'    => 'custom.activity',
                'name'     => 'activity_info',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
            [
                'label'    => 'custom.contact',
                'name'     => 'contacts',
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
     * Loads a view for browsing organisations
     *
     * @param Request $request
     *
     * @return view for browsing organisations
     */
    public function list(Request $request)
    {
        if (Role::isAdmin()) {
            $perPage = 6;
            $params = [
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
            ];

            if (isset($request->active)) {
                $params['criteria']['active'] = (bool) $request->active;
            }

            if (isset($request->approved)) {
                $params['criteria']['approved'] = (bool) $request->approved;
            }

            if (isset($request->parent)) {
                $parent = Organisation::where('uri', $request->parent)->first();

                if (isset($parent->id)) {
                    $params['criteria']['org_id'] = $parent->id;
                }
            }

            $request = Request::create('/api/listOrganisations', 'POST', $params);
            $api = new ApiOrganisation($request);
            $result = $api->listOrganisations($request)->getData();

            $paginationData = $this->getPaginationData(
                $result->organisations,
                $result->total_records,
                array_except(app('request')->input(), ['q', 'page',]),
                $perPage
            );

            return view(
                'admin/organisations',
                [
                    'class'         => 'user',
                    'organisations' => $paginationData['items'],
                    'pagination'    => $paginationData['paginate'],
                    'selectedOrg'   => isset($parent) && !empty($parent->id)
                        ? $parent
                        : null
                ]
            );
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
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
        if (Role::isAdmin()) {
            $search = $request->q;

            if (empty(trim($search))) {
                return redirect('/admin/organisations');
            }

            $perPage = 6;
            $params = [
                'api_key'          => \Auth::user()->api_key,
                'criteria'         => [
                    'keywords' => $search,
                ],
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
            ];

            $request = Request::create('/api/searchOrganisations', 'POST', $params);
            $api = new ApiOrganisation($request);
            $result = $api->searchOrganisations($request)->getData();
            $organisations = !empty($result->organisations) ? $result->organisations : [];
            $count = !empty($result->total_records) ? $result->total_records : 0;

            $getParams = ['q' => $search];

            $paginationData = $this->getPaginationData(
                $organisations,
                $count,
                $getParams,
                $perPage
            );

            return view(
                'admin/organisations',
                [
                    'class'         => 'user',
                    'organisations' => $paginationData['items'],
                    'pagination'    => $paginationData['paginate'],
                    'search'        => $search
                ]
            );
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Loads a view for registering an organisation
     *
     * @param Request $request
     *
     * @return view to register an organisation or
     * a view to view the registered organisation
     */
    public function register(Request $request)
    {
        if (Role::isAdmin()) {
            $post = [
                'data' => $request->all()
            ];

            if (!empty($post['data']['logo'])) {
                $post['data']['logo_filename'] = $post['data']['logo']->getClientOriginalName();
                $post['data']['logo'] = $post['data']['logo']->getPathName();
            }

            $post['data']['description'] = $post['data']['descript'];
            $request = Request::create('/api/addOrganisation', 'POST', $post);
            $api = new ApiOrganisation($request);
            $result = $api->addOrganisation($request)->getData();

            if ($result->success) {
                session()->flash('alert-success', __('custom.add_org_success'));
            } else {
                session()->flash(
                    'alert-danger',
                    isset($result->error) ? $result->error->message : __('custom.add_org_error')
                );
            }

            return $result->success
                ? redirect('admin/organisations/view/'. Organisation::where('id', $result->org_id)->value('uri'))
                : redirect('admin/organisations/register')->withInput(Input::all())->withErrors($result->errors);
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Loads a view for registering an organisations
     *
     * @return view login on success or error on fail
     */
    public function showOrgRegisterForm() {

        if (Role::isAdmin()) {
            $query = Organisation::select('id', 'name')->where('type', '!=', Organisation::TYPE_GROUP);

            $query->whereHas('userToOrgRole', function($q) {
                $q->where('user_id', \Auth::user()->id);
            });

            $parentOrgs = $query->get();

            return view(
                'admin/orgRegister',
                [
                    'class'      => 'user',
                    'fields'     => self::getTransFields(),
                    'parentOrgs' => $parentOrgs
                ]
            );
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Loads a view for viewing an organisation
     *
     * @param Request $request
     *
     * @return view to view the a registered organisation
     */
    public function view(Request $request, $uri)
    {
        $orgId = Organisation::where('uri', $uri)
            ->whereIn('type', array_flip(Organisation::getPublicTypes()))
            ->value('id');

        if (Role::isAdmin($orgId)) {
            $request = Request::create('/api/getOrganisationDetails', 'POST', ['org_id' => $orgId]);
            $api = new ApiOrganisation($request);
            $result = $api->getOrganisationDetails($request)->getData();

            if ($result->success) {
                return view('admin/orgView', ['class' => 'user', 'organisation' => $result->data]);
            }

            return redirect('/admin/organisations');
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Loads a view for editing an organisation
     *
     * @param Request $request
     *
     * @return view for editing org details
     */
    public function edit(Request $request, $uri)
    {
        $orgId = Organisation::where('uri', $uri)
            ->whereIn('type', array_flip(Organisation::getPublicTypes()))
            ->value('id');

        if (Role::isAdmin($orgId)) {
            $query = Organisation::select('id', 'name')->where('type', '!=', Organisation::TYPE_GROUP);

            $parentOrgs = $query->get();

            $orgModel = Organisation::with('CustomSetting')->find($orgId)->loadTranslations();
            $customModel = CustomSetting::where('org_id', $orgModel->id)->get()->loadTranslations();
            $orgModel->logo = $this->getImageData($orgModel->logo_data, $orgModel->logo_mime_type);

            if (isset($request->view)) {

                return view(
                    'admin/orgEdit',
                    [
                        'class'      => 'user',
                        'model'      => $orgModel,
                        'withModel'  => $customModel,
                        'fields'     => self::getTransFields(),
                        'parentOrgs' => $parentOrgs
                    ]
                );
            }

            $post = [
                'data'          => $request->all(),
                'org_id'        => $orgId,
                'parentOrgs'    => $parentOrgs,
            ];

            if (!empty($post['data']['logo'])) {
                $post['data']['logo_filename'] = $post['data']['logo']->getClientOriginalName();
                $post['data']['logo'] = $post['data']['logo']->getPathName();
            }

            if (isset($post['data']['descript'])) {
                $post['data']['description'] = $post['data']['descript'];
            }

            if ($request->has('save')) {
                $request = Request::create('/api/editOrganisation', 'POST', $post);
                $api = new ApiOrganisation($request);
                $result = $api->editOrganisation($request)->getData();
                $errors = !empty($result->errors) ? $result->errors : [];

                $orgModel = Organisation::with('CustomSetting')->find($orgId)->loadTranslations();
                $customModel = CustomSetting::where('org_id', $orgModel->id)->get()->loadTranslations();
                $orgModel->logo = $this->getImageData($orgModel->logo_data, $orgModel->logo_mime_type);

                if ($result->success) {
                    session()->flash('alert-success', __('custom.edit_success'));
                } else {
                    session()->flash('alert-danger', __('custom.edit_error'));
                }

                return !$result->success
                    ? view(
                        'admin/orgEdit',
                        [
                            'class'      => 'user',
                            'model'      => $orgModel,
                            'withModel'  => $customModel,
                            'fields'     => self::getTransFields(),
                            'parentOrgs' => $parentOrgs
                        ]
                    )->withErrors($result->errors)
                    : view(
                        'admin/orgEdit',
                        [
                            'class'      => 'user',
                            'model'      => $orgModel,
                            'withModel'  => $customModel,
                            'fields'     => self::getTransFields(),
                            'parentOrgs' => $parentOrgs
                        ]
                    );

                return redirect('/admin/organisations');
            }
            return view(
                'admin/orgEdit',
                [
                    'class'      => 'user',
                    'model'      => $orgModel,
                    'withModel'  => $customModel,
                    'fields'     => self::getTransFields(),
                    'parentOrgs' => $parentOrgs
                ]);
        }


        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Loads a view for deleting organisations
     *
     * @param Request $request
     *
     * @return view with a list of organisations and request success message
     */
    public function delete(Request $request, $id)
    {
        $orgId = Organisation::where('id', $id)
            ->whereIn('type', array_flip(Organisation::getPublicTypes()))
            ->value('id');

        if (Role::isAdmin($orgId)) {
            $params = [
                'api_key' => \Auth::user()->api_key,
                'org_id'  => $id,
            ];

            $request = Request::create('/api/deleteOrganisation', 'POST', $params);
            $api = new ApiOrganisation($request);
            $result = $api->deleteOrganisation($request)->getData();

            if ($result->success) {
                session()->flash('alert-success', __('custom.delete_success'));

                return back();
            }

            session()->flash('alert-danger', __('custom.delete_error'));

            return back();
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
        $org = Organisation::where('uri', $uri)->first();
        $isAdmin = Role::isAdmin($org->id);

        if ($isAdmin) {
            if ($org) {
                if ($request->has('edit_member')) {
                    $rq = Request::create('/api/editMember', 'POST', [
                        'org_id'    => $org->id,
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
                    if (app('App\Http\Controllers\UserController')->delMember($userId, $org->id)) {
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
                        'org_id'    => $org->id,
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
                            'org_id'    => $org->id,
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

                $org->logo = $this->getImageData($org->logo_data, $org->logo_mime_type);

                $criteria = ['org_id' => $org->id];

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

                return view('admin/orgMembers', [
                    'class'         => 'user',
                    'members'       => $paginationData['items'],
                    'pagination'    => $paginationData['paginate'],
                    'organisation'  => $org,
                    'roles'         => $roles,
                    'filter'        => $filter,
                    'keywords'      => $keywords,
                    'isAdmin'       => $isAdmin
                ]);
            }

            return redirect('/admin/organisations');
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    public function addMembersNew(Request $request, $uri)
    {
        $organisation = Organisation::where('uri', $uri)->first();
        $class = 'user';

        if ($organisation) {
            if (Role::isAdmin($organisation->id)) {
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
                            'org_id'            => $organisation->id,
                        ],
                    ];

                    $rq = Request::create('/api/addUser', 'POST', $post);
                    $api = new ApiUser($rq);
                    $result = $api->register($rq)->getData();

                    if ($result->success) {
                        $request->session()->flash('alert-success', __('custom.confirm_mail_sent'));

                        return redirect('/admin/organisations/members/'. $uri);
                    } else {
                        $request->session()->flash('alert-danger', __('custom.add_error'));

                        return redirect()->back()->withInput()->withErrors($result->errors);
                    }
                }
            } else {
                return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
            }
        }

        return view('admin/addOrgMembersNew', compact('class', 'error', 'digestFreq', 'invMail', 'roles', 'organisation'));
    }
}

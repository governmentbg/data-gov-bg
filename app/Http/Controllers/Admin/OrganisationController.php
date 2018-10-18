<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\User;
use App\Module;
use App\DataSet;
use App\Category;
use App\Resource;
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
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;
use App\Http\Controllers\Api\ActionsHistoryController as ApiActionsHistory;

class OrganisationController extends AdminController
{
    /**
     * Loads a view for browsing organisations
     *
     * @param Request $request
     *
     * @return view for browsing organisations
     */
    public function list(Request $request)
    {
        $perPage = 6;
        $params = [
            'api_key'          => Auth::user()->api_key,
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

        $request = Request::create('/api/listOrganisations', 'POST', $params);
        $api = new ApiOrganisation($request);
        $result = $api->listOrganisations($request)->getData();
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
        if ($request->has('back')) {
            return redirect()->route('adminOrgs');
        }

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

    /**
     * Loads a view for registering an organisations
     *
     * @return view login on success or error on fail
     */
    public function showOrgRegisterForm()
    {
        $parentOrgs = Organisation::select('id', 'name')
            ->where('type', '!=', Organisation::TYPE_GROUP)->get();

        return view(
            'admin/orgRegister',
            [
                'class'      => 'user',
                'fields'     => $this->getTransFields(),
                'parentOrgs' => $parentOrgs
            ]
        );
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
        if ($request->has('back')) {
            return redirect()->route('adminOrgs');
        }

        $orgId = Organisation::where('uri', $uri)
            ->whereIn('type', array_flip(Organisation::getPublicTypes()))
            ->value('id');

        $request = Request::create('/api/getOrganisationDetails', 'POST', ['org_id' => $orgId]);
        $api = new ApiOrganisation($request);
        $result = $api->getOrganisationDetails($request)->getData();

        if ($result->success) {
            return view('admin/orgView', ['class' => 'user', 'organisation' => $this->getModelUsernames($result->data)]);
        }

        return redirect('/admin/organisations');
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
        $org = Organisation::where('uri', $uri)
            ->whereIn('type', array_flip(Organisation::getPublicTypes()))
            ->first();

        if (empty($org)) {
            return redirect('/admin/organisations');
        }

        $query = Organisation::select('id', 'name')->where('type', '!=', Organisation::TYPE_GROUP);

        $parentOrgs = $query->get();

        $orgModel = Organisation::with('CustomSetting')->find($org->id)->loadTranslations();
        $customModel = CustomSetting::where('org_id', $orgModel->id)->get()->loadTranslations();
        $orgModel->logo = $this->getImageData($orgModel->logo_data, $orgModel->logo_mime_type);
        $root = 'admin';

        $viewData = [
            'class'      => 'user',
            'model'      => $orgModel,
            'withModel'  => $customModel,
            'fields'     => $this->getTransFields(),
            'parentOrgs' => $parentOrgs,
            'root'       => $root
        ];

        if (isset($request->view)) {
            return view('admin/orgEdit', $viewData);
        }

        $post = [
            'api_key'       => Auth::user()->api_key,
            'data'          => $request->all(),
            'org_id'        => $org->id,
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

            $orgModel = Organisation::with('CustomSetting')->find($org->id)->loadTranslations();
            $customModel = CustomSetting::where('org_id', $orgModel->id)->get()->loadTranslations();
            $orgModel->logo = $this->getImageData($orgModel->logo_data, $orgModel->logo_mime_type);

            if ($result->success) {
                session()->flash('alert-success', __('custom.edit_success'));

                if (!empty($post['data']['uri'])) {
                    return redirect(url('/admin/organisations/edit/'. $post['data']['uri']));
                }
            } else {
                session()->flash('alert-danger', __('custom.edit_error'));
            }
        }

        return view('admin/orgEdit', $viewData)->withErrors(isset($result->errors) ? $result->errors : []);
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

        $params = [
            'api_key' => \Auth::user()->api_key,
            'org_id'  => $id,
        ];

        $request = Request::create('/api/deleteOrganisation', 'POST', $params);
        $api = new ApiOrganisation($request);
        $result = $api->deleteOrganisation($request)->getData();

        if ($result->success) {
            session()->flash('alert-success', __('custom.delete_success'));

            return redirect('/admin/organisations');
        }

        session()->flash('alert-danger', __('custom.delete_error'));

        return redirect('/admin/organisations');
    }

    public function viewMembers(Request $request, $uri)
    {
        $perPage = 6;
        $filter = $request->offsetGet('filter');
        $userId = $request->offsetGet('user_id');
        $roleId = $request->offsetGet('role_id');
        $keywords = $request->offsetGet('keywords');
        $org = Organisation::where('uri', $uri)->first();

        if ($org) {
            if ($request->has('edit_member')) {
                if(empty($roleId)) {
                    return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.empty_role')));
                }

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
                $criteria['role_ids'] = [$filter];
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

            $rq = Request::create('/api/listRoles', 'POST', ['criteria' => ['for_org' => 1]]);
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
                'isAdmin'       => true
            ]);
        }

        return redirect('/admin/organisations');
    }

    public function addMembersNew(Request $request, $uri)
    {
        $organisation = Organisation::where('uri', $uri)->first();
        $class = 'user';

        if ($organisation) {
            $rq = Request::create('/api/listRoles', 'POST', ['criteria' => ['for_org' => 1]]);
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
                        'invite'            => true,
                    ],
                ];

                $rq = Request::create('/api/register', 'POST', $post);
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
        }

        return view('admin/addOrgMembersNew', compact('class', 'error', 'digestFreq', 'invMail', 'roles', 'organisation'));
    }

    public function chronology(Request $request, $uri)
    {
        $class = 'user';
        $locale = \LaravelLocalization::getCurrentLocale();

        $params = [
            'org_uri'   => $uri,
            'locale'    => $locale
        ];

        $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
        $api = new ApiOrganisation($rq);
        $result = $api->getOrganisationDetails($rq)->getData();

        if ($result->success && !empty($result->data)) {
            $params = [
                'api_key'   => \Auth::user()->api_key,
                'criteria'  => [
                    'org_ids'   => [$result->data->id],
                    'locale'    => $locale
                ]
            ];

            $rq = Request::create('/api/listDataSets', 'POST', $params);
            $api = new ApiDataSet($rq);
            $res = $api->listDataSets($rq)->getData();

            $criteria = [
                'org_ids' => [$result->data->id]
            ];

            $objType = Module::getModules()[Module::ORGANISATIONS];
            $actObjData[$objType] = [];
            $actObjData[$objType][$result->data->id] = [
                'obj_id'        => $result->data->uri,
                'obj_name'      => $result->data->name,
                'obj_module'    => Str::lower(utrans('custom.organisations')),
                'obj_type'      => 'org',
                'obj_view'      => '/admin/organisations/view/'. $result->data->uri,
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
                        'obj_view'      => '/user/organisations/dataset/view/'. $dataset->uri,
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
                                'obj_view'          => '/user/organisations/'. $result->data->uri .'/resource/'. $resource->uri,
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
                'user/orgChronology',
                [
                    'class'          => $class,
                    'organisation'   => $result->data,
                    'chronology'     => !empty($paginationData['items']) ? $paginationData['items'] : [],
                    'pagination'     => !empty($paginationData['paginate']) ? $paginationData['paginate'] : [],
                    'actionObjData'  => $actObjData,
                    'actionTypes'    => $actTypes,
                ]
            );
        }

        return redirect('admin/organisations');
    }

    public function orgDatasets(Request $request, $uri)
    {
        $org = Organisation::where('uri', $uri)->first();
        $orgId = !is_null($org) ? $org->id : null;

        if (!$orgId) {
            return back();
        }

        $org->logo = $this->getImageData($org->logo_data, $org->logo_mime_type);
        $perPage = 6;
        $params = [
            'api_key'           => \Auth::user()->api_key,
            'criteria'          => [
                'org_ids'       => [$orgId],
            ],
            'records_per_page'  => $perPage,
            'page_number'       => !empty($request->page) ? $request->page : 1,
        ];

        if ($request->has('q')) {
            $params['criteria']['keywords'] = $request->offsetGet('q');
        }

        $rq = Request::create('/api/listDatasets', 'POST', $params);
        $api = new ApiDataSet($rq);
        $datasets = $api->listDatasets($rq)->getData();
        $paginationData = $this->getPaginationData($datasets->datasets, $datasets->total_records, [], $perPage);

        // handle dataset delete
        if ($request->has('delete')) {
            $uri = $request->offsetGet('dataset_uri');

            if ($this->datasetDelete($uri)) {
                $request->session()->flash('alert-success', __('custom.success_dataset_delete'));
            } else {
                $request->session()->flash('alert-danger', __('custom.fail_dataset_delete'));
            }

            return back();
        }

        return view(
            'admin/orgDatasets',
            [
                'class'         => 'user',
                'datasets'      => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'activeMenu'    => 'organisation',
                'organisation'  => $org
            ]
        );
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Module;
use App\RoleRight;
use App\ActionsHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\RoleController as ApiRole;

class RoleController extends AdminController {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Show the role list.
     *
     * @return view with list and actions
     */
    public function list(Request $request)
    {
        $class = 'user';

        $rq = Request::create('/api/listRoles', 'POST');
        $api = new ApiRole($rq);
        $result = $api->listRoles($rq)->getData();
        $roles = isset($result->roles) ? $result->roles : [];

        return view('admin/roleList', compact('class', 'roles'));
    }

    /**
     * Show the role creation.
     *
     * @return view with inpits
     */
    public function addRole(Request $request)
    {
        $class = 'user';
        $errors = [];

        if ($request->has('save')) {
            $rq = Request::create('/api/addRole', 'POST', [
                'api_key'   => Auth::user()->api_key,
                'data'      => [
                    'name'                  => $request->offsetGet('name'),
                    'active'                => $request->get('active', false),
                    'default_user'          => $request->get('default_user', false),
                    'default_group_admin'   => $request->get('default_group_admin', false),
                    'default_org_admin'     => $request->get('default_org_admin', false),
                    'for_org'               => $request->get('for_org', null),
                    'for_group'             => $request->get('for_group', null),
                ],
            ]);
            $api = new ApiRole($rq);
            $result = $api->addRole($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.edit_success'));

                return redirect(url('admin/roles'));
            } else {
                $errors = $result->errors;
                $request->session()->flash('alert-danger', __('custom.edit_error'));
            }

            return back()->withInput()->withErrors($errors);
        }

        return view('admin/roleAdd', compact('class', 'roles'));
    }

    /**
     * Show edit role.
     *
     * @return view with inpits
     */
    public function editRole(Request $request, $id)
    {
        $class = 'user';
        $errors = [];
        $rq = Request::create('/api/listRoles', 'POST', ['criteria' => ['role_id' => $id]]);
        $api = new ApiRole($rq);
        $result = $api->listRoles($rq)->getData();
        $role = isset($result->roles) ? $result->roles : [];

        if ($request->has('edit')) {
            $rq = Request::create('/api/editRole', 'POST', [
                'api_key'   => Auth::user()->api_key,
                'id'        => $id,
                'data'      => [
                    'name'                  => $request->offsetGet('name'),
                    'active'                => $request->get('active', false),
                    'default_user'          => $request->get('default_user', false),
                    'default_group_admin'   => $request->get('default_group_admin', false),
                    'default_org_admin'     => $request->get('default_org_admin', false),
                    'for_org'               => $request->get('for_org', null),
                    'for_group'             => $request->get('for_group', null),
                ],
            ]);
            $api = new ApiRole($rq);
            $result = $api->editRole($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.edit_success'));

                return redirect(url('admin/roles'));
            } else {
                $errors = $result->errors;
                $request->session()->flash('alert-danger', __('custom.edit_error'));
            }

            return back()->withInput()->withErrors($errors);
        }

        return view('admin/roleEdit', compact('class', 'role'));
    }

    /**
     * Show view role.
     *
     * @return view
     */
    public function viewRole(Request $request, $id)
    {
        $class = 'user';

        $rq = Request::create('/api/listRoles', 'POST', ['criteria' => ['role_id' => $id]]);
        $api = new ApiRole($rq);
        $result = $api->listRoles($rq)->getData();
        $role = isset($result->roles) ? $result->roles : [];

        return view('admin/roleView', compact('class', 'role'));
    }

    /**
     * Delete role.
     *
     * @return view
     */
    public function deleteRole(Request $request, $id)
    {
        $rq = Request::create('/api/deleteRole', 'POST', [
            'api_key'   => Auth::user()->api_key,
            'id'        => $id,
        ]);
        $api = new ApiRole($rq);
        $result = $api->deleteRole($rq)->getData();

        if ($result->success) {
            $request->session()->flash('alert-success', __('custom.delete_success'));

            return redirect(url('admin/roles'));
        }

        $request->session()->flash('alert-danger', __('custom.delete_error'));

        return redirect(url('admin/roles'));
    }

    /**
     * Modify role rights.
     *
     * @return view with inpits
     */
    public function roleRights(Request $request, $id)
    {
        $class = 'user';
        $errors = [];

        $rq = Request::create('/api/getRoleRights', 'POST', ['id' => $id]);
        $api = new ApiRole($rq);
        $result = $api->getRoleRights($rq)->getData();
        $rights = isset($result->rights) ? $result->rights : [];
        $modules = Module::getModules();
        $moduleKeys = array_flip($modules);
        $rightTypes = Role::getRights();

        if (!empty($rights)) {
            $formatted = [];

            foreach ($rights as $right) {
                $formatted[$moduleKeys[$right->module_name]] = [
                    'right'             => $right->right_id,
                    'limit_to_own_data' => $right->limit_to_own_data,
                    'api'               => $right->api,
                ];
            }

            $rights = $formatted;
        }

        if ($request->has('edit')) {
            $post = $request->get('rights', []);
            $data = [];

            foreach ($post as $key => $record) {
                if (!empty($record['right'])) {
                    $data[] = [
                        'module_name'       => $modules[$key],
                        'right'             => $record['right'],
                        'limit_to_own_data' => isset($record['limit_to_own_data']) ? $record['limit_to_own_data'] : 0,
                        'api'               => isset($record['api']) ? $record['api'] : 0,
                    ];
                }
            }

            $rq = Request::create('/api/modifyRoleRights', 'POST', [
                'api_key'   => Auth::user()->api_key,
                'id'        => $id,
                'data'      => $data,
            ]);
            $api = new ApiRole($rq);
            $result = $api->modifyRoleRights($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.edit_success'));

                return back();
            } else {
                $errors = $result->errors;
                $request->session()->flash('alert-danger', __('custom.edit_error'));
            }

            return back()->withInput()->withErrors($errors);
        }

        return view('admin/roleRights', compact('class', 'rights', 'modules', 'rightTypes'));
    }
}

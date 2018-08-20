<?php

namespace App\Http\Controllers\Admin;

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
    public function list(Request $request) {
        if ($request->has('delete')) {
            if ($this->deleteRole($request->offsetGet('id'))) {
                $request->session()->flash('alert-success', __('custom.delete_success'));
            } else {
                $request->session()->flash('alert-danger', __('custom.delete_error'));
            }

            return back();
        }

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
    public function addRole(Request $request) {
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
     * @return view with inpits
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
     * @return view with inpits
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

}

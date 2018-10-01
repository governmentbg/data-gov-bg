<?php

namespace App\Http\Controllers\Api;

use App\Role;
use App\RoleRight;
use App\Module;
use App\ActionsHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class RoleController extends ApiController
{
    /**
     * API function for adding a new role
     *
     * @param string api_key - required
     * @param array data - required
     * @param string data[name] - required
     * @param boolean data[active] - required
     * @param boolean data[default_user] - optional
     * @param boolean data[default_group_admin] - optional
     * @param boolean data[default_org_admin] - optional
     *
     * @return json with success and role id or error
     */
    public function addRole(Request $request)
    {
        $post = $request->get('data', []);

        $validator = \Validator::make($post, [
            'name'                  => 'required|max:255',
            'active'                => 'required|bool',
            'default_user'          => 'nullable|bool',
            'default_group_admin'   => 'nullable|bool',
            'default_org_admin'     => 'nullable|bool',
            'for_org'               => 'nullable|int',
            'for_group'             => 'nullable|int',
        ]);

        if (isset($post['default_user']) && $post['default_user']) {
            if (Role::where('default_user', 1)->where('active', 1)->first()) {
               return $this->errorResponse(__('custom.default_user_role'));
            }
        }

        if (isset($post['default_group_admin']) && $post['default_group_admin']) {
            if (Role::where('default_group_admin', 1)->where('active', 1)->first()) {
               return $this->errorResponse(__('custom.default_group_admin_role'));
            }
        }

        if (isset($post['default_org_admin']) && $post['default_org_admin']) {
            if (Role::where('default_org_admin', 1)->where('active', 1)->first()) {
               return $this->errorResponse(__('custom.default_org_admin_role'));
            }
        }

        if (!$validator->fails()) {
            $rightCheck = RoleRight::checkUserRight(
                Module::ROLES,
                RoleRight::RIGHT_EDIT
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            try {
                $newRole = Role::create($post);

                $logData = [
                    'module_name'      => Module::getModuleName(Module::ROLES),
                    'action'           => ActionsHistory::TYPE_ADD,
                    'action_object'    => $newRole->id,
                    'action_msg'       => 'Added role',
                ];

                Module::add($logData);

                return $this->successResponse(['id' => $newRole->id], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.add_role_fail'), $validator->errors()->messages());
    }

    /**
     * API function for editing a role
     *
     * @param string api_key - required
     * @param integer id - required
     * @param array data - required
     * @param string data[name] - required
     * @param boolean data[active] - required
     * @param boolean data[default_user] - optional
     * @param boolean data[default_group_admin] - optional
     * @param boolean data[default_org_admin] - optional
     *
     * @return json with success or error
     */
    public function editRole(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'id'    => 'required|int|exists:roles,id',
            'data'  => 'required|array',
        ]);

        if (!$validator->fails()) {
            $validator = \Validator::make($post['data'], [
                'name'                  => 'required|max:255',
                'active'                => 'required|bool',
                'default_user'          => 'nullable|bool',
                'default_group_admin'   => 'nullable|bool',
                'default_org_admin'     => 'nullable|bool',
                'for_org'               => 'nullable|int',
                'for_group'             => 'nullable|int',
            ]);

            if (isset($post['data']['default_user']) && $post['data']['default_user']) {
                if (Role::where('default_user', 1)->where('id', '!=', $post['id'])->first()) {
                    return $this->errorResponse(__('custom.edit_role_fail'));
                }
            }

            if (isset($post['data']['default_group_admin']) && $post['data']['default_group_admin']) {
                if (Role::where('default_group_admin', 1)->where('id', '!=', $post['id'])->first()) {
                    return $this->errorResponse(__('custom.edit_role_fail'));
                }
            }

            if (isset($post['data']['default_org_admin']) && $post['data']['default_org_admin']) {
                if (Role::where('default_org_admin', 1)->where('id', '!=', $post['id'])->first()) {
                    return $this->errorResponse(__('custom.edit_role_fail'));
                }
            }

            if (!$validator->fails()) {
                try {
                    $role = Role::where('id', $post['id'])->first();
                    $rightCheck = RoleRight::checkUserRight(
                        Module::ROLES,
                        RoleRight::RIGHT_EDIT,
                        [],
                        [
                            'created_by' => $role->created_by
                        ]
                    );

                    if (!$rightCheck) {
                        return $this->errorResponse(__('custom.access_denied'));
                    }

                    $role->update($post['data']);

                    if ($role) {
                        $logData = [
                            'module_name'      => Module::getModuleName(Module::ROLES),
                            'action'           => ActionsHistory::TYPE_MOD,
                            'action_object'    => $post['id'],
                            'action_msg'       => 'Edited role',
                        ];

                        Module::add($logData);
                    }

                    return $this->successResponse();
                } catch (QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }

        return $this->errorResponse(__('custom.edit_role_fail'), $validator->errors()->messages());
    }

    /**
     * API function for deleting a role
     *
     * @param string api_key - required
     * @param integer id - required
     *
     * @return json success or error
     */
    public function deleteRole(Request $request)
    {
        $validator = \Validator::make($request->all(), ['id' => 'required|int|exists:roles,id']);

        $id = $request->get('id');

        if (!$validator->fails()) {
            try {
                $role = Role::find($id);
                $rightCheck = RoleRight::checkUserRight(
                    Module::ROLES,
                    RoleRight::RIGHT_ALL,
                    [],
                    [
                        'created_by' => $role->created_by
                    ]
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }

                $role = $role->delete();

                if ($role) {
                    $logData = [
                        'module_name'      => Module::getModuleName(Module::ROLES),
                        'action'           => ActionsHistory::TYPE_DEL,
                        'action_object'    => $id,
                        'action_msg'       => 'Deleted role',
                    ];

                    Module::add($logData);
                }

                return $this->successResponse();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.delete_role_fail'), $validator->errors()->messages());
    }

    /**
     * API function for listing all roles
     *
     * @param string api_key - required
     * @param array criteria - optional
     * @param boolean active - optional | 1 = active 0 = inactive
     * @param boolean data[default_user] - optional
     * @param boolean data[default_group_admin] - optional
     * @param boolean data[default_org_admin] - optional
     *
     * @return json with list of roles or error
     */
    public function listRoles(Request $request)
    {
        $post = $request->get('criteria', []);

        $validator = \Validator::make($post, [
            'role_id'               => 'nullable|int|digits_between:1,10',
            'active'                => 'nullable|bool',
            'org_id'                => 'nullable|int|exists:organisations,id,deleted_at,NULL|digits_between:1,10',
            'default_user'          => 'nullable|bool',
            'default_group_admin'   => 'nullable|bool',
            'default_org_admin'     => 'nullable|bool',
            'for_org'               => 'nullable|int',
            'for_group'             => 'nullable|int',
        ]);

        if (!$validator->fails()) {
            $rightCheck = RoleRight::checkUserRight(
                Module::ROLES,
                RoleRight::RIGHT_VIEW
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            $query = Role::select();

            if (isset($post['role_id'])) {
                $query->where('id', $post['role_id']);
            }

            if (isset($post['active'])) {
                $query->where('active', $post['active']);
            }

            if (isset($post['org_id'])) {
                $query->whereHas('userToOrg', function($q) use ($post) {
                    $q->where('org_id', $post['org_id']);
                });
            }

            if (isset($post['for_org'])) {
                $query->where('for_org', $post['for_org']);
            }

            if (isset($post['for_group'])) {
                $query->where('for_group', $post['for_group']);
            }

            try {
                $roles = $query->get()->toArray();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::ROLES),
                    'action'           => ActionsHistory::TYPE_SEE,
                    'action_msg'       => 'Listed roles',
                ];

                Module::add($logData);

                return $this->successResponse(['roles' => $roles], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.list_role_fail'), $validator->errors()->messages());
    }

    /**
     * API function for listing all rights for a given role
     *
     * @param string api_key - required
     * @param integer id - required
     *
     * @return json with list of role's rights or error
     */
    public function getRoleRights(Request $request)
    {
        $validator = \Validator::make($request->all(), ['id' => 'required|int|exists:roles,id|digits_between:1,10']);

        if (!$validator->fails()) {
            $rightCheck = RoleRight::checkUserRight(
                Module::ROLES,
                RoleRight::RIGHT_VIEW
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            $id = $request->get('id');

            try {
                $role = Role::find($id);
                $rights = RoleRight::getRightsDescription();
                $result = [];

                foreach ($role->rights as $right) {
                    $right['right_id'] = $right->right;
                    $right['right'] = $rights[$right['right_id']];

                    $result[] = $right->toArray();
                }

                $logData = [
                    'module_name'      => Module::getModuleName(Module::ROLES),
                    'action'           => ActionsHistory::TYPE_SEE,
                    'action_object'    => $id,
                    'action_msg'       => 'Got role rights',
                ];

                Module::add($logData);

                return $this->successResponse(['rights' => $result], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.get_role_right_fail'), $validator->errors()->messages());
    }

    /**
     * API function for changing a role's rights
    *
     * @param string api_key - required
     * @param integer id - required
     * @param array data - required | array with rights
     * @param array data[right] - required | array with right data
     * @param string data[right][module_name] - required
     * @param integer data[right][right_id] - required
     * @param boolean data[right][limit_to_own_data] - required
     * @param boolean data[right][api] - required
     *
     * @return json $response - response with status
     */
    public function modifyRoleRights(Request $request)
    {
        $post = $request->all();
        $modules = Module::getModules();
        $rights = RoleRight::getRights();

        $validator = \Validator::make($post, [
            'id'                        => 'required|int|exists:roles,id|digits_between:1,10',
            'data.*.module_name'        => 'required|string|max:191|in:'. implode(',', $modules),
            'data.*.right'              => 'required|int|digits_between:1,10|in:'. implode(',', array_flip($rights)),
            'data.*.limit_to_own_data'  => 'required|bool',
            'data.*.api'                => 'required|bool',
        ]);

        if (!$validator->fails()) {
            $rightCheck = RoleRight::checkUserRight(
                Module::ROLES,
                RoleRight::RIGHT_EDIT
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            $role = Role::where('id', $post['id'])->first();
            $rights = $role->rights;
            $names = $request->input('data.*.module_name');
            $success = true;

            DB::beginTransaction();

            try {
                foreach ($rights as $right) {
                    if (!in_array($right->module_name, $names)) {
                        RoleRight::find($right->id)->delete();
                    }
                }
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());

                $success = false;
            }

            try {
                foreach ($post['data'] as $module) {
                    $newRight = RoleRight::updateOrCreate([
                        'role_id'       => $post['id'],
                        'module_name'   => $module['module_name']
                    ], $module);
                }
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());

                $success = false;
            }

            if ($success) {
                DB::commit();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::ROLES),
                    'action'           => ActionsHistory::TYPE_MOD,
                    'action_object'    => $post['id'],
                    'action_msg'       => 'Modified role rights',
                ];

                Module::add($logData);
                return $this->successResponse();
            }

            DB::rollback();
        }

        return $this->errorResponse(__('custom.modify_role_right_fail'), $validator->errors()->messages());
    }
}

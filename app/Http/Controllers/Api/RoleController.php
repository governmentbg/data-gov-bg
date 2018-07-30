<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Log;
use App\Role;
use App\RoleRight;

class RoleController extends ApiController
{
    /**
     * API function for adding a new role
     *
     * @param string api_key - required
     * @param array data - required
     * @param string data[name] - required
     * @param boolean data[active] - required
     *
     * @return json with success and role id or error
     */
    public function addRole(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'name'      => 'required|max:255',
            'active'    => 'required|boolean',
        ]);

        if (!$validator->fails()) {
            try {
                $newRole = Role::create($post);

                if ($newRole) {
                    return $this->successResponse(['id' => $newRole->id], true);
                }
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Add role failure', $validator->errors()->messages());
    }

    /**
     * API function for editing a role
     *
     * @param string api_key - required
     * @param integer id - required
     * @param array data - required
     * @param string data[name] - required
     * @param boolean data[active] - required
     *
     * @return json with success or error
     */
    public function editRole(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'id'        => 'required|max:255',
            'name'      => 'required|max:255',
            'active'    => 'required|boolean',
        ]);

        if (
            !$validator->fails()
            && Role::where('id', $post['id'])->get()->count()
        ) {
            try {
                if (Role::where('id', $post['id'])->update($post)) {
                    return $this->successResponse();
                }
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Edit role failure', $validator->errors()->messages());
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
        $validator = \Validator::make($request->all(), ['id' => 'required|int']);

        $id = $request->offsetGet('id');

        if (!$validator->fails()) {
            try {
                if (Role::find($id)->delete()) {
                    return $this->successResponse();
                }
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Delete role failure', $validator->errors()->messages());
    }

    /**
     * API function for listing all roles
     *
     * @param string api_key - required
     * @param array criteria - optional
     * @param boolean active - optional | 1 = active 0 = inactive
     *
     * @return json with list of roles or error
     */
    public function listRoles(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['criteria.active' => 'nullable|boolean']);

        if (!$validator->fails()) {
            $query = Role::select();

            if (isset($post['criteria']['active'])) {
                $query->where('active', $post['criteria']['active']);
            }

            try {
                $roles = $query->get();

                return $this->successResponse(['roles' => $roles], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Get role data failure', $validator->errors()->messages());
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
        $validator = \Validator::make($request->all(), ['id' => 'required|int']);

        if (!$validator->fails()) {
            $id = $request->get('id');

            try {
                $role = Role::find($id);
                $rights = RoleRight::getRightsDescription();

                if ($role) {
                    foreach ($role->rights as $right) {
                        $right['right_id'] = $right->right;
                        $right['right'] = $rights[$right->right];
                    }

                    return $this->successResponse(['rights' => $role->rights], true);
                }
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Get role rights failure', $validator->errors()->messages());
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
        $id = $request->get('id');

        $validator = \Validator::make($post, [
            'data.*.module_name'       => 'required|string|max:255',
            'data.*.right'             => 'required|integer',
            'data.*.limit_to_own_data' => 'required|boolean',
            'data.*.api'               => 'required|boolean',
        ]);

        if (
            empty($id)
            || !Role::where('id', $id)->get()->count()
            || $validator->fails()
        ) {
            $response = $this->errorResponse('No role found', $validator->errors()->messages());
        } else {
            $role = Role::where('id', $id)->first();
            $rights = $role->rights;
            $names = $request->input('data.*.module_name');

            try {
                foreach ($rights as $right) {
                    if (!in_array($right->module_name, $names)) {
                        RoleRight::find($right->id)->delete();
                    }
                }
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
                return $this->errorResponse();
            }

            try {
                foreach ($post['data'] as $module) {
                    $newRight = RoleRight::updateOrCreate(
                        ['role_id' => $id, 'module_name' => $module['module_name']],
                        $module
                    );

                    $response = $newRight ? $this->successResponse() : $this->errorResponse('No role found');

                    if (empty($newRight)) {
                        break;
                    }
                }
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $response;
    }
}

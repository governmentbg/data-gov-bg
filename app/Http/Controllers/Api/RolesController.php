<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiController;
use App\User;
use App\Role;
use App\RoleRight;

class RolesController extends ApiController
{
    // ROLES - initial roles CRUD and lists 

    // Route::post('roles/addRole', 'ApiTemplateController@addRole');
    public function addRole(Request $request)
    {
        $validator = \Validator::make($request->all(),[
                'name'      => 'required|max:255',
                'active'    => 'required|boolean',
            ]
        );

        if ($validator->fails()) {
            $response = new JsonResponse([
                'success'   => false,
                'status'    => 500,
                'error'     => [
                    'type'     => 'General',
                    'message'  => 'Add role failure'
                ],
            ], 500);
        } else {
            $data = $request->all();
            unset($data['api_key']);
            
            $data['created_by'] = \Auth::user()->id;
    
            if ($newRole = Role::create($data)) {
                $response = new JsonResponse([
                    'success' => true,
                    'id' => $newRole->id,
                ], 200);
            }
        }
        
        return $response;
    }

    // Route::post('roles/editRole/{id}', 'ApiTemplateController@editRole');
    public function editRole(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'name'      => 'required|max:255',
            'active'    => 'required|boolean',
        ]);

        if (
            empty($id)
            || $validator->fails()
            || !Role::where('id', $id)->get()->count()
        ) {
            $response = new JsonResponse([
                'success'   => false,
                'status'    => 500,
                'error'     => [
                    'type'     => 'General',
                    'message'  => 'Edit role failure'
                ],
            ], 500);
        } else {
            $data = $request->all();
            unset($data['api_key']);
            $data['updated_by'] = \Auth::user()->id;

            if (Role::where('id', $id)->update($data)) {
                $response = new JsonResponse([
                   'success' => true,
                ], 200);
            }
        }

        return $response;
    }

    // Route::get('roles/deleteRole/{id}', 'ApiTemplateController@deleteRole');
    public function deleteRole(Request $request, $id)
    {
        if (empty($id) || !Role::where('id', $id)->get()->count()) {
            $response = new JsonResponse([
                'success'   => false,
                'status'    => 500,
                'error'     => [
                    'type'     => 'General',
                    'message'  => 'Delete role failure'
                ],
            ], 500);
        } else {
            Role::find($id)->delete();

            $response = new JsonResponse([
                'success' => true,
            ], 200);
        }

        return $response;
    }

    // Route::post('roles/listRoles', 'ApiTemplateController@listRoles');
    public function listRoles(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            $response = new JsonResponse([
                'success'    => false,
                'status'     => 500,
                'error'      => [
                    'type'     => 'General error',
                    'message'  => 'Get role data failure',
                ],
            ], 500);
        } else {
            if ($request->has('active')) {
                $roles = Role::where('active', $request->active)->get();
            } else {
                $roles = Role::all();
            }
    
            $response = new JsonResponse([
                'success'   => true,
                'data'      => $roles,
            ], 200);
        }

        return $response;
    }

    // Route::get('roles/getRoleRights/{id}', 'ApiTemplateController@getRoleRights');
    public function getRoleRights(Request $request, $id)
    {
        if (empty($id) || !Role::where('id', $id)->get()->count()) {
            $response = new JsonResponse([
                'success'    => false,
                'status'     => 500,
                'error'      => [
                    'type'     => 'General',
                    'message'  => 'Get role rights failure',
                ],
            ], 500);
        } else {
            $role = Role::where('id', $id)->first();
            $rights = $role->rights;

            $response = new JsonResponse([
                'success'   => true,
                'data'      => $rights,
            ], 200);
        }
        
        return $response;
    }

    // Route::post('roles/modifyRoleRights/{id}', 'ApiTemplateController@modifyRoleRights');
    public function modifyRoleRights(Request $request, $id)
    {
        $errorResponse = new JsonResponse([
            'success'    => false,
            'status'     => 500,
            'error'      => [
                'type'     => 'General',
                'message'  => 'No role found.',
            ],
        ], 404);

        $successResponse = new JsonResponse([
            'success' => true,
        ], 200);

        $validator = \Validator::make($request->all(), [
            'data.*.module_name'       => 'required|max:255',
            'data.*.right'             => 'required',
            'data.*.limit_to_own_data' => 'required|boolean',
            'data.*.api'               => 'required|boolean',
        ]);

        $data = $request->all();
        unset($data['api_key']);

        if (
            empty($id)
            || !Role::where('id', $id)->get()->count()
            || $validator->fails()
        ) {
            $response = $errorResponse;
        } else {
            $role = Role::where('id', $id)->first();
            $rights = $role->rights;
            $names = $request->input('data.*.module_name');

            foreach ($rights as $right) {
                if (!in_array($right->module_name, $names)) {
                    RoleRight::find($right->id)->delete();
                }
            }

            foreach ($data['data'] as $module) {
                $module['created_by'] = \Auth::user()->id;
                $module['updated_by'] = \Auth::user()->id;

                if ($newRight = RoleRight::updateOrCreate(['role_id' => $id, 'module_name' => $module['module_name']], $module)) {
                    $response = $successResponse;
                } else {
                    $response = $errorResponse;
                }
            }
        }
        
        return $response;
    }
    // ROLES END
}

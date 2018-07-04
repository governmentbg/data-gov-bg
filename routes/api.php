<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware([/*'auth',*/ 'auth.api', /*'throttle:60,1'*/])->group(function () {
    Route::post('roles/addRole', 'Api\RoleController@addRole');
    Route::post('roles/editRole/{id}', 'Api\RoleController@editRole');
    Route::post('roles/deleteRole/{id}', 'Api\RoleController@deleteRole');
    Route::post('roles/listRoles', 'Api\RoleController@listRoles');
    Route::post('roles/getRoleRights/{id}', 'Api\RoleController@getRoleRights');
    Route::post('roles/modifyRoleRights/{id}', 'Api\RoleController@modifyRoleRights');
    Route::post('rights/listRights', 'Api\RightController@listRights');
 });

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
    Route::post('roles/addRole', 'Api\RolesController@addRole');
    Route::post('roles/editRole/{id}', 'Api\RolesController@editRole');
    Route::get('roles/deleteRole/{id}', 'Api\RolesController@deleteRole');
    Route::post('roles/listRoles', 'Api\RolesController@listRoles');
    Route::get('roles/getRoleRights/{id}', 'Api\RolesController@getRoleRights');
    Route::post('roles/modifyRoleRights/{id}', 'Api\RolesController@modifyRoleRights');
 });

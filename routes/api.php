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




Route::middleware(['auth.api', /*'throttle:60,1'*/])->group(function () {
    Route::post('/listUsers', 'Api\UserController@listUsers');
    Route::post('/searchUsers', 'Api\UserController@searchUsers');
    Route::post('/getUserRoles', 'Api\UserController@getUserRoles');
    Route::post('/getUserSettings', 'Api\UserController@getUserSettings');
    Route::post('/addUser', 'Api\UserController@addUser');
    Route::post('/editUser', 'Api\UserController@editUser');
    Route::post('/deleteUser', 'Api\UserController@deleteUser');
    Route::post('/generateAPIKey', 'Api\UserController@generateAPIKey');
    Route::post('/inviteUser', 'Api\UserController@inviteUser');

    Route::post('/addOrganisation', 'Api\OrganisationController@addOrganisation');
    Route::post('/editOrganisation', 'Api\OrganisationController@editOrganisation');
    Route::post('/listOrganisations', 'Api\OrganisationController@listOrganisations');
    Route::post('/searchOrganisations', 'Api\OrganisationController@searchOrganisations');
    Route::post('/deleteOrganisation', 'Api\OrganisationController@deleteOrganisation');

    Route::post('roles/addRole', 'Api\RoleController@addRole');
    Route::post('roles/editRole', 'Api\RoleController@editRole');
    Route::post('roles/deleteRole', 'Api\RoleController@deleteRole');
    Route::post('roles/listRoles', 'Api\RoleController@listRoles');

    Route::post('roles/getRoleRights', 'Api\RoleController@getRoleRights');
    Route::post('roles/modifyRoleRights', 'Api\RoleController@modifyRoleRights');
    Route::post('rights/listRights', 'Api\RightController@listRights');

    Route::post('addSection', 'Api\SectionController@addSection');
    Route::post('editSection', 'Api\SectionController@editSection');
    Route::post('deleteSection', 'Api\SectionController@deleteSection');
    Route::post('listSections', 'Api\SectionController@listSections');
    Route::post('listSubsections', 'Api\SectionController@listSubsections');

    Route::post('listThemes', 'Api\ThemeController@listThemes');
});

Route::post('/register', 'Api\UserController@register');

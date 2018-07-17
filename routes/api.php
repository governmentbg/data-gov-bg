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

    Route::post('/addRole', 'Api\RoleController@addRole');
    Route::post('/editRole', 'Api\RoleController@editRole');
    Route::post('/deleteRole', 'Api\RoleController@deleteRole');
    Route::post('/listRoles', 'Api\RoleController@listRoles');
    Route::post('/getRoleRights', 'Api\RoleController@getRoleRights');
    Route::post('/modifyRoleRights', 'Api\RoleController@modifyRoleRights');

    Route::post('rights/listRights', 'Api\RightController@listRights');

    Route::post('/addSection', 'Api\SectionController@addSection');
    Route::post('/editSection', 'Api\SectionController@editSection');
    Route::post('/deleteSection', 'Api\SectionController@deleteSection');
    Route::post('/listSections', 'Api\SectionController@listSections');
    Route::post('/listSubsections', 'Api\SectionController@listSubsections');

    Route::post('/listThemes', 'Api\ThemeController@listThemes');

    Route::post('/addDataSet', 'Api\DataSetController@addDataSet');
    Route::post('/editDataSet', 'Api\DataSetController@editDataSet');
    Route::post('/deleteDataSet', 'Api\DataSetController@deleteDataSet');
    Route::post('/getDataSetDetails', 'Api\DataSetController@getDataSetDetails');
    Route::post('/addDataSetToGroup', 'Api\DataSetController@addDataSetToGroup');
    Route::post('/removeDataSetFromGroup', 'Api\DataSetController@removeDataSetFromGroup');
});

Route::post('/register', 'Api\UserController@register');

Route::post('/listDataSets', 'Api\DataSetController@listDataSets');
Route::post('/searchDataSet', 'Api\DataSetController@searchDataSet');

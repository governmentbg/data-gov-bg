<?php

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

Route::middleware(['auth.api' /*'throttle:60,1'*/])->group(function () {
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
    Route::post('/deleteOrganisation', 'Api\OrganisationController@deleteOrganisation');

    Route::post('/addGroup', 'Api\OrganisationController@addGroup');
    Route::post('/editGroup', 'Api\OrganisationController@editGroup');
    Route::post('/deleteGroup', 'Api\OrganisationController@deleteGroup');

    Route::post('/addRole', 'Api\RoleController@addRole');
    Route::post('/editRole', 'Api\RoleController@editRole');
    Route::post('/deleteRole', 'Api\RoleController@deleteRole');
    Route::post('/listRoles', 'Api\RoleController@listRoles');
    Route::post('/getRoleRights', 'Api\RoleController@getRoleRights');
    Route::post('/modifyRoleRights', 'Api\RoleController@modifyRoleRights');

    Route::post('/listActionHistory', 'Api\ActionsHistoryController@listActionHistory');

    Route::post('/listRights', 'Api\RightController@listRights');

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

    Route::post('/addTermsOfUse', 'Api\TermsOfUseController@addTermsOfUse');
    Route::post('/editTermsOfUse', 'Api\TermsOfUseController@editTermsOfUse');
    Route::post('/deleteTermsOfUse', 'Api\TermsOfUseController@deleteTermsOfUse');
    Route::post('/listTermsOfUse', 'Api\TermsOfUseController@listTermsOfUse');

    Route::post('/sendTermsOfUseRequest', 'Api\TermsOfUseRequestController@sendTermsOfUseRequest');
    Route::post('/editTermsOfUseRequest', 'Api\TermsOfUseRequestController@editTermsOfUseRequest');
    Route::post('/deleteTermsOfUseRequest', 'Api\TermsOfUseRequestController@deleteTermsOfUseRequest');
    Route::post('/listTermsOfUseRequests', 'Api\TermsOfUseRequestController@listTermsOfUseRequests');

    Route::post('/addNews', 'Api\NewsController@addNews');
    Route::post('/editNews', 'Api\NewsController@editNews');
    Route::post('/deleteNews', 'Api\NewsController@deleteNews');
    Route::post('/getNewsDetails', 'Api\NewsController@getNewsDetails');

    Route::post('/addResourceMetadata', 'Api\ResourceController@addResourceMetadata');
    Route::post('/addResourceData', 'Api\ResourceController@addResourceData');
    Route::post('/editResourceMetadata', 'Api\ResourceController@editResourceMetadata');
    Route::post('/updateResourceData', 'Api\ResourceController@updateResourceData');
    Route::post('/deleteResource', 'Api\ResourceController@deleteResource');

    Route::post('/addMainCategory', 'Api\CategoryController@addMainCategory');
    Route::post('/editMainCategory', 'Api\CategoryController@editMainCategory');
    Route::post('/deleteMainCategory', 'Api\CategoryController@deleteMainCategory');
    Route::post('/addTag', 'Api\CategoryController@addTag');
    Route::post('/editTag', 'Api\CategoryController@editTag');
    Route::post('/deleteTag', 'Api\CategoryController@deleteTag');
});

Route::post('/register', 'Api\UserController@register');

Route::post('/searchNews', 'Api\NewsController@searchNews');
Route::post('/listNews', 'Api\NewsController@listNews');

Route::post('/listDataSets', 'Api\DataSetController@listDataSets');
Route::post('/searchDataSet', 'Api\DataSetController@searchDataSet');

Route::post('/listResources', 'Api\ResourceController@listResources');
Route::post('/getResourceMetadata', 'Api\ResourceController@getResourceMetadata');
Route::post('/getResourceSchema', 'Api\ResourceController@getResourceSchema');
Route::post('/getResourceData', 'Api\ResourceController@getResourceData');
Route::post('/getResourceView', 'Api\ResourceController@getResourceView');
Route::post('/searchResourceData', 'Api\ResourceController@searchResourceData');
Route::post('/getLinkedData', 'Api\ResourceController@getLinkedData');

Route::post('/listMainCategories', 'Api\CategoryController@listMainCategories');
Route::post('/getMainCategoryDetails', 'Api\CategoryController@getMainCategoryDetails');
Route::post('/listTags', 'Api\CategoryController@listTags');
Route::post('/getTagDetails', 'Api\CategoryController@getTagDetails');

Route::post('/listOrganisations', 'Api\OrganisationController@listOrganisations');
Route::post('/searchOrganisations', 'Api\OrganisationController@searchOrganisations');
Route::post('/getOrganisationDetails', 'Api\OrganisationController@getOrganisationDetails');

Route::post('/listGroups', 'Api\OrganisationController@listGroups');
Route::post('/searchGroups', 'Api\OrganisationController@searchGroups');
Route::post('/getGroupDetails', 'Api\OrganisationController@getGroupDetails');
Route::post('/getTermsOfUseDetails', 'Api\TermsOfUseController@getTermsOfUseDetails');

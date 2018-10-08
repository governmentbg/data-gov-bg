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
    Route::post('getUserRoles', 'Api\UserController@getUserRoles');
    Route::post('getUserSettings', 'Api\UserController@getUserSettings');
    Route::post('addUser', 'Api\UserController@addUser');
    Route::post('editUser', 'Api\UserController@editUser');
    Route::post('deleteUser', 'Api\UserController@deleteUser');
    Route::post('generateAPIKey', 'Api\UserController@generateAPIKey');
    Route::post('inviteUser', 'Api\UserController@inviteUser');

    Route::post('addOrganisation', 'Api\OrganisationController@addOrganisation');
    Route::post('addUserToOrg', 'Api\OrganisationController@addUserToOrg');
    Route::post('editOrganisation', 'Api\OrganisationController@editOrganisation');
    Route::post('getUserOrganisations', 'Api\OrganisationController@getUserOrganisations');
    Route::post('deleteOrganisation', 'Api\OrganisationController@deleteOrganisation');
    Route::post('delMember', 'Api\OrganisationController@delMember');
    Route::post('editMember', 'Api\OrganisationController@editMember');

    Route::post('addGroup', 'Api\OrganisationController@addGroup');
    Route::post('editGroup', 'Api\OrganisationController@editGroup');
    Route::post('deleteGroup', 'Api\OrganisationController@deleteGroup');

    Route::post('addRole', 'Api\RoleController@addRole');
    Route::post('editRole', 'Api\RoleController@editRole');
    Route::post('deleteRole', 'Api\RoleController@deleteRole');
    Route::post('listRoles', 'Api\RoleController@listRoles');
    Route::post('getRoleRights', 'Api\RoleController@getRoleRights');
    Route::post('modifyRoleRights', 'Api\RoleController@modifyRoleRights');

    Route::post('rights/listRights', 'Api\RightController@listRights');
    Route::post('addPage', 'Api\PageController@addPage');
    Route::post('editPage', 'Api\PageController@editPage');
    Route::post('deletePage', 'Api\PageController@deletePage');
    Route::post('listModules', 'Api\ModulesController@listModules');
    Route::post('addActionHistory', 'Api\ActionsHistoryController@addActionHistory');

    Route::post('listRights', 'Api\RightController@listRights');

    Route::post('addSection', 'Api\SectionController@addSection');
    Route::post('editSection', 'Api\SectionController@editSection');
    Route::post('deleteSection', 'Api\SectionController@deleteSection');
    Route::post('isParent', 'Api\SectionController@isParent');

    Route::post('listThemes', 'Api\ThemeController@listThemes');

    Route::post('addDataset', 'Api\DataSetController@addDataset');
    Route::post('editDataset', 'Api\DataSetController@editDataset');
    Route::post('deleteDataset', 'Api\DataSetController@deleteDataset');
    Route::post('addDatasetToGroup', 'Api\DataSetController@addDatasetToGroup');
    Route::post('removeDatasetFromGroup', 'Api\DataSetController@removeDatasetFromGroup');
    Route::post('getUsersDataSetCount', 'Api\DataSetController@getUsersDataSetCount');

    Route::post('addTag', 'Api\TagController@addTag');
    Route::post('editTag', 'Api\TagController@editTag');
    Route::post('deleteTag', 'Api\TagController@deleteTag');

    Route::post('sendDataRequest', 'Api\DataRequestController@sendDataRequest');
    Route::post('editDataRequest', 'Api\DataRequestController@editDataRequest');
    Route::post('deleteDataRequest', 'Api\DataRequestController@deleteDataRequest');
    Route::post('listDataRequests', 'Api\DataRequestController@listDataRequests');

    Route::post('addDocument', 'Api\DocumentController@addDocument');
    Route::post('editDocument', 'Api\DocumentController@editDocument');
    Route::post('deleteDocument', 'Api\DocumentController@deleteDocument');
    Route::post('listDocuments', 'Api\DocumentController@listDocuments');
    Route::post('appendDocument', 'Api\DocumentController@appendDocumentData');

    Route::post('roles/getRoleRights', 'Api\RoleController@getRoleRights');
    Route::post('roles/modifyRoleRights', 'Api\RoleController@modifyRoleRights');
    Route::post('rights/listRights', 'Api\RightController@listRights');

    Route::post('editSignal', 'Api\SignalController@editSignal');
    Route::post('deleteSignal', 'Api\SignalController@deleteSignal');
    Route::post('listSignals', 'Api\SignalController@listSignals');

    Route::post('addTermsOfUse', 'Api\TermsOfUseController@addTermsOfUse');
    Route::post('editTermsOfUse', 'Api\TermsOfUseController@editTermsOfUse');
    Route::post('deleteTermsOfUse', 'Api\TermsOfUseController@deleteTermsOfUse');
    Route::post('listTermsOfUse', 'Api\TermsOfUseController@listTermsOfUse');

    Route::post('addLocale', 'Api\LocaleController@addLocale');
    Route::post('editLocale', 'Api\LocaleController@editLocale');
    Route::post('deleteLocale', 'Api\LocaleController@deleteLocale');

    Route::post('sendTermsOfUseRequest', 'Api\TermsOfUseRequestController@sendTermsOfUseRequest');
    Route::post('editTermsOfUseRequest', 'Api\TermsOfUseRequestController@editTermsOfUseRequest');
    Route::post('deleteTermsOfUseRequest', 'Api\TermsOfUseRequestController@deleteTermsOfUseRequest');
    Route::post('listTermsOfUseRequests', 'Api\TermsOfUseRequestController@listTermsOfUseRequests');

    Route::post('addNews', 'Api\NewsController@addNews');
    Route::post('editNews', 'Api\NewsController@editNews');
    Route::post('deleteNews', 'Api\NewsController@deleteNews');
    Route::post('getNewsDetails', 'Api\NewsController@getNewsDetails');

    Route::post('addResourceMetadata', 'Api\ResourceController@addResourceMetadata');
    Route::post('addResourceData', 'Api\ResourceController@addResourceData');
    Route::post('editResourceMetadata', 'Api\ResourceController@editResourceMetadata');
    Route::post('updateResourceData', 'Api\ResourceController@updateResourceData');
    Route::post('deleteResource', 'Api\ResourceController@deleteResource');
    Route::post('hasReportedResource', 'Api\ResourceController@hasReportedResource');
    Route::post('listDataFormats', 'Api\ResourceController@listDataFormats');

    Route::post('addMainCategory', 'Api\CategoryController@addMainCategory');
    Route::post('editMainCategory', 'Api\CategoryController@editMainCategory');
    Route::post('deleteMainCategory', 'Api\CategoryController@deleteMainCategory');

    Route::post('deleteCustomSetting', 'Api\CustomSettingsController@delete');

    Route::post('addFollow', 'Api\UserFollowController@addFollow');
    Route::post('unFollow', 'Api\UserFollowController@unFollow');
    Route::post('getFollowersCount', 'Api\UserFollowController@getFollowersCount');

    Route::post('addMember', 'Api\OrganisationController@addMember');

    Route::post('addImage', 'Api\ImageController@addImage');
    Route::post('getImageDetails', 'Api\ImageController@getImageDetails');
    Route::post('listImages', 'Api\ImageController@listImages');
    Route::post('editImage', 'Api\ImageController@editImage');
    Route::post('deleteImage', 'Api\ImageController@deleteImage');

    Route::post('xml2json', 'Api\ConversionController@xml2json');
    Route::post('json2xml', 'Api\ConversionController@json2xml');
    Route::post('csv2json', 'Api\ConversionController@csv2json');
    Route::post('json2csv', 'Api\ConversionController@json2csv');
    Route::post('kml2json', 'Api\ConversionController@kml2json');
    Route::post('json2kml', 'Api\ConversionController@json2kml');
    Route::post('rdf2json', 'Api\ConversionController@rdf2json');
    Route::post('json2rdf', 'Api\ConversionController@json2rdf');
    Route::post('pdf2json', 'Api\ConversionController@pdf2json');
    Route::post('img2json', 'Api\ConversionController@img2json');
    Route::post('doc2json', 'Api\ConversionController@doc2json');
    Route::post('xls2json', 'Api\ConversionController@xls2json');
    Route::post('toJSON', 'Api\ConversionController@toJSON');
    Route::post('toXML', 'Api\ConversionController@toXML');
    Route::post('toCSV', 'Api\ConversionController@toCSV');
    Route::post('toKML', 'Api\ConversionController@toKML');
    Route::post('toRDF', 'Api\ConversionController@toRDF');

    Route::post('addHelpSection', 'Api\HelpController@addHelpSection');
    Route::post('editHelpSection', 'Api\HelpController@editHelpSection');
    Route::post('deleteHelpSection', 'Api\HelpController@deleteHelpSection');
    Route::post('isSectionParent', 'Api\HelpController@isParent');

    Route::post('addHelpPage', 'Api\HelpController@addHelpPage');
    Route::post('editHelpPage', 'Api\HelpController@editHelpPage');
    Route::post('deleteHelpPage', 'Api\HelpController@deleteHelpPage');
});

Route::post('listHelpSections', 'Api\HelpController@listHelpSections');
Route::post('listHelpSubsections', 'Api\HelpController@listHelpSubsections');

Route::post('listHelpPages', 'Api\HelpController@listHelpPages');
Route::post('getHelpPageDetails', 'Api\HelpController@getHelpPageDetails');

Route::post('sendSignal', 'Api\SignalController@sendSignal');

Route::post('register', 'Api\UserController@register');
Route::post('listDataUsers', 'Api\UserController@listDataUsers');
Route::post('listUsers', 'Api\UserController@listUsers');

Route::post('searchNews', 'Api\NewsController@searchNews');
Route::post('listNews', 'Api\NewsController@listNews');

Route::post('listDatasets', 'Api\DataSetController@listDatasets');
Route::post('getDatasetDetails', 'Api\DataSetController@getDatasetDetails');

Route::post('listTags', 'Api\TagController@listTags');
Route::post('getTagDetails', 'Api\TagController@getTagDetails');
Route::post('searchTag', 'Api\TagController@searchTag');
Route::post('listDataTags', 'Api\TagController@listDataTags');

Route::post('listActionHistory', 'Api\ActionsHistoryController@listActionHistory');
Route::post('listActionTypes', 'Api\ActionsHistoryController@listActionTypes');
Route::post('userCount', 'Api\UserController@userCount');

Route::post('listResources', 'Api\ResourceController@listResources');
Route::post('getResourceMetadata', 'Api\ResourceController@getResourceMetadata');
Route::post('getResourceSchema', 'Api\ResourceController@getResourceSchema');
Route::post('getResourceData', 'Api\ResourceController@getResourceData');
Route::post('getResourceView', 'Api\ResourceController@getResourceView');
Route::post('searchResourceData', 'Api\ResourceController@searchResourceData');
Route::post('getLinkedData', 'Api\ResourceController@getLinkedData');
Route::post('listDataCategories', 'Api\CategoryController@listDataCategories');

Route::post('listMainCategories', 'Api\CategoryController@listMainCategories');
Route::post('getMainCategoryDetails', 'Api\CategoryController@getMainCategoryDetails');

Route::post('listOrganisations', 'Api\OrganisationController@listOrganisations');
Route::post('getOrganisationDetails', 'Api\OrganisationController@getOrganisationDetails');
Route::post('getMembers', 'Api\OrganisationController@getMembers');
Route::post('listDataGroups', 'Api\OrganisationController@listDataGroups');
Route::post('listOrganisationTypes', 'Api\OrganisationController@listOrganisationTypes');

Route::post('listGroups', 'Api\OrganisationController@listGroups');
Route::post('listDataOrganisations', 'Api\OrganisationController@listDataOrganisations');
Route::post('getGroupDetails', 'Api\OrganisationController@getGroupDetails');
Route::post('getMostActiveOrganisation', 'Api\OrganisationController@getMostActiveOrganisation');

Route::post('getTermsOfUseDetails', 'Api\TermsOfUseController@getTermsOfUseDetails');
Route::post('listDataTermsOfUse', 'Api\TermsOfUseController@listDataTermsOfUse');

Route::post('listSections', 'Api\SectionController@listSections');
Route::post('listSubsections', 'Api\SectionController@listSubsections');

Route::post('listPages', 'Api\PageController@listPages');

Route::post('listLocale', 'Api\LocaleController@listLocale');
Route::post('getLocaleDetails', 'Api\LocaleController@getLocaleDetails');

Route::post('forgottenPassword', 'Api\UserController@forgottenPassword');
Route::post('passwordReset', 'Api\UserController@passwordReset');

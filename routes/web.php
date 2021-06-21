<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('auth')->group(function() {
    Route::post('/user/organisations/register', 'UserController@registerOrg');
    Route::get('/user/organisations/register', 'UserController@showOrgRegisterForm');

    Route::post('/user/changePassword', 'UserController@changePassword');

    Route::post('/admin/adminChangePassword', 'Admin\UserController@adminChangePassword');

    Route::middleware('check.resources')->group(function () {
        Route::middleware('admin')->group(function () {
            Route::match(['get', 'post'], 'admin/organisations/deletedDatasets/{uri}', 'Admin\OrganisationController@listDeletedDatasets')->name('adminOrgDatasetsDeleted');
            Route::match(['get', 'post'], 'admin/groups/deletedDatasets/{uri}', 'Admin\OrganisationController@listDeletedDatasets')->name('adminGroupDatasetsDeleted');
            Route::match(['get', 'post'], 'admin/organisations/{orgUri}/viewDeletedDataset/{uri}', 'Admin\OrganisationController@viewDeletedDataset');
            Route::match(['get', 'post'], 'admin/groups/{orgUri}/viewDeletedDataset/{uri}', 'Admin\OrganisationController@viewDeletedDataset');
            Route::match(['get', 'post'], 'admin/organisations/hardDeleteDataset', 'Admin\OrganisationController@hardDeleteDataset');
            Route::match(['get', 'post'], 'admin/organisations/dataset/create/{uri}', 'UserController@orgDatasetCreate');
            Route::match(['get', 'post'], 'admin/groups/dataset/create/{uri}', 'UserController@groupDatasetCreate');
            Route::match(['get', 'post'], 'admin/groups/datasets/{uri}', 'UserController@groupDatasets');
            Route::match(['get', 'post'], 'admin/groups/{grpUri}/dataset/edit/{uri}', 'UserController@groupDatasetEdit');
            Route::match(['get', 'post'], 'admin/groups/{grpUri}/dataset/{uri}', 'UserController@groupDatasetView')->name('adminGroupDatasetView');
            Route::match(['get', 'post'], 'admin/organisations/{orguri}/resource/{uri}/{version?}', 'UserController@orgResourceView')->name('adminOrgResourceView');
            Route::match(['get', 'post'], 'admin/organisations/dataset/view/{uri}', 'UserController@orgDatasetView')->name('adminOrgDatasetView');
            Route::match(['post'], 'admin/organisations/dataset/move/', 'UserController@adminOrgDatasetMove')->name('adminOrgDatasetMove');
            Route::match(['get', 'post'], 'admin/organisations/{orgUri}/dataset/edit/{uri}', 'UserController@orgDatasetEdit');
            Route::match(
                ['get', 'post'],
                '/admin/groups/{grpUri}/dataset/resource/create/{uri}',
                'UserController@groupResourceCreate'
            )->name('adminGroupResourceCreate');
            Route::match(
                ['get', 'post'],
                '/admin/organisations/{orguri}/dataset/resource/create/{uri}',
                'UserController@orgResourceCreate'
            )->name('adminOrgResourceCreate');
            Route::match(['get', 'post'], 'admin/groups/{grpUri}/resource/{uri}/{version?}', 'UserController@groupResourceView');
            Route::match(['get', 'post'], 'admin/organisations/{orguri}/resource/{uri}/{version?}', 'UserController@orgResourceView')->name('adminOrgResourceView');
        });
        Route::match(['get', 'post'], '/admin/organisations', 'Admin\OrganisationController@list')->name('adminOrgs');
        Route::match(['get', 'post'], '/admin/organisations/search', 'Admin\OrganisationController@search');
        Route::post('/admin/organisations/register', 'Admin\OrganisationController@register');
        Route::get('/admin/organisations/register', 'Admin\OrganisationController@showOrgRegisterForm');
        Route::match(['get', 'post'], '/admin/organisations/view/{uri}', 'Admin\OrganisationController@view')->name('adminOrgView');
        Route::match(['get', 'post'], '/admin/organisations/chronology/{uri}', 'Admin\OrganisationController@chronology');
        Route::match(['post', 'get'], '/admin/organisations/edit/{uri}', 'Admin\OrganisationController@edit');
        Route::post('/admin/organisations/delete/{id}', 'Admin\OrganisationController@delete');
        Route::match(
            ['get', 'post'],
            '/admin/organisations/members/{uri}',
            'Admin\OrganisationController@viewMembers'
        )->name('adminOrgMembersView');
        Route::match(
            ['get', 'post'],
            '/admin/organisations/members/addNew/{uri}',
            'Admin\OrganisationController@addMembersNew'
        )->name('adminAddOrgMembersNew');
        Route::get(
            '/admin/organisations/members/delete',
            'Admin\OrganisationController@delMember'
        )->name('adminDelOrgMember');

        Route::match(['get', 'post'], '/admin/groups', 'Admin\GroupController@list')->name('adminGroups');
        Route::match(['get', 'post'], '/admin/groups/register', 'Admin\GroupController@register');
        Route::match(['get', 'post'], '/admin/groups/view/{uri}', 'Admin\GroupController@view');
        Route::match(['get', 'post'], '/admin/groups/chronology/{uri}', 'Admin\GroupController@chronology');

        Route::post('/admin/groups/delete/{id}', 'Admin\GroupController@delete');
        Route::match(['get', 'post'], '/admin/groups/edit/{uri}', 'Admin\GroupController@edit');
        Route::get('/admin/groups/search', 'Admin\GroupController@search');
        Route::match(
            ['get', 'post'],
            '/admin/groups/members/{uri}',
            'Admin\GroupController@viewMembers'
        )->name('adminGroupMembersView');
        Route::match(
            ['get', 'post'],
            '/admin/groups/members/addNew/{uri}',
            'Admin\GroupController@addMembersNew'
        )->name('adminAddGroupMembersNew');

        Route::match(['get', 'post'], '/admin/terms-of-use/list', 'Admin\TermsOfUseController@list')->name('adminTermsOfUse');
        Route::match(['get', 'post'], '/admin/terms-of-use/add', 'Admin\TermsOfUseController@add');
        Route::match(['get', 'post'], '/admin/terms-of-use/view/{id}', 'Admin\TermsOfUseController@view');
        Route::match(['get', 'post'], '/admin/terms-of-use/edit/{id}', 'Admin\TermsOfUseController@edit');
        Route::match(['get', 'post'], '/admin/terms-of-use/delete/{id}', 'Admin\TermsOfUseController@delete');

        Route::match(['get', 'post'], '/admin/terms-of-use-request/list', 'Admin\TermsOfUseRequestController@list');
        Route::match(['get', 'post'], '/admin/terms-of-use-request/edit/{id}', 'Admin\TermsOfUseRequestController@edit');
        Route::match(['get', 'post'], '/admin/terms-of-use-request/delete/{id}', 'Admin\TermsOfUseRequestController@delete');

        Route::match(['get', 'post'], '/admin/themes/list', 'Admin\ThemeController@list')->name('adminThemes');
        Route::match(['get', 'post'], '/admin/themes/add', 'Admin\ThemeController@add');
        Route::match(['get', 'post'], '/admin/themes/view/{id}', 'Admin\ThemeController@view');
        Route::match(['get', 'post'], '/admin/themes/edit/{id}', 'Admin\ThemeController@edit');
        Route::match(['get', 'post'], '/admin/themes/delete/{id}', 'Admin\ThemeController@delete');

        Route::match(['get', 'post'], '/admin/images/add', 'Admin\ImageController@add');
        Route::match(['get', 'post'], '/admin/images/view/{id}', 'Admin\ImageController@view');
        Route::match(['get', 'post'], '/admin/images/edit/{id}', 'Admin\ImageController@edit');
        Route::match(['get', 'post'], '/admin/images/delete/{id}', 'Admin\ImageController@delete');
        Route::match(['get', 'post'], '/admin/images/list', 'Admin\ImageController@list')->name('adminImages');

        Route::match(['get', 'post'], '/admin/categories/list', 'Admin\SubThemeController@list')->name('adminCategories');
        Route::match(['get', 'post'], '/admin/categories/search', 'Admin\SubThemeController@search');
        Route::match(['get', 'post'], '/admin/categories/add', 'Admin\SubThemeController@add');
        Route::match(['get', 'post'], '/admin/categories/view/{id}', 'Admin\SubThemeController@view');
        Route::match(['get', 'post'], '/admin/categories/edit/{id}', 'Admin\SubThemeController@edit');
        Route::match(['get', 'post'], '/admin/categories/delete/{id}', 'Admin\SubThemeController@delete');

        Route::match(['get', 'post'], '/admin/users', 'Admin\UserController@list')->name('adminUsers');
        Route::match(['get', 'post'], '/admin/users/search', 'Admin\UserController@search');
        Route::match(['get', 'post'], '/admin/users/create', 'Admin\UserController@create');
        Route::match(['get', 'post'], '/admin/users/edit/{id}', 'Admin\UserController@edit');

        Route::match(['get', 'post'], '/admin/documents/list', 'Admin\DocumentController@list')->name('adminDocs');
        Route::match(['get', 'post'], '/admin/documents/search', 'Admin\DocumentController@search');
        Route::match(['get', 'post'], '/admin/documents/add', 'Admin\DocumentController@add');
        Route::match(['get', 'post'], '/admin/documents/view/{id}', 'Admin\DocumentController@view');
        Route::match(['get', 'post'], '/admin/documents/edit/{id}', 'Admin\DocumentController@edit');
        Route::match(['get', 'post'], '/admin/documents/delete/{id}', 'Admin\DocumentController@delete');

        Route::match(['get', 'post'], '/admin/forum/discussions/list', 'Admin\ForumController@listDiscussions');
        Route::match(['get', 'post'], '/admin/forum/discussions/add', 'Admin\ForumController@addDiscussion');
        Route::match(['get', 'post'], '/admin/forum/discussions/view/{id}', 'Admin\ForumController@viewDiscussion');
        Route::match(['get', 'post'], '/admin/forum/discussions/edit/{id}', 'Admin\ForumController@editDiscussion');
        Route::match(['get', 'post'], '/admin/forum/discussions/delete/{id}', 'Admin\ForumController@deleteDiscussion');

        Route::match(['get', 'post'], '/admin/forum/posts/list/{id}', 'Admin\ForumController@listPosts');
        Route::match(['get', 'post'], '/admin/forum/posts/view/{id}', 'Admin\ForumController@viewPost');
        Route::match(['get', 'post'], '/admin/forum/posts/delete/{id}', 'Admin\ForumController@deletePost');

        Route::match(['get', 'post'], '/admin/forum/categories/list', 'Admin\ForumController@listCategories');
        Route::match(['get', 'post'], '/admin/forum/categories/add', 'Admin\ForumController@addCategory');
        Route::match(['get', 'post'], '/admin/forum/categories/view/{id}', 'Admin\ForumController@viewCategory');
        Route::match(['get', 'post'], '/admin/forum/categories/edit/{id}', 'Admin\ForumController@editCategory');
        Route::match(['get', 'post'], '/admin/forum/categories/delete/{id}', 'Admin\ForumController@deleteCategory');

        Route::match(['get', 'post'], '/admin/forum/subcategories/list/{id}', 'Admin\ForumController@listSubcategories');
        Route::match(['get', 'post'], '/admin/forum/subcategories/add/{id}', 'Admin\ForumController@addSubcategory');
        Route::match(['get', 'post'], '/admin/forum/subcategories/view/{id}', 'Admin\ForumController@viewSubcategory');
        Route::match(['get', 'post'], '/admin/forum/subcategories/edit/{id}', 'Admin\ForumController@editSubcategory');
        Route::match(['get', 'post'], '/admin/forum/subcategories/delete/{id}', 'Admin\ForumController@deleteSubcategory');

        Route::match(['get', 'post'], '/admin/roles', 'Admin\RoleController@list')->name('adminRoles');
        Route::match(['get', 'post'], '/admin/roles/add', 'Admin\RoleController@addRole');
        Route::match(['get', 'post'], '/admin/roles/edit/{id}', 'Admin\RoleController@editRole');
        Route::match(['get', 'post'], '/admin/roles/view/{id}', 'Admin\RoleController@viewRole');
        Route::match(['get', 'post'], '/admin/roles/delete/{id}', 'Admin\RoleController@deleteRole');
        Route::match(['get', 'post'], '/admin/roles/rights/{id}', 'Admin\RoleController@roleRights');

        Route::match(['get', 'post'], '/admin/languages', 'Admin\LangController@list')->name('adminLangs');
        Route::match(['get', 'post'], '/admin/languages/add', 'Admin\LangController@addLang');
        Route::match(['get', 'post'], '/admin/languages/edit/{id}', 'Admin\LangController@editLang');
        Route::match(['get', 'post'], '/admin/languages/delete/{id}', 'Admin\LangController@deleteLocale');

        Route::match(['get', 'post'], 'admin/history/{type}', 'Admin\HistoryController@history');

        Route::match(['get', 'post'], '/user/newsFeed/{filter?}/{objId?}', 'UserController@newsFeed');

        Route::match(['get', 'post'], '/user/dataset/view/{uri}', 'UserController@datasetView')->name('userDatasetView');
        Route::match(['get', 'post'], '/user/datasets', 'UserController@datasets');
        Route::match(['get', 'post'], '/user/dataset/create', 'UserController@datasetCreate');

        Route::match(['get', 'post'], '/user/organisations/dataset/create/{uri}', 'UserController@orgDatasetCreate');
        Route::match(['get', 'post'], '/user/groups/dataset/create/{uri}', 'UserController@groupDatasetCreate');
        Route::match(['get', 'post'], '/user/dataset/edit/{uri}', 'UserController@datasetEdit')->name('datasetEdit');

        Route::match(['get', 'post'], '/admin/datasets', 'Admin\DataSetController@listDatasets')->name('adminDataSets');
        Route::match(['get', 'post'], '/admin/dataset/add', 'Admin\DataSetController@add');
        Route::match(['get', 'post'], '/admin/dataset/view/{uri}', 'Admin\DataSetController@view')->name('adminDatasetView');
        Route::match(['get', 'post'], '/admin/dataset/edit/{uri}', 'Admin\DataSetController@edit');
        Route::match(['get', 'post'], '/admin/dataset/delete', 'Admin\DataSetController@delete');
        Route::match(['get', 'post'], '/admin/datasetsDeleted', 'Admin\DataSetController@listDeletedDatasets');
        Route::match(['get', 'post'], '/admin/viewDeletedDataset/{uri}', 'Admin\DataSetController@viewDeletedDataset');

        Route::match(
            ['get', 'post'],
            '/user/dataset/resource/create/{uri}',
            'UserController@resourceCreate'
        )->name('resourceCreate');
        Route::match(
            ['get', 'post'],
            '/user/groups/{grpUri}/dataset/resource/create/{uri}',
            'UserController@groupResourceCreate'
        )->name('userGroupResourceCreate');
        Route::match(
            ['get', 'post'],
            '/user/organisations/{orguri}/dataset/resource/create/{uri}',
            'UserController@orgResourceCreate'
        )->name('userOrgResourceCreate');

        Route::match(
            ['get', 'post'],
            '/admin/dataset/resource/create/{uri}',
            'Admin\DataSetController@resourceCreate'
        );

        Route::match(['get', 'post'], 'user/resource/view/{uri}/{version?}', 'UserController@resourceView')->name('resourceView');
        Route::match(['get', 'post'], 'admin/resource/view/{uri}/{version?}', 'Admin\DataSetController@resourceView');
        Route::match(['get', 'post'], 'user/resource/edit/{uri}', 'UserController@resourceEditMeta');
        Route::match(['get', 'post'], 'admin/resource/edit/{uri}/{parentUri?}', 'Admin\DataSetController@resourceEditMeta');
        Route::match(['get', 'post'], 'user/resource/update/{uri}', 'UserController@resourceUpdate');
        Route::match(['get', 'post'], 'admin/resource/update/{uri}', 'Admin\DataSetController@resourceUpdate');
        Route::match(['get', 'post'], 'resource/import/cancel/{uri}/{action}', 'ResourceController@resourceCancelImport');
        Route::match(['get', 'post'], 'importCSV', 'ResourceController@importCsvData');
        Route::match(['get', 'post'], 'importElastic', 'ResourceController@importElasticData');
        Route::match(['get', 'post'], 'user/invite', 'UserController@inviteUser');
        Route::match(['get', 'post'], 'user/settings', 'UserController@settings')->name('settings');

        Route::match(['get', 'post'], 'user/groups/datasets/{uri}', 'UserController@groupDatasets');
        Route::match(['get', 'post'], 'user/groups/{grpUri}/dataset/edit/{uri}', 'UserController@groupDatasetEdit');
        Route::match(['get', 'post'], 'user/groups/{grpUri}/dataset/{uri}', 'UserController@groupDatasetView')->name('userGroupDatasetView');
        Route::match(['get', 'post'], 'user/groups/{grpUri}/resource/{uri}/{version?}', 'UserController@groupResourceView');
        Route::match(['get', 'post'], 'user/groups/resource/edit/{uri}/{parentUri}', 'UserController@resourceEditMeta');
        Route::match(['get', 'post'], 'user/groups/resource/update/{uri}/{parentUri}', 'UserController@resourceUpdate');

        Route::match(['get', 'post'], 'user/groups/register', 'UserController@registerGroup');
        Route::get('user/groups/search', 'UserController@searchGroups');
        Route::match(['get', 'post'], 'user/groups', 'UserController@groups');
        Route::match(['get', 'post'], 'user/groups/view/{uri}', 'UserController@viewGroup');
        Route::match(['get', 'post'], 'user/groups/edit/{uri}', 'UserController@editGroup');
        Route::match(['get', 'post'], 'user/groups/chronology/{uri}', 'UserController@groupChronology');
        Route::post('user/groups/delete/{id}', 'UserController@deleteGroup');

        Route::match(['get', 'post'], 'user/organisations/datasets/{uri}', 'UserController@orgDatasets');
        Route::match(['get', 'post'], 'admin/organisations/datasets/{uri}', 'Admin\OrganisationController@orgDatasets');
        Route::match(['get', 'post'], 'user/organisations/{orguri}/resource/{uri}/{version?}', 'UserController@orgResourceView')->name('userOrgResourceView');
        Route::match(['get', 'post'], 'user/organisations/resource/edit/{uri}/{parentUri}', 'UserController@resourceEditMeta');
        Route::match(['get', 'post'], 'user/organisations/resource/update/{uri}/{parentUri}', 'UserController@resourceUpdate');
        Route::match(['get', 'post'], 'user/organisations/dataset/view/{uri}', 'UserController@orgDatasetView')->name('userOrgDatasetView');
        Route::match(['get', 'post'], 'user/organisations/chronology/{uri}', 'UserController@orgChronology');

        Route::get('user/organisations', 'UserController@organisations');
        Route::match(['get', 'post'], 'user/organisations/delete/{id}', 'UserController@deleteOrg');
        Route::match(['get', 'post'], 'user/organisations/search', 'UserController@searchOrg');
        Route::get('user/organisations/view/{uri}', 'UserController@viewOrg')->name('userOrgView');
        Route::match(['get', 'post'], 'user/organisations/edit/{uri}', 'UserController@editOrg');
        Route::match(['get', 'post'], 'user/organisations/{orgUri}/dataset/edit/{uri}', 'UserController@orgDatasetEdit');

        Route::match(['get', 'post'], 'admin/groups/resource/edit/{uri}/{parentUri}', 'Admin\DataSetController@resourceEditMeta');
        Route::match(['get', 'post'], 'admin/groups/resource/update/{uri}/{parentUri}', 'Admin\DataSetController@resourceUpdate');
        Route::match(['get', 'post'], 'admin/organisations/resource/edit/{uri}/{parentUri}', 'Admin\DataSetController@resourceEditMeta');
        Route::match(['get', 'post'], 'admin/organisations/resource/update/{uri}/{parentUri}', 'Admin\DataSetController@resourceUpdate');

        Route::get('user/organisations/datasets/search', 'UserController@searchDataset');

        Route::match(['get', 'post'], 'user/organisations/members/{uri}', 'UserController@viewOrgMembers')->name('userOrgMembersView');
        Route::match(['get', 'post'], 'user/groups/members/{uri}', 'UserController@viewGroupMembers')->name('userGroupMembersView');

        Route::match(
            ['get', 'post'],
            'user/groups/members/addNew/{uri}',
            'UserController@addGroupMembersNew'
        )->name('addGroupMembersNew');

        Route::get(
            'user/organisations/members/addByMail',
            'UserController@addOrgMembersByMail'
        )->name('addOrgMembersByMail');
        Route::match(
            ['get', 'post'],
            'user/organisations/members/addNew/{uri}',
            'UserController@addOrgMembersNew'
        )->name('addOrgMembersNew');
        Route::get(
            'user/organisations/members/addExisting',
            'UserController@addOrgMembersExisting'
        )->name('addOrgMembersExisting');
        Route::get(
            'user/organisations/members/delete',
            'UserController@delOrgMember'
        )->name('delOrgMember');

        Route::match(['get', 'post'], 'admin/signals/list', 'Admin\SignalController@list');
        Route::match(['get', 'post'], 'admin/signal/edit/{id}', 'Admin\SignalController@edit');
        Route::match(['get', 'post'], 'admin/signal/delete/{id}', 'Admin\SignalController@delete');
        Route::match(['get', 'post'], 'signal/remove', 'UserController@removeSignal');

        Route::match(['get', 'post'], '/admin/sections/list', 'Admin\SectionController@list')->name('adminSections');
        Route::match(['get', 'post'], '/admin/sections/add', 'Admin\SectionController@add');
        Route::match(['get', 'post'], '/admin/sections/view/{id}', 'Admin\SectionController@view');
        Route::match(['get', 'post'], '/admin/sections/edit/{id}', 'Admin\SectionController@edit');
        Route::match(['get', 'post'], '/admin/sections/delete/{id}', 'Admin\SectionController@delete');

        Route::match(['get', 'post'], '/admin/subsections/list/{id}', 'Admin\SubsectionController@list')->name('adminSubSections');
        Route::match(['get', 'post'], '/admin/subsections/add/{id}', 'Admin\SubsectionController@add');
        Route::match(['get', 'post'], '/admin/subsections/view/{id}', 'Admin\SubsectionController@view');
        Route::match(['get', 'post'], '/admin/subsections/edit/{id}', 'Admin\SubsectionController@edit');
        Route::match(['get', 'post'], '/admin/subsections/delete/{id}', 'Admin\SubsectionController@delete');

        Route::match(['get', 'post'], '/admin/pages/list', 'Admin\PageController@list')->name('adminPages');
        Route::match(['get', 'post'], '/admin/pages/view/{id}', 'Admin\PageController@view');
        Route::match(['get', 'post'], '/admin/pages/delete/{id}', 'Admin\PageController@delete');
        Route::match(['get', 'post'], '/admin/pages/edit/{id}', 'Admin\PageController@edit');
        Route::match(['get', 'post'], '/admin/pages/add', 'Admin\PageController@add');

        Route::match(['get', 'post'], '/admin/news/list', 'Admin\NewsController@list')->name('adminNews');
        Route::match(['get', 'post'], '/admin/news/view/{id}', 'Admin\NewsController@view');
        Route::match(['get', 'post'], '/admin/news/delete/{id}', 'Admin\NewsController@delete');
        Route::match(['get', 'post'], '/admin/news/edit/{id}', 'Admin\NewsController@edit');
        Route::match(['get', 'post'], '/admin/news/add', 'Admin\NewsController@add');

        Route::match(['get', 'post'], '/admin/data-requests/list', 'Admin\DataRequestController@listDataRequests');
        Route::match(['get', 'post'], '/admin/data-request/edit/{id}', 'Admin\DataRequestController@editDataRequest');
        Route::match(['get', 'post'], '/admin/data-request/delete/{id}', 'Admin\DataRequestController@deleteDataRequest');

        Route::match(['get', 'post'], 'admin/help/sections/list', 'Admin\HelpController@listSections');
        Route::match(['get', 'post'], 'admin/help/subsections/list/{id}', 'Admin\HelpController@listSubsections');
        Route::match(['get', 'post'], 'admin/help/section/add/{parent?}', 'Admin\HelpController@addHelpSecton');
        Route::match(['get', 'post'], 'admin/help/section/edit/{id}', 'Admin\HelpController@editHelpSection');
        Route::match(['get', 'post'], 'admin/help/section/view/{id}', 'Admin\HelpController@viewHelpSection');
        Route::match(['get', 'post'], 'admin/help/subsection/view/{id}', 'Admin\HelpController@viewHelpSubsection');
        Route::get('admin/help/section/delete/{id}', 'Admin\HelpController@deleteHelpSection');

        Route::match(['get', 'post'], 'admin/help/pages/list', 'Admin\HelpController@listPages');
        Route::match(['get', 'post'], 'admin/help/pages/add', 'Admin\HelpController@addHelpPage')->name('addHelpPage');
        Route::match(['get', 'post'], 'admin/help/page/edit/{id}', 'Admin\HelpController@editHelpPage');
        Route::match(['get', 'post'], 'admin/help/page/view/{id}', 'Admin\HelpController@viewHelpPage');
        Route::get('admin/help/page/delete/{id}', 'Admin\HelpController@deleteHelpPage');
    });
});

Route::match(['get', 'post'], '/resource/download', 'ResourceController@resourceDownload');
Route::match(['get', 'post'], '/resource/download/{uri}/{format}', 'ResourceController@resourceDirectDownload');
Route::match(['get', 'post'], '/dataset/{uri}/resources/download/{format}', 'ResourceController@resourcesDownloadZip');
Route::match(['get', 'post'], 'help', 'HelpController@list');
Route::match(['get', 'post'], 'help/search', 'HelpController@search');
Route::match(['get', 'post'], 'help/view/{id}/{activePage?}', 'HelpController@view');
Route::match(['get', 'post'], 'help/page/view/{id}', 'HelpController@pageView');

Route::match(['get', 'post'], 'tool', 'ToolController@configDbms');
Route::match(['get', 'post'], 'tool/configDbms', 'ToolController@configDbms');
Route::match(['get', 'post'], 'tool/configFile', 'ToolController@configFile');
Route::match(['get', 'post'], 'tool/chronology', 'ToolController@configHistory');

Route::match(['get', 'post'], 'users/list', 'UserController@listUsers')->name('usersList');
Route::match(['get', 'post'], 'user/profile/{id}', 'UserController@profile');
Route::match(['get', 'post'], 'user/profile/chronology/{id}', 'UserController@userChronology');

Route::post('user/sendTermsOfUseReq', 'UserController@sendTermsOfUseReq');
Route::get('/', 'HomeController@index');

Route::get('logout', function() {
    Session::flush();
    Auth::logout();

    return redirect('/');
});

Route::get('preGenerated', 'UserController@preGenerated')->name('preGenerated');

Route::match(['get', 'post'], 'login', 'Auth\LoginController@login')->name('login');

Route::match(['get', 'post'], 'registration', 'UserController@registration')->name('registration');
Route::match(['get', 'post'], 'orgRegistration', 'UserController@orgRegistration')->name('orgRegistration');

Route::match(['get', 'post'], 'confirmation', 'UserController@confirmation')->name('confirmation');
Route::match(['get', 'post'], 'mailConfirmation', 'UserController@mailConfirmation')->name('mailConfirmation');
Route::match(['get', 'post'], 'confirmError', 'UserController@confirmError')->name('confirmError');

Route::match(['get', 'post'], 'delSettings', 'UserController@deleteCustomSettings');

Route::match(['get', 'post'], 'execResourceQueryScript', 'ResourceController@execResourceQueryScript');

Route::match(['get', 'post'], 'data', 'DataController@list')->name('data');
Route::match(['get', 'post'], 'data/view/{uri}', 'DataController@view')->name('dataView');
Route::match(['get', 'post'], 'data/resourceView/{uri}/{version?}', 'DataController@resourceView')->name('dataResourceView');
Route::post('data/resource/sendSignal', 'DataController@sendSignal');
Route::match(['get', 'post'], 'data/resource/embed/{uri}', 'VisualisationController@resourceEmbed');

Route::match(['get', 'post'], 'data/linkedData', 'DataController@linkedData');

Route::match(['get', 'post'], 'data/reported', 'DataController@reportedList')->name('reportedData');
Route::match(['get', 'post'], 'data/reported/view/{uri}', 'DataController@reportedView')->name('reportedView');
Route::match(['get', 'post'], 'data/reported/resourceView/{uri}/{version?}', 'DataController@reportedResourceView')->name('reportedResourceView');

Route::match(['get', 'post'], 'data/chronology/{uri}', 'DataController@chronology')->name('dataChronology');

Route::match(['get', 'post'], 'organisation', 'OrganisationController@list')->name('organisations');
Route::match(['get', 'post'], 'organisation/profile/{uri}', 'OrganisationController@view')->name('orgProfile');
Route::post('organisation/delete', 'OrganisationController@delete')->name('orgDelete');

Route::match(['get', 'post'], 'organisation/{uri}/datasets', 'OrganisationController@datasets')->name('orgDatasets');
Route::match(['get', 'post'], 'organisation/dataset/{uri}', 'OrganisationController@viewDataset')->name('orgViewDataset');
Route::match(['get', 'post'], 'organisation/datasets/resourceView/{uri}/{version?}', 'OrganisationController@resourceView')->name('orgDataResourceView');
Route::post('organisation/resource/sendSignal', 'OrganisationController@sendSignal');

Route::match(['get', 'post'], 'organisation/chronology/{uri}', 'OrganisationController@chronology');
Route::match(['get', 'post'], 'organisation/dataset/chronology/{uri}', 'OrganisationController@datasetChronology');

Route::match(['get', 'post'], 'groups', 'GroupController@list')->name('groups');
Route::match(['get', 'post'], 'groups/view/{uri}', 'GroupController@view')->name('groupView');
Route::post('groups/delete', 'GroupController@delete')->name('groupDelete');
Route::match(['get', 'post'], 'groups/chronology/{uri}', 'GroupController@chronology');

Route::get('/msg', 'MsgController@display');

Route::get('user', 'UserController@index');
Route::post('user', 'UserController@index');

Route::get('user/orgMembers', function () {
    return view('user/orgMembers', ['class' => 'user']);
});

Route::match(['get', 'post'], 'password/forgotten', 'UserController@forgottenPassword');
Route::match(['get', 'post'], 'password/reset', 'UserController@passwordReset')->name('passReset');

Route::match(['post', 'get'], 'request', 'RequestController@sendDataRequest')->middleware('help');

Route::match(['get', 'post'], 'news', 'NewsController@listNews');
Route::match(['get', 'post'], 'news/search', 'NewsController@searchNews');
Route::match(['get', 'post'], 'news/view/{id}', 'NewsController@viewNews');

Route::match(['get', 'post'], 'document', 'DocumentController@listDocuments');
Route::match(['get', 'post'], 'document/search', 'DocumentController@searchDocuments');
Route::match(['get', 'post'], 'document/view/{id}', 'DocumentController@viewDocument');
Route::match(['get', 'post'], 'document/download/{path}/{fileName}', 'DocumentController@downloadDocument');

Route::get('{section}', 'StaticPageController@show');

Route::get('lang/{lang}', ['as' => 'lang.switch', 'uses' => 'LanguageController@switchLang']);

Route::get('/datasets/{uri}/rss', 'FeedController@getOrganisationDatasetHistory');
Route::get('/datasets/rss', 'FeedController@getDatasetsHistory');
Route::get('/news/rss', 'FeedController@getNewsHistory');

Route::get('/images/item/{id}', 'DocumentController@viewImage');
Route::get('/images/thumb/{id}', 'DocumentController@viewImage');

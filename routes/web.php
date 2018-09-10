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

    Route::match(['get', 'post'], '/user/resource/download', 'UserController@resourceDownload')->name('resourceDownload');
    Route::post('/admin/adminChangePassword', 'Admin\UserController@adminChangePassword');

    Route::match(['get', 'post'], '/admin/organisations', 'Admin\OrganisationController@list');
    Route::match(['get', 'post'], '/admin/organisations/search', 'Admin\OrganisationController@search');
    Route::post('/admin/organisations/register', 'Admin\OrganisationController@register');
    Route::get('/admin/organisations/register', 'Admin\OrganisationController@showOrgRegisterForm');
    Route::get('/admin/organisations/view/{uri}', 'Admin\OrganisationController@view')->name('adminOrgView');
    Route::match(['get', 'post'], '/admin/organisations/{uri}/chronology', 'Admin\OrganisationController@chronology');
    Route::match(['post', 'get'], '/admin/organisations/edit/{uri}', 'Admin\OrganisationController@edit');
    Route::post('/admin/organisations/delete/{id}', 'Admin\OrganisationController@delete');
    Route::match(
        ['get', 'post'],
        '/admin/organisations/members/{uri}',
        'Admin\OrganisationController@viewMembers'
    )->name('adminOrgMembersView');

    Route::get(
        '/admin/organisations/members/addByMail',
        'Admin\OrganisationController@addMembersByMail'
    )->name('adminAddOrgMembersByMail');
    Route::match(
        ['get', 'post'],
        '/admin/organisations/members/addNew/{uri}',
        'Admin\OrganisationController@addMembersNew'
    )->name('adminAddOrgMembersNew');
    Route::get(
        '/admin/organisations/members/addExisting',
        'Admin\OrganisationController@addMembersExisting'
    )->name('adminAddOrgMembersExisting');
    Route::get(
        '/admin/organisations/members/delete',
        'Admin\OrganisationController@delMember'
    )->name('adminDelOrgMember');

    Route::match(['get', 'post'], '/admin/groups', 'Admin\GroupController@list');
    Route::match(['get', 'post'], '/admin/groups/register', 'Admin\GroupController@register');
    Route::match(['get', 'post'], '/admin/groups/view/{uri}', 'Admin\GroupController@view');
    Route::match(['get', 'post'], '/admin/groups/{uri}/chronology', 'Admin\GroupController@chronology');

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

    Route::match(['get', 'post'], '/admin/terms-of-use/list', 'Admin\TermsOfUseController@list');
    Route::match(['get', 'post'], '/admin/terms-of-use/add', 'Admin\TermsOfUseController@add');
    Route::match(['get', 'post'], '/admin/terms-of-use/view/{id}', 'Admin\TermsOfUseController@view');
    Route::match(['get', 'post'], '/admin/terms-of-use/edit/{id}', 'Admin\TermsOfUseController@edit');
    Route::match(['get', 'post'], '/admin/terms-of-use/delete/{id}', 'Admin\TermsOfUseController@delete');

    Route::match(['get', 'post'], '/admin/terms-of-use-request/list', 'Admin\TermsOfUseRequestController@list');
    Route::match(['get', 'post'], '/admin/terms-of-use-request/edit/{id}', 'Admin\TermsOfUseRequestController@edit');
    Route::match(['get', 'post'], '/admin/terms-of-use-request/delete/{id}', 'Admin\TermsOfUseRequestController@delete');

    Route::match(['get', 'post'], '/admin/themes/list', 'Admin\ThemeController@list');
    Route::match(['get', 'post'], '/admin/themes/add', 'Admin\ThemeController@add');
    Route::match(['get', 'post'], '/admin/themes/view/{id}', 'Admin\ThemeController@view');
    Route::match(['get', 'post'], '/admin/themes/edit/{id}', 'Admin\ThemeController@edit');
    Route::match(['get', 'post'], '/admin/themes/delete/{id}', 'Admin\ThemeController@delete');

    Route::match(['get', 'post'], '/admin/images/add', 'Admin\ImageController@add');
    Route::match(['get', 'post'], '/admin/images/view/{id}', 'Admin\ImageController@view');
    Route::match(['get', 'post'], '/admin/images/edit/{id}', 'Admin\ImageController@edit');
    Route::match(['get', 'post'], '/admin/images/delete/{id}', 'Admin\ImageController@delete');
    Route::match(['get', 'post'], '/admin/images/list', 'Admin\ImageController@list');

    Route::get('/images/item/{id}', 'Admin\ImageController@viewImage');
    Route::get('/images/thumb/{id}', 'Admin\ImageController@viewImage');

    Route::match(['get', 'post'], '/admin/categories/list', 'Admin\SubThemeController@list');
    Route::match(['get', 'post'], '/admin/categories/search', 'Admin\SubThemeController@search');
    Route::match(['get', 'post'], '/admin/categories/add', 'Admin\SubThemeController@add');
    Route::match(['get', 'post'], '/admin/categories/view/{id}', 'Admin\SubThemeController@view');
    Route::match(['get', 'post'], '/admin/categories/edit/{id}', 'Admin\SubThemeController@edit');
    Route::match(['get', 'post'], '/admin/categories/delete/{id}', 'Admin\SubThemeController@delete');

    Route::match(['get', 'post'], '/admin/users', 'Admin\UserController@list');
    Route::match(['get', 'post'], '/admin/users/search', 'Admin\UserController@search');
    Route::match(['get', 'post'], '/admin/users/create', 'Admin\UserController@create');
    Route::match(['get', 'post'], '/admin/users/edit/{id}', 'Admin\UserController@edit');

    Route::match(['get', 'post'], '/admin/documents/list', 'Admin\DocumentController@list');
    Route::match(['get', 'post'], '/admin/documents/search', 'Admin\DocumentController@search');
    Route::match(['get', 'post'], '/admin/documents/add', 'Admin\DocumentController@add');
    Route::match(['get', 'post'], '/admin/documents/view/{id}', 'Admin\DocumentController@view');
    Route::match(['get', 'post'], '/admin/documents/edit/{id}', 'Admin\DocumentController@edit');
    Route::match(['get', 'post'], '/admin/documents/delete/{id}', 'Admin\DocumentController@delete');

    Route::match(['get', 'post'], '/admin/roles', 'Admin\RoleController@list');
    Route::match(['get', 'post'], '/admin/roles/add', 'Admin\RoleController@addRole');
    Route::match(['get', 'post'], '/admin/roles/edit/{id}', 'Admin\RoleController@editRole');
    Route::match(['get', 'post'], '/admin/roles/view/{id}', 'Admin\RoleController@viewRole');
    Route::match(['get', 'post'], '/admin/roles/delete/{id}', 'Admin\RoleController@deleteRole');
    Route::match(['get', 'post'], '/admin/roles/rights/{id}', 'Admin\RoleController@roleRights');

    Route::match(['get', 'post'], '/admin/languages', 'Admin\LangController@list');
    Route::match(['get', 'post'], '/admin/languages/add', 'Admin\LangController@addLang');
    Route::match(['get', 'post'], '/admin/languages/edit/{id}', 'Admin\LangController@editLang');
    Route::match(['get', 'post'], '/admin/languages/delete/{id}', 'Admin\LangController@deleteLocale');

    Route::match(['get', 'post'], 'admin/history/{type}', 'Admin\HistoryController@history');

    Route::middleware('check.resources')->group(function () {
        Route::match(['get', 'post'], '/user/newsFeed/{filter?}/{objId?}', 'UserController@newsFeed');

        Route::match(['get', 'post'], '/user/dataset/view/{uri}', 'UserController@datasetView')->name('datasetView');
        Route::match(['get', 'post'], '/user/datasetDelete', 'UserController@datasetDelete');
        Route::match(['get', 'post'], '/user/datasets', 'UserController@datasets');
        Route::match(['get', 'post'], '/user/dataset/search', 'UserController@datasetSearch');
        Route::match(['get', 'post'], '/user/dataset/create', 'UserController@datasetCreate');
        Route::match(['get', 'post'], '/user/organisations/dataset/create', 'UserController@orgDatasetCreate');
        Route::match(['get', 'post'], '/user/groups/dataset/create', 'UserController@groupDatasetCreate');
        Route::match(['get', 'post'], '/user/dataset/edit/{uri}', 'UserController@datasetEdit')->name('datasetEdit');

        Route::match(['get', 'post'], '/admin/datasets', 'Admin\DataSetController@listDatasets');
        Route::match(['get', 'post'], '/admin/dataset/add', 'Admin\DataSetController@add');
        Route::match(['get', 'post'], '/admin/dataset/view/{uri}', 'Admin\DataSetController@view');
        Route::match(['get', 'post'], '/admin/dataset/edit/{uri}', 'Admin\DataSetController@edit');
        Route::match(['get', 'post'], '/admin/dataset/delete', 'Admin\DataSetController@delete');

        Route::match(
            ['get', 'post'],
            '/user/dataset/resource/create/{uri}',
            'UserController@resourceCreate'
        )->name('resourceCreate');
        Route::match(
            ['get', 'post'],
            '/user/group/dataset/resource/create/{uri}',
            'UserController@groupResourceCreate'
        )->name('groupResourceCreate');
        Route::match(
            ['get', 'post'],
            '/user/organisation/dataset/resource/create/{uri}',
            'UserController@orgResourceCreate'
        )->name('orgResourceCreate');

        Route::match(['get', 'post'], '/user/resourceView/{uri}', 'UserController@resourceView')->name('resourceView');
        Route::match(['get', 'post'], '/user/resourceCancelImport/{uri}', 'UserController@resourceCancelImport')->name('cancelImport');
        Route::match(['get', 'post'], '/user/importCSV', 'UserController@importCsvData');
        Route::match(['get', 'post'], '/user/importXML', 'UserController@importXmlData');
        Route::match(['get', 'post'], '/user/importElastic', 'UserController@importElasticData');
        Route::match(['get', 'post'], '/user/invite', 'UserController@inviteUser');
        Route::match(['get', 'post'], '/user/settings', 'UserController@settings')->name('settings');

        Route::match(['get', 'post'], '/user/groups/datasets/{uri}', 'UserController@groupDatasets');
        Route::match(['get', 'post'], '/user/groups/dataset/edit/{uri}', 'UserController@groupDatasetEdit');
        Route::match(['get', 'post'], '/user/groups/dataset/{uri}', 'UserController@groupDatasetView')->name('groupDatasetView');
        Route::match(['get', 'post'], 'user/groups/resource/{uri}', 'UserController@groupResourceView');

        Route::match(['get', 'post'], '/user/groups/register', 'UserController@registerGroup');
        Route::get('/user/groups/search', 'UserController@searchGroups');
        Route::match(['get', 'post'], '/user/groups', 'UserController@groups');
        Route::match(['get', 'post'], '/user/groups/view/{uri}', 'UserController@viewGroup');
        Route::match(['get', 'post'], '/user/groups/edit/{uri}', 'UserController@editGroup');
        Route::match(['get', 'post'], '/user/groups/{uri}/chronology', 'UserController@groupChronology');
        Route::post('/user/groups/delete/{id}', 'UserController@deleteGroup');

        Route::match(['get', 'post'], '/user/organisations/datasets/{uri}', 'UserController@orgDatasets');
        Route::match(['get', 'post'], '/user/organisations/datasets/resourceView/{uri}', 'UserController@orgResourceView')->name('orgResourceView');
        Route::match(['get', 'post'], '/user/organisations/dataset/view/{uri}', 'UserController@orgDatasetView')->name('orgDatasetView');
        Route::match(['get', 'post'], '/user/organisations/{uri}/chronology', 'UserController@orgChronology');

        Route::get('/user/organisations', 'UserController@organisations');
        Route::match(['get', 'post'], '/user/organisations/delete/{id}', 'UserController@deleteOrg');
        Route::match(['get', 'post'], '/user/organisations/search', 'UserController@searchOrg');
        Route::get('/user/organisations/view/{uri}', 'UserController@viewOrg')->name('userOrgView');
        Route::match(['get', 'post'], '/user/organisations/edit/{uri}', 'UserController@editOrg');
        Route::match(['get', 'post'], '/user/organisations/datasets/edit/{uri}', 'UserController@orgDatasetEdit');
        Route::get('/user/organisations/datasets/search', 'UserController@searchDataset');

        Route::match(['get', 'post'], '/user/organisations/members/{uri}', 'UserController@viewOrgMembers')->name('userOrgMembersView');
        Route::match(['get', 'post'], '/user/groups/members/{uri}', 'UserController@viewGroupMembers')->name('userGroupMembersView');

        Route::match(
            ['get', 'post'],
            '/user/groups/members/addNew/{uri}',
            'UserController@addGroupMembersNew'
        )->name('addGroupMembersNew');

        Route::get(
            '/user/organisations/members/addByMail',
            'UserController@addOrgMembersByMail'
        )->name('addOrgMembersByMail');
        Route::match(
            ['get', 'post'],
            '/user/organisations/members/addNew/{uri}',
            'UserController@addOrgMembersNew'
        )->name('addOrgMembersNew');
        Route::get(
            '/user/organisations/members/addExisting',
            'UserController@addOrgMembersExisting'
        )->name('addOrgMembersExisting');
        Route::get(
            '/user/organisations/members/delete',
            'UserController@delOrgMember'
        )->name('delOrgMember');

        Route::match(['get', 'post'], '/admin/signals/list', 'Admin\SignalController@list');
        Route::match(['get', 'post'], '/admin/signal/edit/{id}', 'Admin\SignalController@edit');
        Route::match(['get', 'post'], '/admin/signal/delete/{id}', 'Admin\SignalController@delete');
    });
});

Route::match(['get', 'post'], '/users/list', 'UserController@listUsers')->name('usersList');
Route::match(['get', 'post'], '/user/profile/{id}', 'UserController@profile');
Route::match(['get', 'post'], '/user/profile/{id}/chronology', 'UserController@userChronology');

Route::post('/user/sendTermsOfUseReq', 'UserController@sendTermsOfUseReq');
Route::get('/', 'HomeController@index');

Route::get('/logout', function() {
    Session::flush();
    Auth::logout();

    return redirect('/');
});

Route::get('/preGenerated', 'UserController@preGenerated')->name('preGenerated');

Route::match(['get', 'post'], '/login', 'Auth\LoginController@login')->name('login');

Route::match(['get', 'post'],'/registration', 'UserController@registration')->name('registration');
Route::match(['get', 'post'],'/orgRegistration', 'UserController@orgRegistration')->name('orgRegistration');

Route::match(['get', 'post'],'/confirmation', 'UserController@confirmation')->name('confirmation');
Route::match(['get', 'post'],'/mailConfirmation', 'UserController@mailConfirmation')->name('mailConfirmation');
Route::match(['get', 'post'],'/confirmError', 'UserController@confirmError')->name('confirmError');

Route::match(['get', 'post'], '/delSettings', 'UserController@deleteCustomSettings');

Route::get('/accessibility', function () {
    return view('accessibility', ['class' => 'index']);
});

Route::get('/terms', function () {
    return view('terms', ['class' => 'index']);
});

Route::match(['get', 'post'], '/data', 'DataController@view')->name('dataView');

Route::get('/data/view/{uri}', function () {
    return view('data/view', [
        'class' => 'data',
        'filter' => 'healthcare',
        'mainCats' => [
            'healthcare',
            'innovation',
            'education',
            'public_sector',
            'municipalities',
            'agriculture',
            'justice',
            'economy_business',
        ],
    ]);
});

Route::get('/data/resourceView/{uri}', function () {
    return view('data/view', [
        'class' => 'data',
        'filter' => 'healthcare',
        'mainCats' => [
            'healthcare',
            'innovation',
            'education',
            'public_sector',
            'municipalities',
            'agriculture',
            'justice',
            'economy_business',
        ],
    ]);
});

Route::match(['get', 'post'], '/data/linkedData', 'DataController@linkedData');

Route::get('/data/reportedList', function () {
    return view('data/reportedList', [
        'class' => 'data-attention',
        'filter' => 'healthcare',
        'mainCats' => [
            'healthcare',
            'innovation',
            'education',
            'public_sector',
            'municipalities',
            'agriculture',
            'justice',
            'economy_business',
        ],
    ]);
});

Route::get('/data/reportedView', function () {
    return view('data/reportedView', ['class' => 'data-attention']);
});

Route::match(['get', 'post'], '/organisation', 'OrganisationController@list')->name('orgList');
Route::match(['get', 'post'], '/organisation/search', 'OrganisationController@search');
Route::match(['get', 'post'], '/organisation/profile/{uri}', 'OrganisationController@view');

Route::match(['get', 'post'], '/organisation/{uri}/datasets', 'OrganisationController@datasets');
Route::match(['get', 'post'], '/organisation/{orgUri}/dataset/{uri}', 'OrganisationController@viewDataset');

Route::match(['get', 'post'], '/organisation/{uri}/chronology', 'OrganisationController@chronology');

Route::get('/user', 'UserController@index');
Route::post('/user', 'UserController@index');

Route::get('/user/orgMembers', function () {
    return view('user/orgMembers', ['class' => 'user']);
});

Route::match(['get', 'post'],'/password/forgotten', 'UserController@forgottenPassword');
Route::match(['get', 'post'],'/password/reset', 'UserController@passwordReset')->name('passReset');

Route::get('/request', function () {
    return view('request/dataRequest', ['class' => 'request']);
});

Route::get('/news', function () {
    return view('news/list', ['class' => 'news']);
});

Route::get('/news/view', function () {
    return view('news/view', ['class' => 'news']);
});

Route::get('/document', function () {
    return view('document/list', ['class' => 'documents']);
});

Route::get('/document/view', function () {
    return view('document/view', ['class' => 'documents']);
});

Route::get('/contact', function () {
    return view('contact/contact', ['class' => 'contact']);
});

Route::get('/visualisation', function () {
    return view('visualisation/visualisation', ['class' => 'visualisations']);
});

Route::get('lang/{lang}', ['as' => 'lang.switch', 'uses' => 'LanguageController@switchLang']);

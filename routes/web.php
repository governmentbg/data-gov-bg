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

Route::middleware('auth')->group(function () {
    Route::match(['get', 'post'], '/users/list', 'UserController@listUsers')->name('usersList');
    Route::match(['get', 'post'], '/user/profile/{id}', 'UserController@profile');
    Route::get('/users/list/search', 'UserController@searchUsers');

    Route::post('/user/organisations/register', 'UserController@registerOrg');
    Route::get('/user/organisations/register', 'UserController@showOrgRegisterForm');

    Route::middleware('check.resources')->group(function () {
        Route::match(['get', 'post'], '/user/newsFeed/{filter?}/{objId?}', 'UserController@newsFeed');

        Route::match(['get', 'post'], '/user/dataset/view/{uri}', 'UserController@datasetView')->name('datasetView');
        Route::post('/user/datasetDelete', 'UserController@datasetDelete');
        Route::match(['get', 'post'], '/user/datasets', 'UserController@datasets');
        Route::match(['get', 'post'], '/user/dataset/search', 'UserController@datasetSearch');
        Route::match(['get', 'post'], '/user/dataset/create', 'UserController@datasetCreate');
        Route::match(['get', 'post'], '/user/organisations/dataset/create', 'UserController@orgDatasetCreate');
        Route::match(['get', 'post'], '/user/groups/dataset/create', 'UserController@groupDatasetCreate');
        Route::match(['get', 'post'], '/user/dataset/edit/{uri}', 'UserController@datasetEdit')->name('datasetEdit');

        Route::match(['get', 'post'], '/user/resource/create', 'UserController@resourceCreate')->name('resourceCreate');

        Route::get('/user/resourceView', 'UserController@resourceView')->name('resourceView');
        Route::match(['get', 'post'], '/user/invite', 'UserController@inviteUser');
        Route::match(['get', 'post'], '/user/settings', 'UserController@settings')->name('settings');

        Route::match(['get', 'post'], '/user/groups/datasets', 'UserController@groupDatasets');
        Route::match(['get', 'post'], '/user/groups/dataset/edit/{uri}', 'UserController@groupDatasetEdit');
        Route::match(['get', 'post'], '/user/groups/dataset/view/{uri}', 'UserController@groupDatasetView');
        Route::match(['get', 'post'], 'user/groups/dataset/{uri}', 'UserController@groupDataSetView');
        Route::match(['get', 'post'], 'user/groups/resource/{uri}', 'UserController@groupResourceView');

        Route::match(['get', 'post'], '/user/groups/register', 'UserController@registerGroup');
        Route::get('/user/groups/search', 'UserController@searchGroups');
        Route::match(['get', 'post'], '/user/groups', 'UserController@groups');
        Route::match(['get', 'post'], '/user/groups/view/{uri}', 'UserController@viewGroup');
        Route::match(['get', 'post'], '/user/groups/edit/{uri}', 'UserController@editGroup');
        Route::post('/user/groups/delete/{id}', 'UserController@deleteGroup');
        Route::match(['get', 'post'], '/user/groups/datasets', 'UserController@groupDatasets');

        Route::match(['get', 'post'], '/user/organisations/datasets', 'UserController@orgDatasets');
        Route::get('/user/organisations/datasets/resourceView', 'UserController@orgResourceView')->name('orgResourceView');
        Route::match(['get', 'post'], '/user/organisations/dataset/view/{uri}', 'UserController@orgDatasetView');

        Route::get('/user/organisations', 'UserController@organisations');
        Route::post('/user/organisations/delete/{id}', 'UserController@deleteOrg');
        Route::get('/user/organisations/search', 'UserController@searchOrg');
        Route::get('/user/organisations/view/{uri}', 'UserController@viewOrg')->name('userOrgView');
        Route::post('/user/organisations/edit/{uri}', 'UserController@editOrg');
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
    });
});

Route::post('/user/sendTermsOfUseReq', 'UserController@sendTermsOfUseReq');
Route::get('/', 'HomeController@index');

Route::get('/logout', function() {
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

Route::get('/accessibility', function () {
    return view('accessibility', ['class' => 'index']);
});

Route::get('/terms', function () {
    return view('terms', ['class' => 'index']);
});

Route::get('/data', function () {
    return view('data/list', ['class' => 'data']);
});

Route::get('/data/view', function () {
    return view('data/view', ['class' => 'data']);
});

Route::get('/data/relatedData', function () {
    return view('data/relatedData', ['class' => 'data']);
});

Route::get('/data/reportedList', function () {
    return view('data/reportedList', ['class' => 'data-attention']);
});

Route::get('/data/reportedView', function () {
    return view('data/reportedView', ['class' => 'data-attention']);
});

Route::get('/organisation', function () {
    return view('organisation/list', ['class' => 'organisation']);
});

Route::get('/organisation/profile', function () {
    return view('organisation/profile', ['class' => 'organisation']);
});

Route::get('/organisation/datasets', function () {
    return view('organisation/datasets', ['class' => 'organisation']);
});

Route::get('/organisation/viewDataset', function () {
    return view('organisation/viewDataset', ['class' => 'organisation']);
});

Route::get('/organisation/chronology', function () {
    return view('organisation/chronology', ['class' => 'organisation']);
});

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

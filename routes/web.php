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

//Route::get('/', 'HomeController@index');
//
//Auth::routes();

Route::middleware('auth')->group(function () {
    Route::match(['get', 'post'], '/users/list', 'UserController@listUsers')->name('usersList');
    Route::match(['get', 'post'],'/user/profile/{id}', 'UserController@profile');
    Route::get('/users/list/search', 'UserController@searchUsers');

    Route::post('/user/organisations/register', 'UserController@registerOrg');
    Route::get('/user/organisations/register', 'UserController@showOrgRegisterForm');

    Route::get('/user/searchGroups', 'UserController@searchGroups');

    Route::middleware('check.resources')->group(function () {
        Route::match(['get', 'post'], '/user/newsFeed/{filter?}/{objId?}', 'UserController@newsFeed');

        Route::get('/user/datasetView', 'UserController@datasetView')->name('datasetView');
        Route::post('/user/datasetDelete', 'UserController@datasetDelete');
        Route::match(['get', 'post'], '/user/datasets', 'UserController@datasets');
        Route::match(['get', 'post'], '/user/dataset/search', 'UserController@datasetSearch');
        Route::match(['get', 'post'], '/user/dataset/create', 'UserController@datasetCreate');
        Route::match(['get', 'post'], '/user/dataset/edit', 'UserController@datasetEdit')->name('datasetEdit');

        Route::get('/user/resourceView', 'UserController@resourceView')->name('resourceView');
        Route::match(['get', 'post'],'/user/invite', 'UserController@inviteUser');
        Route::match(['get', 'post'], '/user/settings', 'UserController@settings')->name('settings');
        Route::match(['get', 'post'],'/user/registerGroup', 'UserController@registerGroup');
        Route::match(['get', 'post'],'/user/userGroups', 'UserController@userGroups');
        Route::match(['get', 'post'],'/user/groupView/{uri}', 'UserController@groupView');
        Route::match(['get', 'post'],'/user/editGroup/{id}', 'UserController@editGroup');
        Route::post('/user/group/delete/{id}', 'UserController@deleteGroup');
        Route::match(['get', 'post'], '/user/groupDatasets', 'UserController@groupDatasets');


        Route::match(['get', 'post'], '/user/organisations/datasets', 'UserController@orgDatasets');
        Route::get('/user/organisations/datasets/resourceView', 'UserController@orgResourceView')->name('orgResourceView');
        Route::get('/user/organisations/datasetView', 'UserController@orgDatasetView')->name('orgDatasetView');


        Route::get('/user/organisations', 'UserController@organisations');
        Route::post('/user/organisations/delete', 'UserController@deleteOrg');
        Route::get('/user/organisations/search', 'UserController@searchOrg');
        Route::get('/user/organisations/view', 'UserController@viewOrg')->name('userOrgView');
        Route::post('/user/organisations/edit', 'UserController@editOrg');
        Route::post('/user/organisations/register', 'UserController@registerOrg');
        Route::get('/user/organisations/register', 'UserController@showOrgRegisterForm');
        Route::get('/user/organisations/datasets/search', 'UserController@searchDataset');

    });
});

Route::post('/user/sendTermsOfUseReq', 'UserController@sendTermsOfUseReq');
Route::get('/', function () {
    return view('home/index', ['class' => 'index']);
});

Route::get('/logout', function() {
    Auth::logout();

    return view('home/index', ['class' => 'index']);
});

Route::get('/preGenerated', 'UserController@preGenerated');

Route::match(['get', 'post'],'/login', 'Auth\LoginController@login')->name('login');

Route::match(['get', 'post'],'/registration', 'UserController@registration');
Route::match(['get', 'post'],'/orgRegistration', 'UserController@orgRegistration')->name('orgRegistration');

Route::match(['get', 'post'],'/confirmation', 'UserController@confirmation');
Route::match(['get', 'post'],'/mailConfirmation', 'UserController@mailConfirmation');

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

Route::match(['get', 'post'], '/user/newsFeed/{filter?}/{objId?}', 'UserController@newsFeed');

Route::get('/user/groups', function () {
    return view('user/groups', ['class' => 'user']);
});

Route::get('/user/groupView', function () {
    return view('user/groupView', ['class' => 'user']);
});

Route::get('/user/groupMembers', function () {
    return view('user/groupMembers', ['class' => 'user']);
});

Route::get('/user/orgMembers', function () {
    return view('user/orgMembers', ['class' => 'user']);
});

Route::match(['get', 'post'],'/password/forgotten', 'UserController@forgottenPassword');
Route::match(['get', 'post'],'/password/reset', 'UserController@passwordReset');

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

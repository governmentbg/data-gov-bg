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

Route::get('/', function () {
    return view('home/index', ['class' => 'index']);
});

Route::get('/logout', function() {
    Auth::logout();

    return view('home/index', ['class' => 'index']);
});

Route::get('/login', 'Auth\LoginController@login');
Route::post('/login', 'Auth\LoginController@login');

Route::get('/registration', 'UserController@registration');
Route::post('/registration', 'UserController@registration');

Route::get('/confirmation', 'HomeController@confirmation');

Route::get('/orgRegistration', 'UserController@orgRegistration');
Route::post('/orgRegistration', 'UserController@orgRegistration')->name('orgRegistration');

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

Route::get('/user/datasets', 'UserController@datasets');
Route::get('/user/datasetView/{uri}', 'UserController@datasetView');
Route::post('/user/deleteDataset', 'UserController@deleteDataset');
Route::match(['get', 'post'], '/user/createDataset', 'UserController@createDataset');

Route::get('/user/resourceView', function () {
    return view('user/resourceView', ['class' => 'user']);
});

Route::get('/user/organisations', function () {
    return view('user/organisations', ['class' => 'user']);
});

Route::get('/user/groups', function () {
    return view('user/groups', ['class' => 'user']);
});

Route::get('/user/organisations', 'UserController@organisations');

Route::post('/user/organisation/delete', 'UserController@deleteOrg');

Route::get('/user/organisations/search', 'UserController@searchOrg');

Route::get('/user/groupView', function () {
    return view('user/groupView', ['class' => 'user']);
});

Route::get('/user/groupMembers', function () {
    return view('user/groupMembers', ['class' => 'user']);
});

Route::get('/user/orgView', function () {
    return view('user/orgView', ['class' => 'user']);
});

Route::get('/user/orgMembers', function () {
    return view('user/orgMembers', ['class' => 'user']);
});

Route::get('/user/edit', function () {
    return view('user/edit', ['class' => 'user']);
});

Route::get('/user/settings', function () {
    return view('user/settings', ['class' => 'user']);
});

Route::get('/user/orgRegistration', function () {
    return view('user/orgRegistration', ['class' => 'user']);
});

Route::get('/user/groupRegistration', function () {
    return view('user/groupRegistration', ['class' => 'user']);
});

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

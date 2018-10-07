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

Route::get('/', function () {
    return view('welcome');
});

// ログインとかユーザー作成まわりのルート
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get ('/team/list',              'TeamController@index')     ->name('team.list');
Route::get ('/team/detail/{team}',     'TeamController@detail')    ->name('team.detail')    ->where('team', '[0-9]+');
Route::post('/team/detail/{team}',     'TeamController@update')    ->name('team.update')    ->where('team', '[0-9]+');
Route::post('/team/logo/{team}',       'TeamController@logoUpdate')->name('team.logoUpdate')->where('team', '[0-9]+');

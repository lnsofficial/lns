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

// とっぷぺ～じ
Route::get('/', function () {
    return view('welcome');
});

// ログインとかユーザー作成まわりのルート(を展開してる)
Auth::routes();

// ログイン後のホーム
Route::get('/home', 'HomeController@index')->name('home');

// ユーザー関連
Route::get ('/user/list',              'UserController@index')     ->name('user.list');
Route::get ('/user/detail/{user}',     'UserController@detail')    ->name('user.detail')    ->where('user', '[0-9]+');

// チーム関連
Route::get ('/team/list',              'TeamController@index')     ->name('team.list');
Route::get ('/team/detail/{team}',     'TeamController@detail')    ->name('team.detail')    ->where('team', '[0-9]+');
Route::post('/team/detail/{team}',     'TeamController@update')    ->name('team.update')    ->where('team', '[0-9]+');
Route::post('/team/logo/{team}',       'TeamController@logoUpdate')->name('team.logoUpdate')->where('team', '[0-9]+');
Route::post('/team/breakup/{team}',    'TeamController@breakup')   ->name('team.breakup')   ->where('team', '[0-9]+');

// マッチ関連
Route::get ('/match/list',             'MatchController@index')    ->name('match.list');

// お知らせ関連
Route::get ('/notice/list',            'NoticeController@index')   ->name('notice.list');

// 作業ログ関連
Route::get ('/worklog/list',           'WorklogController@index')  ->name('worklog.list');

// 管理ユーザー関連
Route::get ('/operator/list',                  'OperatorController@index') ->name('operator.list');
Route::get ('/operator/detail/{operator}',     'OperatorController@detail')->name('operator.detail')    ->where('operator', '[0-9]+');
Route::post('/operator/detail/{operator}',     'OperatorController@update')->name('operator.update')    ->where('operator', '[0-9]+');

// APIキュー関連
Route::get ('/queue/list',             'QueueController@index')    ->name('queue.list');
Route::get ('/queue/detail/{queue}',   'QueueController@detail')   ->name('queue.detail')    ->where('queue', '[0-9]+');
Route::post('/queue/detail/{queue}',   'QueueController@update')   ->name('queue.update')    ->where('queue', '[0-9]+');

<?php

use Illuminate\Support\Facades\Route;

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
Route::resource('groups', 'GroupController');
Route::resource('conversations', 'ConversationController');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::post('/userOnline/{id}','UserOnlineController@online');
Route::post('/userOffline/{id}','UserOnlineController@offline');
Route::get('/userOther','HomeController@getUserOther');

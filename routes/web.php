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

Auth::routes();

Route::get('/admin', 'Admin\UserController@loginForm');
Route::post('/admin/login','Admin\UserController@doLogin');
Route::get('/admin/home','Admin\AdminController@index');

Route::get('/admin/all_transactions','Admin\AdminController@all_transactions');

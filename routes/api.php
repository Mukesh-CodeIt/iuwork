<?php

use Illuminate\Http\Request;
use App\Http\Middleware\JwtAdminMiddleware;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', 'User\AuthController@register');
Route::post('login', 'User\AuthController@authenticate');

Route::group(['middleware' => ['jwt.auth']], function() {
  Route::get('logout', 'User\AuthController@logout');
  Route::get('get_user/{id}', 'User\UserController@get_user');
  Route::post('update_user/{id}', 'User\UserController@update_user');
  Route::put('delete_user/{id}', 'User\UserController@delete_user');
  Route::put('deactivate_user/{id}', 'User\UserController@deactivate_user');
  Route::put('activate_user/{id}', 'User\UserController@activate_user');
  Route::put('visible_user/{id}', 'User\UserController@visible_user');
  Route::put('invisible_user/{id}', 'User\UserController@invisible_user');
  Route::get('all_users', 'User\UserController@getAllUsers');
  Route::post('upload_user_profile_image', 'User\UserController@upload_user_profile_image');

  Route::get('all_skills', 'SkillController@all_skills');
  Route::get('get_skills_by_category/{id}', 'SkillController@get_skills_by_category');
  Route::post('store_skill', 'SkillController@store_skill');
  Route::get('get_skill/{id}', 'SkillController@get_skill');
  Route::put('update_skill/{id}', 'SkillController@update_skill');
  Route::put('delete_skill/{id}', 'SkillController@delete_skill');

  Route::get('all_categories', 'SkillController@all_categories');
  Route::post('store_category', 'SkillController@store_category');
  Route::get('get_category/{id}', 'SkillController@get_category');
  Route::put('update_category/{id}', 'SkillController@update_category');
  Route::put('delete_category/{id}', 'SkillController@delete_category');

  Route::get('all_jobs', 'JobController@all_jobs');
  Route::post('store_job', 'JobController@store_job');
  Route::put('update_job/{id}', 'JobController@update_job');
  Route::get('get_job/{id}', 'JobController@get_job');

  // Employee Profile
  Route::post('employee_apply_for_job', 'JobController@employee_apply_for_job');
  Route::get('find_work', 'JobController@find_work');
  Route::get('my_job_history', 'JobController@my_job_history');
  Route::get('job_started', 'JobController@job_started');
  Route::get('job_finished', 'JobController@job_finished');
  Route::get('my_earnings', 'JobController@my_earnings');
  Route::get('users_i_follow', 'User\UserController@users_i_follow');
  Route::get('users_i_block', 'User\UserController@users_i_block');
  Route::post('send_chat_message', 'User\UserController@send_chat_message');
  Route::get('get_messages', 'User\UserController@get_messages');
  Route::post('store_feedback', 'User\UserController@store_feedback');


  Route::post('follow_user', 'User\UserController@follow_user');
  Route::put('read_user_follower_notification/{id}', 'User\UserController@read_user_follower_notification');

  Route::post('block_user', 'User\UserController@block_user');
});

Route::group(['middleware' => ['App\Http\Middleware\JwtAdminMiddleware']], function() {

});

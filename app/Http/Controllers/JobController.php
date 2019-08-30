<?php

namespace App\Http\Controllers;

use App\User;
use DB;
use DateTime;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JobController extends Controller
{
  public function __construct(){
    $this->user = JWTAuth::parseToken()->authenticate();
  }

  public function store_job(Request $request){
    try {
      $validator = Validator::make($request->all(),
        [
          'job_title' => 'required|string|max:255',
          'pay_rate_per_hour' => 'required',
          'job_start_time' => 'required',
          'job_end_time' => 'required',
          'skill_id' => 'required'
        ]
      );

      if($validator->fails()){
        return response()->json($validator->errors());
      }
      else {
        // $start_time = new DateTime($request->job_start_time);
        // $end_time = new DateTime($request->job_end_time);
        // $diff  = date_diff($start_time, $end_time);
        // $duration = $diff->h." hours";

        $job_id = DB::table('jobs')->insertGetId([
          'job_title' => $request->job_title,
          'pay_rate_per_hour' => $request->pay_rate_per_hour,
          'job_start_time' => $request->job_start_time,
          'job_end_time' => $request->job_end_time,
          'job_posted_date' => date("Y-m-d H:i:s")
        ]);

        $job_skills = DB::table('job_skills')
        ->insert(['skill_id' => $request->skill_id, 'job_id' => $job_id]);

        $job_users = DB::table('user_jobs')
        ->insert(['user_id' => JWTAuth::user()->id, 'job_id' => $job_id]);

        return response()->json(['success' => true, 'message' => 'Job Posted Successfully', 'job_id' => $job_id, 'job_skills' => $job_skills, 'job_users' => $job_users], 200);
      }
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
  }

  public function update_job(Request $request, $id){
    try {
      $validator = Validator::make($request->all(),
        [
          'job_title' => 'required|string|max:255',
          'pay_rate_per_hour' => 'required',
          'job_start_time' => 'required',
          'job_end_time' => 'required',
          'skill_id' => 'required'
        ]
      );

      if($validator->fails()){
        return response()->json($validator->errors());
      }
      else {

        $job = DB::table('jobs')
        ->where('id','=',$id)
        ->update([
        'job_title' => $request->job_title,
        'pay_rate_per_hour' => $request->pay_rate_per_hour,
        'job_start_time' => $request->job_start_time,
        'job_end_time' => $request->job_end_time,
        'last_updated_by' => JWTAuth::user()->id,
        'last_updated_date' => date("Y-m-d H:i:s")
        ]);

        $job_skills_deleted = DB::table('job_skills')->where('job_id', $id)->delete();
        $job_skills_updated = DB::table('job_skills')
        ->insert(['skill_id' => $request->skill_id, 'job_id' => $id]);

        return response()->json(['success' => true, 'message' => 'Job Updated Successfully', 'job' => $job, 'job_skills_deleted' => $job_skills_deleted, 'job_skills_updated' => $job_skills_updated], 200);
      }
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
  }

  public function all_jobs(Request $request){
    try{
      $validator = Validator::make($request->all(),
        [
          'user_type' => 'required|string',
          'user_id' => 'required',
        ]
      );

      if($validator->fails()){
        return response()->json($validator->errors());
      }
      else {
        $user_type = $request->user_type;
        $user_id = $request->user_id;
        $jobs = DB::table('jobs')
        ->leftjoin('user_jobs', 'jobs.id', '=', 'user_jobs.job_id')
        ->leftjoin('users', 'user_jobs.user_id', '=', 'users.id')
        ->leftjoin('job_skills', 'jobs.id', '=', 'job_skills.job_id')
        ->leftjoin('skills', 'job_skills.skill_id', '=', 'skills.id')
        ->leftjoin('categories', 'skills.category_id', '=', 'categories.id')
        ->select(
          'jobs.id as job_id',
          'jobs.job_title as job_title',
          'jobs.job_status as job_status',
          'jobs.pay_rate_per_hour as job_pay_rate_per_hour',
          'jobs.job_start_time as job_start_time',
          'jobs.job_end_time as job_end_time',
          'jobs.job_posted_date as job_posted_date',
          'categories.id as category_id',
          'categories.category_name as category_name',
          'categories.category_status as category_status',
          'skills.id as skill_id',
          'skills.skill_name as skill_name',
          'skills.skill_status as skill_status',
          'users.id as user_id',
          'users.name as user_name',
          'users.line_manager_name as user_line_manager_name',
          'users.business_name as user_business_name',
          'users.address_1 as user_address_1',
          'users.address_2 as user_address_2',
          'users.geo_latitude as user_geo_latitude',
          'users.geo_longitude as user_geo_longitude'
        )
        ->where('jobs.job_status', '=', 'pending')
        ->where('users.type', '=', $user_type)
        ->where('users.id', '=', $user_id)
        ->orderBy('jobs.job_posted_date', 'DESC')
        ->get();

        foreach ($jobs as $key => $value) {
          $user_skills = DB::table('user_skills')
          ->leftjoin('users', 'user_skills.user_id', '=', 'users.id')
          ->leftjoin('skills', 'user_skills.skill_id', '=', 'skills.id')
          ->select('skills.id as skill_id', 'skills.skill_name as skill_name')
          ->where('users.id','=',$value->user_id)
          ->get();
          $value->user_skills = $user_skills;
        }


        return response()->json($jobs, 200);
      }
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
  }

  public function get_job($id){
    $job = DB::table('jobs')->where('id', '=', $id)->first();
    return response()->json(['success' => true, 'job' => $job], 200);
  }

  public function find_work(Request $request){
    try{
      $category_id = $request->category_id;
      $skill_id = $request->skill_id;
      $pay_rate_per_hour = $request->pay_rate_per_hour;
      $query = DB::table('jobs')
      ->leftjoin('user_jobs', 'jobs.id', '=', 'user_jobs.job_id')
      ->leftjoin('users', 'user_jobs.user_id', '=', 'users.id')
      ->leftjoin('job_skills', 'jobs.id', '=', 'job_skills.job_id')
      ->leftjoin('skills', 'job_skills.skill_id', '=', 'skills.id')
      ->leftjoin('categories', 'skills.category_id', '=', 'categories.id')
      ->select(
        'jobs.id as job_id',
        'jobs.job_title as job_title',
        'jobs.job_status as job_status',
        'jobs.pay_rate_per_hour as job_pay_rate_per_hour',
        'jobs.job_start_time as job_start_time',
        'jobs.job_end_time as job_end_time',
        'jobs.job_duration as job_duration',
        'jobs.job_posted_date as job_posted_date',
        'categories.id as category_id',
        'categories.category_name as category_name',
        'categories.category_status as category_status',
        'skills.id as skill_id',
        'skills.skill_name as skill_name',
        'skills.skill_status as skill_status',
        'users.id as user_id',
        'users.name as user_name',
        'users.line_manager_name as user_line_manager_name',
        'users.business_name as user_business_name',
        'users.address_1 as user_address_1',
        'users.address_2 as user_address_2',
        'users.geo_latitude as user_geo_latitude',
        'users.geo_longitude as user_geo_longitude'
      );

      if($category_id != ""){
        $query->where('categories.id', '=', $category_id);
      }
      if($skill_id != ""){
        $query->where('skills.id', '=', $skill_id);
      }
      if($pay_rate_per_hour != ""){
        $query->where('jobs.pay_rate_per_hour', '=', $pay_rate_per_hour);
      }


      $jobs = $query->where('jobs.job_status', '=', 'pending')
                    ->distinct()
                    ->orderBy('jobs.job_posted_date', 'DESC')
                    ->get();

      foreach ($jobs as $key => $value) {
        $user_skills = DB::table('user_skills')
        ->leftjoin('users', 'user_skills.user_id', '=', 'users.id')
        ->leftjoin('skills', 'user_skills.skill_id', '=', 'skills.id')
        ->select('skills.id as skill_id', 'skills.skill_name as skill_name')
        ->where('users.id','=',$value->user_id)
        ->get();
        $value->user_skills = $user_skills;
      }


      return response()->json($jobs, 200);
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
  }

  public function my_job_history(Request $request){
    try{
      $history_jobs = DB::table('jobs')
      ->leftjoin('user_jobs', 'jobs.id', '=', 'user_jobs.job_id')
      ->leftjoin('users', 'user_jobs.user_id', '=', 'users.id')
      ->select('jobs.*','users.id as user_id', 'users.name as user_name')
      ->where('jobs.job_status','!=', 'pending')
      ->where('users.id','=', $request->user_id)
      ->get();

      return response()->json($history_jobs, 200);
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
  }

  public function employee_apply_for_job(Request $request){
    try{
      $validator = Validator::make($request->all(),
        [
          'job_id' => 'required',
          'employee_user_id' => 'required',
          'employer_user_id' => 'required'
        ]
      );

      if($validator->fails()){
        return response()->json($validator->errors());
      }
      else {
        $job_history = DB::table('job_history')->insertGetId([
          'employer_user_id' => $request->employer_user_id,
          'employee_user_id' => $request->employee_user_id,
          'job_id' => $request->job_id,
          'employee_applied_date' => date("Y-m-d H:i:s"),
          'job_history_status' => 'applied',
          'created_date' => date("Y-m-d H:i:s")
        ]);

        return response()->json(['success' => true, 'message' => 'Applied for Job Successfully'], 200);
      }
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
  }

  // public function employer_accepted_job_request_from_employee(Request $request){
  //   try{
  //     $validator = Validator::make($request->all(),
  //       [
  //         'job_id' => 'required',
  //         'employee_user_id' => 'required',
  //         'employer_user_id' => 'required'
  //       ]
  //     );
  //
  //     if($validator->fails()){
  //       return response()->json($validator->errors());
  //     }
  //     else {
  //       $job_history = DB::table('job_history')->insertGetId([
  //         'employer_user_id' => $request->employer_user_id,
  //         'employee_user_id' => $request->employee_user_id,
  //         'job_id' => $request->job_id,
  //         'employee_applied_date' => date("Y-m-d H:i:s"),
  //         'job_history_status' => 'applied',
  //         'created_date' => date("Y-m-d H:i:s")
  //       ]);
  //
  //       return response()->json(['success' => true, 'message' => 'Applied for Job Successfully'], 200);
  //     }
  //   } catch (JWTException $e) {
  //       return response()->json(['error' => 'Could not create token'], 500);
  //   }
  // }




}

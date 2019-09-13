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

        $job_history = DB::table('job_history')
        ->insert(['employer_user_id' => JWTAuth::user()->id, 'job_id' => $job_id, 'created_date' => date("Y-m-d H:i:s")]);

        if($request->hasFile('image')){
          $files = $request->file('image');
          $result = $this->upload_job_files($job_id, $files);
          return response()->json(['uploaded' => $result, 'message' => 'Job Posted Successfully', 'job_id' => $job_id, 'job_skills' => $job_skills, 'job_history' => $job_history], 200);
        }else {
          return response()->json(['success' => true, 'message' => 'Job Posted Successfully', 'job_id' => $job_id, 'job_skills' => $job_skills, 'job_history' => $job_history], 200);
        }

      }
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
  }

  public function upload_job_files($job_id, $files) {
    try{
        foreach ($files as $key => $file) {
          if(!$file->isValid()) {
              return response()->json(['file_not_uploaded'], 400);
          }else {
            $upload = $file->store('uploads', 'public');
            $images_path[] = $upload;
            $filename = $file->getClientOriginalName();
            $images[] = $filename;

            $uploaded = DB::table('job_files')->insert([
              'job_id' => $job_id,
              'file_name' => $filename,
              'file_url' => $upload,
              'created_date' => date("Y-m-d H:i:s")
            ]);
          }
        }
        return response()->json(['success' => $uploaded],200);
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
  }

  public function store_job_application(Request $request){
    try {
      $validator = Validator::make($request->all(),
        [
          'employee_user_id' => 'required',
          'job_id' => 'required'
        ]
      );

      if($validator->fails()){
        return response()->json($validator->errors());
      }
      else {
        $job_application_check = DB::table('job_applications')->where('employee_user_id', '=', $request->employee_user_id)->where('job_id', '=', $request->job_id)->first();
        if($job_application_check){
          return response()->json(['success' => false, 'message' => 'Already applied for this job'], 400);
        }else {
          $user = User::find($request->employee_user_id);
          
          if($user->type == "employer" || $user->role_id == 1){
            return response()->json(['success' => false, 'message' => 'Only Employees can apply for this job'], 400);
          }else {
            $job_application = DB::table('job_applications')->insertGetId([
              'employee_user_id' => $request->employee_user_id,
              'job_id' => $request->job_id,
              'employee_applied_date' => date("Y-m-d H:i:s"),
              'created_date' => date("Y-m-d H:i:s")
            ]);
          }
        }
      }

      return response()->json(['success' => true, 'message' => 'Job Applied Successfully', 'job_application' => $job_application], 200);
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
  }

  public function get_job_applications(Request $request){
    try {
      $validator = Validator::make($request->all(),
        [
          'employer_user_id' => 'required'
        ]
      );

      if($validator->fails()){
        return response()->json($validator->errors());
      }
      else {
        $job_applications = DB::table('jobs')
        ->leftjoin('job_history', 'jobs.id', '=', 'job_history.job_id')
        ->leftjoin('job_applications', 'jobs.id', '=', 'job_applications.job_id')
        ->leftjoin('users as employer', 'job_history.employer_user_id', '=', 'employer.id')
        ->leftjoin('users as employee', 'job_applications.employee_user_id', '=', 'employee.id')
        ->select(
          'jobs.id as job_id',
          'jobs.job_title',
          'jobs.job_status as job_status',
          'employer.id as employer_user_id',
          'employer.name as employer_user_name',
          'employee.id as employee_user_id',
          'employee.name as employee_user_name',
          'job_applications.id as job_application_id',
          'job_applications.employee_applied_date',
          'job_applications.job_application_status',
          'job_history.job_history_status'
          )
        ->where('job_history.employer_user_id', '=', $request->employer_user_id)
        ->where('jobs.job_status', '=', 'pending')
        ->where('job_history.job_history_status', '=', null)
        ->where('job_applications.job_application_status', '=', 'applied')
        ->orderBy('job_applications.employee_applied_date', 'DESC')
        ->get()->toArray();
      }

      return response()->json($job_applications, 200);
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
  }

  public function update_job_application(Request $request){
    try {
      $validator = Validator::make($request->all(),
        [
          'job_application_id' => 'required'
        ]
      );

      if($validator->fails()){
        return response()->json($validator->errors());
      }
      else {
        $job_application = DB::table('job_applications')->where('id', '=', $request->job_application_id)->first();
        if($job_application){
          $job_application_updated = DB::table('job_applications')
          ->where('id','=',$job_application->id)
          ->update([
            'job_application_status' => 'accepted',
            'employer_accepted_date' => date("Y-m-d H:i:s"),
            'last_updated_by' => JWTAuth::user()->id,
            'last_updated_date' => date("Y-m-d H:i:s")
          ]);

          $job_history_updated = DB::table('job_history')
          ->where('job_id','=',$job_application->job_id)
          ->update([
            'job_history_status' => 'assigned',
            'employee_user_id' => $job_application->employee_user_id,
            'last_updated_by' => JWTAuth::user()->id,
            'last_updated_date' => date("Y-m-d H:i:s")
          ]);

          $job_updated = DB::table('jobs')
          ->where('id','=',$job_application->job_id)
          ->update([
            'job_status' => 'in-progress',
            'last_updated_by' => JWTAuth::user()->id,
            'last_updated_date' => date("Y-m-d H:i:s")
          ]);
        }else {
          return response()->json(['success' => false, 'message' => 'Not available'], 400);
        }
      }

      return response()->json(['success' => true, 'message' => 'Job application updated successfully', 'job_application_updated' => $job_application_updated, 'job_history_updated' => $job_history_updated, 'job_updated' => $job_updated], 200);
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
  }

  public function update_job_actual_started_time(Request $request){
    try {
      $validator = Validator::make($request->all(),
        [
          'job_id' => 'required'
        ]
      );

      if($validator->fails()){
        return response()->json($validator->errors());
      }
      else {
        $job = DB::table('job_history')->where('job_id', '=', $request->job_id)->first();

        if($job && $job->job_actual_started_date === null && $job->job_history_status === 'assigned'){
          $job_actual_started_date_updated = DB::table('job_history')
          ->where('job_id','=',$job->job_id)
          ->update([
            'job_actual_started_date' => date("Y-m-d H:i:s"),
            'last_updated_by' => JWTAuth::user()->id,
            'last_updated_date' => date("Y-m-d H:i:s")
          ]);
        }else {
          return response()->json(['success' => false, 'message' => 'Not available'], 400);
        }
      }

      return response()->json(['success' => true, 'message' => 'Job actual started date updated successfully', 'job_actual_started_date_updated' => $job_actual_started_date_updated], 200);
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
  }

  public function update_job_actual_finished_time(Request $request){
    try {
      $validator = Validator::make($request->all(),
        [
          'job_id' => 'required'
        ]
      );

      if($validator->fails()){
        return response()->json($validator->errors());
      }
      else {
        $job_data = DB::table('jobs')->where('id', '=', $request->job_id)->first();

        $start_time = new DateTime($job_data->job_start_time);
        $end_time = new DateTime($job_data->job_end_time);
        $diff  = date_diff($start_time, $end_time);
        $duration = $diff->h;

        $amount = $job_data->pay_rate_per_hour * $duration;
        $job = DB::table('job_history')->where('job_id', '=', $request->job_id)->first();

        $t=DB::table('transactions')
                ->where(function($query) use ($job){
                        $query->where('user_from_id',$job->employer_user_id)
                        ->whereIn('transaction_type',['withdrawn']);
                })->orWhere('user_to_id',$job->employee_user_id)
                ->whereIn('transaction_type',['deposited'])
                ->orderBy('transaction_date','desc')
                ->first();
        if($t!=""){
          $balance=$t->balance;
        }
        else{
          $balance=0;
        }

        if(($job && $job->job_history_status === 'assigned') && ($job->job_actual_started_date !== null && $job->job_actual_finished_date === null)){
          $job_actual_finished_date_updated = DB::table('job_history')
          ->where('job_id','=',$job->job_id)
          ->update([
            'job_actual_finished_date' => date("Y-m-d H:i:s"),
            'job_history_status' => 'delivered',
            'last_updated_by' => JWTAuth::user()->id,
            'last_updated_date' => date("Y-m-d H:i:s")
          ]);

          $job_status_updated = DB::table('jobs')
          ->where('id','=',$job->job_id)
          ->update([
            'job_status' => 'completed',
            'last_updated_by' => JWTAuth::user()->id,
            'last_updated_date' => date("Y-m-d H:i:s")
          ]);

          $transaction_id = DB::table('transactions')->insertGetId([
            'user_from_id' => $job->employer_user_id,
            'user_to_id' => $job->employee_user_id,
            'transaction_type' => 'deposited',
            'amount' => $amount,
            'balance' => $balance,
            'transaction_date' => date("Y-m-d H:i:s")
          ]);

          $transaction_job = DB::table('job_transactions')->insert([
            'job_id' => $job->job_id,
            'transaction_id' => $transaction_id,
            'created_date' => date("Y-m-d H:i:s")
          ]);

        }else {
          return response()->json(['success' => false, 'message' => 'Not available'], 400);
        }
      }

      return response()->json(['success' => true, 'message' => 'Job actual finished date updated successfully', 'job_actual_finished_date_updated' => $job_actual_finished_date_updated, 'job_status_updated' => $job_status_updated, 'transaction_id' => $transaction_id, 'transaction_job' => $transaction_job], 200);
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
          'users.image_name as user_image_name',
          'users.image_url as user_image_url',
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
          $user_average_rating = DB::table('feedbacks')
          ->select(DB::raw('AVG(total_ratings) as total_ratings'))
          ->where('feedbacks.user_to_id', '=', $value->user_id)
          ->get();
          $value->user_total_average_rating = round($user_average_rating[0]->total_ratings,1);

          $user_skills = DB::table('user_skills')
          ->leftjoin('users', 'user_skills.user_id', '=', 'users.id')
          ->leftjoin('skills', 'user_skills.skill_id', '=', 'skills.id')
          ->select('skills.id as skill_id', 'skills.skill_name as skill_name')
          ->where('users.id','=',$value->user_id)
          ->get();
          $value->user_skills = $user_skills;

          $job_files = DB::table('job_files')
          ->leftjoin('jobs', 'job_files.job_id', '=', 'jobs.id')
          ->select(
            'job_files.id',
            'job_files.job_id',
            'job_files.file_name',
            'job_files.file_url'
          )
          ->where('job_files.job_id', '=', $value->job_id)
          ->get();
          $value->job_files = $job_files;
        }

        return response()->json($jobs, 200);
      }
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
  }

  public function get_job($id){
    $job = DB::table('jobs')->where('id', '=', $id)->first();
    $job_files = DB::table('job_files')
    ->leftjoin('jobs', 'job_files.job_id', '=', 'jobs.id')
    ->select(
      'job_files.id',
      'job_files.job_id',
      'job_files.file_name',
      'job_files.file_url'
    )
    ->where('job_files.job_id', '=', $id)
    ->get();
    $job->job_files = $job_files;
    return response()->json(['success' => true, 'job' => $job], 200);
  }

  public function find_work(Request $request){
    try{
      $category_id = $request->category_id;
      $skill_id = $request->skill_id;
      $pay_rate_per_hour = $request->pay_rate_per_hour;
      $query = DB::table('jobs')
      ->leftjoin('job_history', 'jobs.id', '=', 'job_history.job_id')
      ->leftjoin('users as employer', 'job_history.employer_user_id', '=', 'employer.id')
      ->leftjoin('users as employee', 'job_history.employee_user_id', '=', 'employee.id')
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
        'employer.id as employer_user_id',
        'employer.name as employer_user_name',
        'employer.image_name as employer_user_image_name',
        'employer.image_url as employer_user_image_url',
        'employer.line_manager_name as employer_user_line_manager_name',
        'employer.business_name as employer_user_business_name',
        'employer.address_1 as employer_user_address_1',
        'employer.address_2 as employer_user_address_2',
        'employer.geo_latitude as employer_user_geo_latitude',
        'employer.geo_longitude as employer_user_geo_longitude',
        'employee.id as employee_user_id',
        'employee.name as employee_user_name',
        'employee.image_name as employee_user_image_name',
        'employee.image_url as employee_user_image_url',
        'employee.line_manager_name as employee_user_line_manager_name',
        'employee.business_name as employee_user_business_name',
        'employee.address_1 as employee_user_address_1',
        'employee.address_2 as employee_user_address_2',
        'employee.geo_latitude as employee_user_geo_latitude',
        'employee.geo_longitude as employee_user_geo_longitude'
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
                    ->get()->toArray();

      foreach ($jobs as $key => $value) {
        $user_average_rating = DB::table('feedbacks')
        ->select(DB::raw('AVG(total_ratings) as total_ratings'))
        ->where('feedbacks.user_to_id', '=', $value->employer_user_id)
        ->get();
        $value->employer_user_total_average_rating = round($user_average_rating[0]->total_ratings,1);

        $user_skills = DB::table('user_skills')
        ->leftjoin('users', 'user_skills.user_id', '=', 'users.id')
        ->leftjoin('skills', 'user_skills.skill_id', '=', 'skills.id')
        ->select('skills.id as skill_id', 'skills.skill_name as skill_name')
        ->where('users.id','=',$value->employer_user_id)
        ->get();
        $value->user_skills = $user_skills;

        $job_files = DB::table('job_files')
        ->leftjoin('jobs', 'job_files.job_id', '=', 'jobs.id')
        ->select(
          'job_files.id',
          'job_files.job_id',
          'job_files.file_name',
          'job_files.file_url'
        )
        ->where('job_files.job_id', '=', $value->job_id)
        ->get();
        $value->job_files = $job_files;
      }

      $blocked = DB::table('blocked_users')
      ->join('users', 'blocked_users.blocked_user_id', '=', 'users.id')
      ->select('users.id as user_id', 'users.name as user_name')
      ->where('block_by_user_id','=',$request->user_id)
      ->get()->toArray();

      $blocked_me = DB::table('blocked_users')
      ->leftjoin('users', 'blocked_users.block_by_user_id', '=', 'users.id')
      ->select('users.id as user_id', 'users.name as user_name')
      ->where('blocked_user_id','=',$request->user_id)
      ->get()->toArray();

      $merged = array_merge($blocked, $blocked_me);
      $result = $this->my_array_unique($merged);
      $users = array_values(array_sort($result, function ($value) {
        return $value->user_id;
      }));

      $tmpArray = array();
      if(count($users) > 0){
        foreach($jobs as $data1) {
          $duplicate = false;
          foreach($users as $data2) {
            if($data1->employer_user_id === $data2->user_id)
              $duplicate = true;
          }
          if($duplicate === false)
            $tmpArray[] = $data1;
        }
      }else {
        $tmpArray = $jobs;
      }

      $jobs = $tmpArray;
      return response()->json($jobs, 200);
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
  }

  public function my_array_unique($array, $keep_key_assoc = false){
    $duplicate_keys = array();
    $tmp = array();
    foreach ($array as $key => $val){
      if (is_object($val))
        $val = (array)$val;
      if (!in_array($val, $tmp))
        $tmp[] = $val;
      else
        $duplicate_keys[] = $key;
    }
    foreach ($duplicate_keys as $key)
      unset($array[$key]);

    return $keep_key_assoc ? $array : array_values($array);
  }

  public function my_job_history(Request $request){
    try{
      $history_jobs = DB::table('jobs')
      ->leftjoin('job_history', 'jobs.id', '=', 'job_history.job_id')
      ->leftjoin('users as employer', 'job_history.employer_user_id', '=', 'employer.id')
      ->leftjoin('users as employee', 'job_history.employee_user_id', '=', 'employee.id')
      ->select('jobs.*','users.id as user_id', 'users.name as user_name')
      ->where('jobs.job_status','!=', 'pending')
      ->whereIn('job_history.job_history_status','=', ['assigned','delivered'])
      ->where('employer.employer_user_id','=', $request->user_id)
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
          'employee_user_id' => 'required'
        ]
      );

      if($validator->fails()){
        return response()->json($validator->errors());
      }
      else {
        $job_history = DB::table('job_applications')->insertGetId([
          'employee_user_id' => $request->employee_user_id,
          'job_id' => $request->job_id,
          'employee_applied_date' => date("Y-m-d H:i:s"),
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

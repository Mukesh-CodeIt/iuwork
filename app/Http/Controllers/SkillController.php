<?php

namespace App\Http\Controllers;

use App\User;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class SkillController extends Controller
{
  public function __construct(){
    $this->user = JWTAuth::parseToken()->authenticate();
  }

  /////////////Skills section/////////

  public function all_skills(){

    $skills = DB::table('skills')
    ->leftjoin('categories', 'skills.category_id', '=', 'categories.id')
    ->select('skills.*', 'categories.category_name')
    ->get();

    return response()->json($skills,200);
  }

  public function get_skills_by_category($id){

    $skills = DB::table('skills')
    ->leftjoin('categories', 'skills.category_id', '=', 'categories.id')
    ->select('skills.*', 'categories.category_name')
    ->where('skills.category_id', '=', $id)
    ->get();

    return response()->json($skills,200);
  }

  public function store_skill(Request $request){

    try {
      $information = [
        'skill_name' => $request->skill_name,
        'category_id' => $request->category_id,
        'created_date' => date("Y-m-d H:i:s")
      ];
      $rules = [
        'skill_name' => 'required|max:255|unique:skills',
        'category_id' => 'required'
      ];

      $validator = Validator::make($information, $rules);
      if($validator->fails()){
        return response()->json($validator->errors());
      }else {
        DB::table('skills')->insert($information);
      }

      return response()->json(['success' => true, 'message' => 'Skill Inserted Successfully']);
    }
    catch(Exception $e) {
      return response()->json(['message' => $e->message()], 500);
    }
  }

  public function get_skill($id){

    $skill = DB::table('skills')
    ->where('id',$id)
    ->get();
    return response()->json(['skill' => $skill]);
  }

  public function update_skill(Request $request, $id){

    $validator = Validator::make($request->all(),
      [
        'skill_name' => 'required|max:255|unique:skills,skill_name,'.$id,
        'category_id' => 'required'
      ]
    );

    if($validator->fails()){
      return response()->json($validator->errors());
    }else {
      $category = DB::table('skills')
      ->where('id','=',$id)
      ->update(['skill_name' => $request->skill_name, 'category_id' => $request->category_id, 'last_updated_by' => JWTAuth::user()->id, 'last_updated_date' => date("Y-m-d H:i:s")]);
    }
    return response()->json(['success' => true,'message' => 'Skill Updated Successfully']);
  }

  public function delete_skill(Request $request, $id){

    $category = DB::table('skills')
      ->where('id','=',$id)
      ->update(['skill_status' => 'deactivated', 'last_updated_by' => JWTAuth::user()->id, 'last_updated_date' => date("Y-m-d H:i:s")]);
    return response()->json(['success' => true,'message' => 'Skill Deleted Successfully']);
  }



  /////////////Skills section end/////////

  /////////////Categories section/////////

  public function store_category(Request $request){

    try {
      $information = [
        'category_name' => $request->category_name,
        'created_date' => date("Y-m-d H:i:s")
      ];
      $rules = [
        'category_name' => 'required|max:255|unique:categories'
      ];

      $validator = Validator::make($information, $rules);
      if($validator->fails()){
        return response()->json($validator->errors());
      }else {
        DB::table('categories')->insert($information);
      }

      return response()->json(['success' => true, 'message' => 'Category Inserted Successfully']);
    }
    catch(Exception $e) {
      return response()->json(['message' => $e->message()], 500);
    }
  }

  public function all_categories(){

    $categories = DB::table('categories')
    ->get();
    return response()->json(['categories' => $categories]);
  }

  public function get_category($id){

    $category = DB::table('categories')
    ->where('id',$id)
    ->get();
    return response()->json(['category' => $category]);
  }

  public function update_category(Request $request, $id){

    $validator = Validator::make($request->all(),
      [
        'category_name' => 'required|max:255|unique:categories,category_name,'.$id
      ]
    );

    if($validator->fails()){
      return response()->json($validator->errors());
    }else {
      $category = DB::table('categories')
      ->where('id','=',$id)
      ->update(['category_name' => $request->category_name, 'last_updated_by' => JWTAuth::user()->id, 'last_updated_date' => date("Y-m-d H:i:s")]);
    }
    return response()->json(['success' => true,'message' => 'Category Updated Successfully']);
  }

  public function delete_category(Request $request, $id){

    $category = DB::table('categories')
      ->where('id','=',$id)
      ->update(['category_status' => 'deactivated', 'last_updated_by' => JWTAuth::user()->id, 'last_updated_date' => date("Y-m-d H:i:s")]);
    return response()->json(['success' => true,'message' => 'Category Deleted Successfully']);
  }

  /////////////Categories section end/////////

  function distance($lat1, $lon1, $lat2, $lon2, $unit) {
    if (($lat1 == $lat2) && ($lon1 == $lon2)) {
      return 0;
    }
    else {
      $theta = $lon1 - $lon2;
      $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
      $dist = acos($dist);
      $dist = rad2deg($dist);
      $miles = $dist * 60 * 1.1515;
      $unit = strtoupper($unit);

      if ($unit == "K") {
        return ($miles * 1.609344);
      } else if ($unit == "N") {
        return ($miles * 0.8684);
      } else {
        return $miles;
      }
    }
  }
}

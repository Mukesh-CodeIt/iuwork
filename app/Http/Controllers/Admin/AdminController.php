<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AdminController extends Controller
{
    public function getAllUsers(){
      if(JWTAuth::user()->role_id === 1){
        $users = User::all();
        return response()->json(['users' => $users]);
      }else {
        return response()->json(['message' => 'access_denied']);
      }
    }

    // public function edit_user($id){
    //   $user = User::find($id);
    //   return response()->json(['user' => $user]);
    // }

    // public function update_user(Request $request, $id){
    //   $request->validate([
    //     'name' => 'required|string|max:255',
    //     'email' => 'required|string|email|max:255|unique:users,email,'.$id,
    //     'password' => 'required|string|min:6',
    //   ]);
    //
    //   $user = User::find($id);
    //   $user->role_id = $request->role_id;
    //   $user->name = $request->name;
    //   $user->email = $request->email;
    //   $user->password = Hash::make($request->password);
    //   $user->gender = $request->gender;
    //   $user->image = $request->image;
    //   $user->status = $request->status;
    //   $user->type = $request->type;
    //   $user->visibility = $request->visibility;
    //   $user->address_1 = $request->address_1;
    //   $user->address_2 = $request->address_2;
    //   $user->geo_latitude = $request->geo_latitude;
    //   $user->geo_longitude = $request->geo_longitude;
    //   $user->city = $request->city;
    //   $user->country = $request->country;
    //   $user->post_zip_code = $request->post_zip_code;
    //   $user->personal_utr_number = $request->personal_utr_number;
    //   $user->national_insaurance_number = $request->national_insaurance_number;
    //   $user->phone_number = $request->phone_number;
    //   $user->business_name = $request->business_name;
    //   $user->line_manager_name = $request->line_manager_name;
    //   $user->personal_details = $request->personal_details;
    //   $user->date_of_birth = $request->date_of_birth;
    //   $user->save();
    //   return response()->json(['message' => 'Updated Successfully', 'user' => $user]);
    // }

    // public function delete_user($id){
    //   $user = User::find($id);
    //   $user->delete();
    //   return response()->json(['message' => 'Deleted Successfully']);
    // }
}

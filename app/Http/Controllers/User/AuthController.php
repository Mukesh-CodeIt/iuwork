<?php

namespace App\Http\Controllers\User;

use App\User;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{

  public function register(Request $request){
    try{
          $name = $request->name;
          $email = $request->email;
          $password = $request->password;
          $gender = $request->gender;
          $type = $request->type;
          $bank_name = $request->bank_name;
          $account_holder_name = $request->account_holder_name;
          $sort_code = $request->sort_code;
          $account_number = $request->account_number;
          $agree_checkbox = $request->agree_checkbox;
          $skill_id = $request->skill_id;
          $address_1 = $request->address_1;
          $address_2 = $request->address_2;
          $city = $request->city;
          $country = $request->country;
          $geo_latitude = $request->geo_latitude;
          $geo_longitude = $request->geo_longitude;
          $post_zip_code = $request->post_zip_code;
          $national_insaurance_number = $request->national_insaurance_number;
          $personal_utr_number = $request->personal_utr_number;
          $phone_number = $request->phone_number;
          $business_name = $request->business_name;
          $line_manager_name = $request->line_manager_name;
          $personal_details = $request->personal_details;
          $date_of_birth = $request->date_of_birth;

          $validator = Validator::make($request->all(),
            [
              'name' => 'required|string|max:255',
              'email' => 'required|string|email|max:255|unique:users',
              'password' => 'required|string|min:6|confirmed',
              'bank_name' => 'required|max:255',
              'account_holder_name' => 'required|max:255',
              'sort_code' => 'required|max:255|',
              'account_number' => 'required|max:255|unique:bank_details',
              'skill_id' => 'required|array|min:1|max:3|between:1,3',
              'address_1' => 'required',
              'address_2' => 'required',
              'city' => 'required',
              'country' => 'required',
              'post_zip_code' => 'required',
              'personal_details' => 'required',
              'phone_number' => 'required|unique:users'
            ]
          );

          if($validator->fails()){
            return response()->json($validator->errors());
          }
          else {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'type' => $type,
                'gender' => $gender,
                'address_1' => $address_1,
                'address_2' => $address_2,
                'city' => $city,
                'country' => $country,
                'post_zip_code' => $post_zip_code,
                'national_insaurance_number' => $national_insaurance_number,
                'personal_utr_number' => $personal_utr_number,
                'business_name' => $business_name,
                'line_manager_name' => $line_manager_name,
                'phone_number' => $phone_number,
                'personal_details' => $personal_details,
                'date_of_birth' => $date_of_birth,
                'registration_date' => date("Y-m-d H:i:s")
              ]);

            $bank_details = DB::table('bank_details')->insert([
              'user_id' => $user->id,
              'bank_name' => $bank_name,
              'account_holder_name' => $account_holder_name,
              'sort_code' => $sort_code,
              'account_number' => $account_number,
              'created_date' => date("Y-m-d H:i:s")
            ]);

            if(is_array($skill_id) && count($skill_id) > 0){
              for($i=0; $i<count($skill_id); $i++){
                $user_skills = DB::table('user_skills')
                      ->insert(['skill_id' => $skill_id[$i], 'user_id' => $user->id]);
              }
            }else {
              return response()->json('Business Industry Details are incomplete',400);
            }

            $token = JWTAuth::fromUser($user);
            return response()->json(['success' => true, 'user' => $user, 'bank_detals' => $bank_details, 'user_skills' => $user_skills, 'token' => $token],200);
          }

      } catch (JWTException $e) {
          return response()->json(['error' => 'Could not create token'], 500);
      }



    // $distance = $this->distance($request->geo_latitude1, $request->geo_longitude1, $request->geo_latitude2, $request->geo_longitude2, "K");
    // $api_key = 'AIzaSyAfHjDvdgpXe4FuDKPY3euRM-ldTsUxKdI';
    // $currentaddress = '9th A St, Bath Island, Karachi, Karachi City, Sindh, Pakistan';
    // $from = '204 Shahrah-e-Faisal Rd, Sindhi Muslim Cooperative Housing Society Block A Sindhi Muslim CHS (SMCHS), Karachi, Karachi City, Sindh, Pakistan';
    // $remFrom = str_replace(' ', '+', $from); //Remove Commas
    // $from = urlencode($remFrom);
    // $to = $currentaddress;
    // $remTo = str_replace(' ', '+', $to); //Remove Commas
    // $to = urlencode($remTo);
    // $data = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?origins=$from&destinations=$to&language=en-EN&sensor=false&key=$api_key");
    // $data = json_decode($data,true);

    // return response()->json(['success' => true,'distance' => $data]);
  }

  public function authenticate(Request $request) {
    $credentials = $request->only('email', 'password');
    $credentials['status'] = 'activated';
    $credentials['is_deleted'] = 0;
    try {
        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json([
              'success' => false,
              'message' => 'Unauthorized'
            ], 401);
        }

    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }

    return response()->json([
      'success' => true,
      'token' => $token
    ], 200);
  }

  public function getAuthenticatedUser() {
    $user = User::find(JWTAuth::user()->id);
    return response([
        'status' => 'success',
        'data' => $user
    ]);
  }

  public function logout(Request $request) {

    $this->validate($request, [
      'token' => 'required'
    ]);

    try {
      JWTAuth::invalidate($request->token);

      return response()->json([
        'success' => true,
        'message' => 'User logged out successfully'
      ], 200);

    } catch(JWTException $e) {
      return response()->json([
        'success' => false,
        'message' => 'Sorry, the user cannot be logged out'
      ], 500);
    }
  }
}

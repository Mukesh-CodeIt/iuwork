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
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
    {

      public function __construct(){
        $this->user = JWTAuth::parseToken()->authenticate();
      }

      public function update_user(Request $request, $id){
        try{

            $bank_detail = DB::table('bank_details')
            ->where('user_id','=',$id)->first();

            $validator = Validator::make($request->all(),
              [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,'.$id,
                'password' => 'required|string|min:6',
                'bank_name' => 'required|max:255',
                'account_holder_name' => 'required|max:255',
                'sort_code' => 'required|max:255|',
                'account_number' => 'required|max:255|unique:bank_details,account_number,'.$bank_detail->id,
                'skill_id' => 'required|array|min:1|max:3|between:1,3',
                'address_1' => 'required',
                'address_2' => 'required',
                'city' => 'required',
                'country' => 'required',
                'post_zip_code' => 'required',
                'personal_details' => 'required',
                'phone_number' => 'required|unique:users,phone_number,'.$id
              ]
            );

            if($validator->fails()){
              return response()->json($validator->errors());
            }
            else {
              $user = DB::table('users')
              ->where('id','=',$id)
              ->update([
                'role_id' => $request->role_id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'gender' => $request->gender,
                'status' => $request->status,
                'type' => $request->type,
                'visibility' => $request->visibility,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'city' => $request->city,
                'country' => $request->country,
                'post_zip_code' => $request->post_zip_code,
                'personal_utr_number' => $request->personal_utr_number,
                'national_insaurance_number' => $request->national_insaurance_number,
                'phone_number' => $request->phone_number,
                'business_name' => $request->business_name,
                'line_manager_name' => $request->line_manager_name,
                'personal_details' => $request->personal_details,
                'date_of_birth' => $request->date_of_birth,
                'last_updated_by' => JWTAuth::user()->id,
                'last_updated_date' => date("Y-m-d H:i:s")
              ]);

              $bank_details = DB::table('bank_details')
              ->where('user_id','=',$id)
              ->update([
                'bank_name' => $request->bank_name,
                'account_holder_name' => $request->account_holder_name,
                'sort_code' => $request->sort_code,
                'account_number' => $request->account_number,
                'last_updated_by' => JWTAuth::user()->id,
                'last_updated_date' => date("Y-m-d H:i:s")
              ]);

              if(is_array($request->skill_id) && count($request->skill_id) > 0){
                $user_skills_deleted = DB::table('user_skills')->where('user_id', $id)->delete();
                for($i=0; $i<count($request->skill_id); $i++){
                  $user_skills_updated = DB::table('user_skills')
                        ->insert(['skill_id' => $request->skill_id[$i], 'user_id' => $id]);
                }
              }else {
                return response()->json('Business Industry Details are incomplete',400);
              }

              return response()->json(['success' => true, 'message' => 'Updated Successfully', 'user' => $user, 'bank_detals' => $bank_details, 'user_skills_updated' => $user_skills_updated],200);
            }
          } catch (JWTException $e) {
              return response()->json(['error' => 'Could not create token'], 500);
          }

        // if(JWTAuth::user()->id != $id && JWTAuth::user()->role_id === 1){
        //   $user->save();
        //   return response()->json(['message' => 'Updated Successfully', 'user' => $user]);
        // }
        //
        // if(JWTAuth::user()->id == $id && JWTAuth::user()->role_id === 1){
        //   $user->save();
        //   return response()->json(['message' => 'Updated Successfully', 'user' => $user]);
        // }
        //
        // if(JWTAuth::user()->id != $id && JWTAuth::user()->role_id === 2){
        //   return response()->json(['message' => 'Access Denied']);
        // }
        //
        // if(JWTAuth::user()->id == $id && JWTAuth::user()->role_id === 2)
        // {
        //   $user->save();
        //   return response()->json(['message' => 'Updated Successfully', 'user' => $user]);
        // }
      }

      public function upload_user_profile_image(Request $request){
        try{

          $validator = Validator::make($request->all(),
            [
              'user_id' => 'required',
              'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg'
            ]
          );

          if($validator->fails()){
            return response()->json($validator->errors());
          }
          else {
            $file = $request->file('image');
            if(!$request->hasFile('image') && !$file->isValid()) {
                return response()->json(['uploaded_file_has_issue'], 400);
            }else {
              $upload = $file->store('profile_images', 'public');
              $filename = $file->getClientOriginalName();

              $user = User::find($request->user_id);
              $user->image_name = $filename;
              $user->image_url = $upload;
              $user->last_updated_by = JWTAuth::user()->id;
              $user->last_updated_date = date("Y-m-d H:i:s");
              $user->save();
            }
            return response()->json(['message' => 'Profile Image Uploaded Successfully', 'user' => $user], 200);
          }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
      }

      public function get_user($id){
        $user = User::find($id);
        if(JWTAuth::user()->id != $id && JWTAuth::user()->role_id === 1){
          return response()->json(['user' => $user]);
        }

        if(JWTAuth::user()->id == $id && JWTAuth::user()->role_id === 1){
          return response()->json(['user' => $user]);
        }

        if(JWTAuth::user()->id != $id && JWTAuth::user()->role_id === 2){
          return response()->json(['message' => 'Access Denied']);
        }

        if(JWTAuth::user()->id == $id && JWTAuth::user()->role_id === 2)
        {
          return response()->json(['user' => $user]);
        }
      }

      public function delete_user($id){
        $user = User::find($id);
        $user->is_deleted = true;
        if(JWTAuth::user()->id != $id && JWTAuth::user()->role_id === 1){
          $user->save();
          return response()->json(['message' => 'Deleted Successfully']);
        }

        if(JWTAuth::user()->id == $id && JWTAuth::user()->role_id === 1){
          return response()->json(['message' => 'Access Denied']);
        }

        if(JWTAuth::user()->id != $id && JWTAuth::user()->role_id === 2){
          return response()->json(['message' => 'Access Denied']);
        }

        if(JWTAuth::user()->id == $id && JWTAuth::user()->role_id === 2)
        {
          return response()->json(['message' => 'Access Denied']);
        }
      }

      public function deactivate_user($id){
        $user = User::find($id);
        $user->status = 'deactivated';
        if(JWTAuth::user()->id != $id && JWTAuth::user()->role_id === 1){
          $user->save();
          return response()->json(['message' => 'Deactivated Successfully']);
        }

        if(JWTAuth::user()->id == $id && JWTAuth::user()->role_id === 1){
          return response()->json(['message' => 'Access Denied']);
        }

        if(JWTAuth::user()->id != $id && JWTAuth::user()->role_id === 2){
          return response()->json(['message' => 'Access Denied']);
        }

        if(JWTAuth::user()->id == $id && JWTAuth::user()->role_id === 2){
          return response()->json(['message' => 'Access Denied']);
        }
      }

      public function activate_user($id){
        $user = User::find($id);
        $user->status = 'activated';
        if(JWTAuth::user()->id != $id && JWTAuth::user()->role_id === 1){
          $user->save();
          return response()->json(['message' => 'Activated Successfully']);
        }

        if(JWTAuth::user()->id == $id && JWTAuth::user()->role_id === 1){
          return response()->json(['message' => 'Access Denied']);
        }

        if(JWTAuth::user()->id != $id && JWTAuth::user()->role_id === 2){
          return response()->json(['message' => 'Access Denied']);
        }

        if(JWTAuth::user()->id == $id && JWTAuth::user()->role_id === 2){
          return response()->json(['message' => 'Access Denied']);
        }
      }

      public function visible_user($id){
        $user = User::find($id);
        $user->visibility = 'visible';
        if(JWTAuth::user()->id != $id && JWTAuth::user()->role_id === 1){
          $user->save();
          return response()->json(['message' => 'Visible Successfully']);
        }

        if(JWTAuth::user()->id == $id && JWTAuth::user()->role_id === 1){
          $user->save();
          return response()->json(['message' => 'Visible Successfully']);
        }

        if(JWTAuth::user()->id != $id && JWTAuth::user()->role_id === 2){
          return response()->json(['message' => 'Access Denied']);
        }

        if(JWTAuth::user()->id == $id && JWTAuth::user()->role_id === 2){
          $user->save();
          return response()->json(['message' => 'Visible Successfully']);
        }
      }

      public function invisible_user($id){
        $user = User::find($id);
        $user->visibility = 'invisible';
        if(JWTAuth::user()->id != $id && JWTAuth::user()->role_id === 1){
          $user->save();
          return response()->json(['message' => 'Invisible Successfully']);
        }

        if(JWTAuth::user()->id == $id && JWTAuth::user()->role_id === 1){
          $user->save();
          return response()->json(['message' => 'Invisible Successfully']);
        }

        if(JWTAuth::user()->id != $id && JWTAuth::user()->role_id === 2){
          return response()->json(['message' => 'Access Denied']);
        }

        if(JWTAuth::user()->id == $id && JWTAuth::user()->role_id === 2){
          $user->save();
          return response()->json(['message' => 'Invisible Successfully']);
        }
      }

      public function getAllUsers(){
        if(JWTAuth::user()->role_id === 1){
          $users = User::all();
          return response()->json(['users' => $users]);
        }else {
          return response()->json(['message' => 'access_denied']);
        }
      }

      public function follow_user(Request $request){
        try {
          $follower = User::find($request->follower_user_id);
          $followed = User::find($request->followed_user_id);

          $validator = Validator::make($request->all(),
            [
              'follower_user_id' => 'required',
              'followed_user_id' => 'required'
            ]
          );

          if($validator->fails()){
            return response()->json($validator->errors());
          }
          else {
            $user_follower = DB::table('user_followers')->insert([
              'follower_user_id' => $request->follower_user_id,
              'followed_user_id' => $request->followed_user_id,
              'follow_date' => date("Y-m-d H:i:s")
            ]);

            $follow_notification = DB::table('notifications')->insert([
              'notify_user_id' => $request->followed_user_id,
              'data' => 'You have been followed by ' .ucfirst(strtolower($follower->name)).' '.ucfirst(strtolower($follower->type)),
              'notify_status' => 'delivered',
              'notify_type' => 'Users Follower',
              'notify_date' => date("Y-m-d H:i:s")
            ]);

            return response()->json(['success' => true, 'message' => 'Followed Successfully', 'user_follower' => $user_follower, 'follow_notification' => $follow_notification], 200);
          }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
      }

      public function block_user(Request $request){
        try {
          $block_by = User::find($request->block_by_user_id);
          $blocked = User::find($request->blocked_user_id);

          $validator = Validator::make($request->all(),
            [
              'block_by_user_id' => 'required',
              'blocked_user_id' => 'required'
            ]
          );

          if($validator->fails()){
            return response()->json($validator->errors());
          }
          else {
            $user_block = DB::table('blocked_users')->insert([
              'block_by_user_id' => $request->block_by_user_id,
              'blocked_user_id' => $request->blocked_user_id,
              'blocked_date' => date("Y-m-d H:i:s")
            ]);

            $user_unfollow_each = DB::table('user_followers')->where('followed_user_id','=',$request->block_by_user_id)->where('follower_user_id','=',$request->blocked_user_id)->delete();
            $user_unfollow_other = DB::table('user_followers')->where('followed_user_id','=',$request->blocked_user_id)->where('follower_user_id','=',$request->block_by_user_id)->delete();

            // $block_notification = DB::table('notifications')->insert([
            //   'notify_user_id' => $request->blocked_user_id,
            //   'data' => 'You have been blocked by ' .ucfirst(strtolower($block_by->name)).' '.ucfirst(strtolower($block_by->type)),
            //   'notify_status' => 'delivered',
            //   'notify_type' => 'Users Block',
            //   'notify_date' => date("Y-m-d H:i:s")
            // ]);

            return response()->json([
              'success' => true,
              'message' => 'Blocked Successfully',
              'user_block' => $user_block,
              // 'block_notification' => $block_notification,
              'user_unfollow_each' => $user_unfollow_each,
              'user_unfollow_other' => $user_unfollow_other
            ], 200);
          }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
      }

      public function read_user_follower_notification(Request $request,$id){
        try {
          $read_notification = DB::table('notifications')
            ->where('id','=',$id)
            ->update(['notify_status' => 'read']);
          return response()->json(['success' => true, 'message' => 'Notification Read Successfully', 'read_notification' => $read_notification], 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
      }

      public function users_i_follow(Request $request){
        try {
          $following = DB::table('user_followers')
          ->join('users', 'user_followers.followed_user_id', '=', 'users.id')
          ->select('users.id as user_id', 'users.name as user_name')
          ->where('follower_user_id','=',$request->user_id)
          ->get();

          return response()->json(['success' => true, 'following' => $following], 200);

        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
      }

      public function users_i_block(Request $request){
        try {
          $blocked = DB::table('blocked_users')
          ->join('users', 'blocked_users.blocked_user_id', '=', 'users.id')
          ->select('users.id as user_id', 'users.name as user_name')
          ->where('block_by_user_id','=',$request->user_id)
          ->get();

          return response()->json(['success' => true, 'blocked' => $blocked], 200);

        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
      }

      public function send_chat_message(Request $request){
        try {
          $validator = Validator::make($request->all(),
            [
              'user_from_id' => 'required',
              'user_to_id' => 'required'
            ]
          );

          if($validator->fails()){
            return response()->json($validator->errors());
          }
          else {
              if(!$request->hasFile('image') && $request->message_text == ""){
                return response()->json(['success' => false, 'message' => 'please insert some text or file to send message'],400);
              }
              $message_id = DB::table('messages')->insertGetId([
                'user_from_id' => $request->user_from_id,
                'user_to_id' => $request->user_to_id,
                'message_text' => $request->message_text,
                'message_date' => date("Y-m-d H:i:s")
              ]);
              if($request->hasFile('image')){
                $files = $request->file('image');
                $result = $this->upload_message_files($message_id, $files);
                return response()->json(['uploaded' => $result, 'message_id' => $message_id, 'message_text' => $request->message_text],200);
              }else {
                return response()->json(['success' => true, 'message_id' => $message_id, 'message_text' => $request->message_text],200);
              }
          }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
      }

      public function upload_message_files($message_id, $files) {
        try{
            foreach ($files as $key => $file) {
              if(!$file->isValid()) {
                  return response()->json(['file_not_uploaded'], 400);
              }else {
                $upload = $file->store('uploads', 'public');
                $images_path[] = $upload;
                $filename = $file->getClientOriginalName();
                $images[] = $filename;

                $uploaded = DB::table('message_files')->insert([
                  'message_id' => $message_id,
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

      public function get_messages(Request $request) {
        try {
          $validator = Validator::make($request->all(),
            [
              'user_from_id' => 'required',
              'user_to_id' => 'required'
            ]
          );

          if($validator->fails()){
            return response()->json($validator->errors());
          }
          else {
            $sender = DB::table('messages')
            ->leftjoin('users as user_from','messages.user_from_id', '=', 'user_from.id')
            ->leftjoin('users as user_to','messages.user_to_id', '=', 'user_to.id')
            ->select(
              'messages.id',
              'messages.message_text',
              'messages.user_from_id',
              'messages.user_to_id',
              'messages.message_date',
              'user_from.name as user_from_message',
              'user_to.name as user_to_message'
            )
            ->where('messages.user_from_id', '=', $request->user_from_id)
            ->where('messages.user_to_id', '=', $request->user_to_id)
            ->get()->toArray();

            foreach ($sender as $key => $value) {
              $message_files = DB::table('message_files')
              ->leftjoin('messages', 'message_files.message_id', '=', 'messages.id')
              ->leftjoin('users as user_from','messages.user_from_id', '=', 'user_from.id')
              ->leftjoin('users as user_to','messages.user_to_id', '=', 'user_to.id')
              ->select(
                'message_files.id',
                'message_files.message_id',
                'message_files.file_name',
                'message_files.file_url'
              )
              ->where('message_files.message_id', '=', $value->id)
              ->get();
              $value->message_files = $message_files;
            }

            $receiver = DB::table('messages')
            ->leftjoin('users as user_from','messages.user_from_id', '=', 'user_from.id')
            ->leftjoin('users as user_to','messages.user_to_id', '=', 'user_to.id')
            ->select(
              'messages.id',
              'messages.message_text',
              'messages.user_from_id',
              'messages.user_to_id',
              'messages.message_date',
              'user_from.name as user_from_message',
              'user_to.name as user_to_message'
            )
            ->where('messages.user_from_id', '=', $request->user_to_id)
            ->where('messages.user_to_id', '=', $request->user_from_id)
            ->get()->toArray();

            foreach ($receiver as $key => $value) {
              $message_files = DB::table('message_files')
              ->leftjoin('messages', 'message_files.message_id', '=', 'messages.id')
              ->leftjoin('users as user_from','messages.user_from_id', '=', 'user_from.id')
              ->leftjoin('users as user_to','messages.user_to_id', '=', 'user_to.id')
              ->select(
                'message_files.id',
                'message_files.message_id',
                'message_files.file_name',
                'message_files.file_url'
              )
              ->where('message_files.message_id', '=', $value->id)
              ->get();
              $value->message_files = $message_files;
            }


            $result = array_merge($sender, $receiver);

            $array = array_reverse(array_values(array_sort($result, function ($value) {
              return $value->id;
            })));

          }
          return response()->json($array,200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
      }
    }

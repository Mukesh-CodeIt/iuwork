<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
// use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Redirect;
use DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AdminController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){
      return view('admins.home');
    }

    // protected function user() {
    //   return JWTAuth::parseToken()->authenticate();
    // }

    public function all_transactions(){
      $transactions = DB::table('transactions')
                      ->leftjoin('users as users_from', 'transactions.user_from_id', '=', 'users_from.id')
                      ->leftjoin('users as users_to', 'transactions.user_to_id', '=', 'users_to.id')
                      ->select(
                        'transactions.id as transaction_id',
                        'transactions.amount',
                        'transactions.balance',
                        'transactions.transaction_status',
                        'transactions.transaction_type',
                        'transactions.transaction_date',
                        'users_from.id as user_from_id',
                        'users_from.name as user_from_name',
                        'users_to.id as user_to_id',
                        'users_to.name as user_to_name'
                        )
                      ->orderBy('transaction_date', 'DESC')->get();
      return view('admins.transactions', compact('transactions'));
    }

    // public function all_users(){
    //   $dd = $this->user();
    //   dd($dd);
    //   $request = Request::create('/api/all_users', 'GET');
    //   $response = Route::dispatch($request);
    //   dd($response);
    // }

    public function getAllUsers(){
      if(JWTAuth::user()->role_id === 1){
        $users = User::all();
        return response()->json(['users' => $users]);
      }else {
        return response()->json(['message' => 'access_denied']);
      }
    }

    public function approve_transaction(Request $request){
      $transaction_id = $request->transaction_id;
      $transaction = DB::table('transactions')->where('id', '=', $transaction_id)->first();

      if($transaction->transaction_type == "deposited"){
        $user=$transaction->user_to_id;
      }
      else if($transaction->transaction_type == "withdrawn"){
        $user=$transaction->user_from_id;
      }

      $t=DB::table('transactions')
            ->where(function($query) use ($user){
                    $query->where('user_from_id',$user)
                    ->whereIn('transaction_type',['withdrawn']);
            })->orWhere('user_to_id',$user)
            ->whereIn('transaction_type',['deposited'])
            ->orderBy('transaction_date','desc')
            ->first();

      if($t!=""){
        $balance=$t->balance;
      }
      else{
        $balance=0;
      }

      if($transaction->transaction_type == "deposited"){
    		$balance = $balance + $transaction->amount;
    	}
    	else if($transaction->transaction_type == "withdrawn"){
		    $balance = $balance - $transaction->amount;
    	}

      if($transaction->transaction_status != "completed"){
        $transaction_updated = DB::table('transactions')
        ->where('id','=',$transaction->id)
        ->update([
          'balance' => $balance,
          'transaction_status' => 'completed',
          'last_updated_by' => JWTAuth::user()->id,
          'last_updated_date' => date("Y-m-d H:i:s")
        ]);
      }

      return redirect()->back();
    }

    public function decline_transaction(Request $request){
      $transaction_id = $request->transaction_id;
      $transaction = DB::table('transactions')->where('id', '=', $transaction_id)->first();

      if($transaction->transaction_type == "deposited"){
        $user=$transaction->user_to_id;
      }
      else if($transaction->transaction_type == "withdrawn"){
        $user=$transaction->user_from_id;
      }

      $t=DB::table('transactions')
            ->where(function($query) use ($user){
                    $query->where('user_from_id',$user)
                    ->whereIn('transaction_type',['withdrawn']);
            })->orWhere('user_to_id',$user)
            ->whereIn('transaction_type',['deposited'])
            ->orderBy('transaction_date','desc')
            ->first();

      if($t!=""){
        $balance=$t->balance;
      }
      else{
        $balance=0;
      }

      if($transaction->transaction_type == "deposited"){
    		$balance = $balance + $transaction->amount;
    	}
    	else if($transaction->transaction_type == "withdrawn"){
		    $balance = $balance - $transaction->amount;
    	}

      if($transaction->transaction_status != "completed"){
        $transaction_updated = DB::table('transactions')
        ->where('id','=',$transaction->id)
        ->update([
          'balance' => $balance,
          'transaction_status' => 'declined',
          'last_updated_by' => JWTAuth::user()->id,
          'last_updated_date' => date("Y-m-d H:i:s")
        ]);
      }
      return redirect()->back();
    }



    // public function doLogin(Request $request)
    // {
    //     $rules = array(
    //         'email'    => 'required|string|email', // make sure the email is an actual email
    //         'password'    => 'required|min:6' // password can only be alphanumeric and has to be greater than 3 characters
    //     );
    //     // run the validation rules on the inputs from the form
    //     $validator = Validator::make($request->all(), $rules);
    //
    //     // if the validator fails, redirect back to the form
    //     if ($validator->fails()) {
    //         return Redirect::to('/admin')
    //             ->withErrors($validator)
    //             ->withInput(Input::except('password'));
    //     }
    //     else {
    //
    //         $username      = Input::get('email');
    //         $password      = Input::get('password');
    //         $password      = Hash::make($password);
    //
    //         $user_detail = DB::table('users')
    //             ->select()
    //             ->where('username', '=', $username)
    //             ->first();
    //         if(!$user_detail)
    //         {
    //             return view('/admin/login')->with('login','Invalid username / password');
    //         }
    //         else
    //         {
    //             $hash1 = $user_detail->password; // A hash is generated
    //             $hash2 = Hash::make(Input::get('password'));
    //             $password_check = Hash::check(Input::get('password'), $hash1) && Hash::check(Input::get('password'), $hash2);
    //             if($password_check === false){
    //                 return view('/admin/login')->with('login','Invalid username / password');
    //             }
    //             else {
    //                 $user = DB::table('user_system')
    //                     ->select()
    //                     ->where('username', '=', $username)
    //                     ->where('isactive',"=",1)
    //                     ->first();
    //                 if(!$user_detail) {
    //                     return view('/admin/login')->with('login','Invalid username / password');
    //                 }
    //
    //                 else {
    //                     $this->swapping($user);
    //                     session()->regenerate();
    //                     session(['user_id'     => $user->id]);
    //                     session(['user_name'   => $user->username]);
    //                     session(['role_id'     => $user->role_id]);
    //                     $arr = array();
    //                     $cr_access = DB::table("credential_access")
    //                         ->select('credentials.credential_name')
    //                         ->join('credentials', 'credentials.id', '=', 'credential_access.credential_id')
    //                         ->where("role_id",$user->role_id)
    //                         ->get();
    //                     foreach ($cr_access as $k=>$v){
    //                         //session([$v->credential_name=>$v->credential_name]);
    //                         $arr[] = $v->credential_name;
    //                     }
    //                     session(['credentialaccess'=>$arr]);
    //                     if($request->session()->get('url.intended') == "http://localhost/pronto_admin/admin" || $request->session()->get('url.intended') == "http://localhost/pronto_admin/admin")
    //                       return Redirect::to('admin/dashboard');
    //                     else
    //                       return Redirect::to($request->session()->get('url.intended'));
    //                 }
    //             }
    //         }
    //         // attempt to do the login
    //         // if (Auth::attempt($userdata)) {
    //         // validation successful!
    //         // redirect them to the secure section or whatever
    //         // return Redirect::to('secure');
    //         // for now we'll just echo success (even though echoing in a controller is bad)
    //         // return Redirect::to('admin/dashboard');
    //
    //         // }
    //
    //         return Redirect::back()->with('login','Invalid username / password');
    //
    //     }
    // }

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

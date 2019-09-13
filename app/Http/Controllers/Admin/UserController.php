<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Redirect;
use DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{

  public function loginForm(){
    if(Auth::check())
		{
			if(Auth::user()->role_id == 1)
			{
				return redirect("admin/home");
			}
			else
			{
				return view('auth.login');
			}
		}
    else {
      return view('auth.login');
    }
  }

  public function doLogin(Request $request)
  {
    $rules = array(
		"email"    => array("required", "email", "exists:users,email"),
		"password" => array("required", "min:6")
	                );
    $validator = Validator::make($request->all(), $rules);
    if($validator->fails())
	  {
		  return redirect()->back()->withErrors($validator)->withInput(Input::except('password'));
	  }
   	else
	  {
      $email = $request->get("email");
  	  $password = $request->get("password");
      if(Auth::attempt(["email" => $email, "password" => $password, "status" => "activated", 'role_id' => 1]))
  	  {
        return redirect('/admin/home');
  	  }
    	else
    	{
    		return redirect()->back()->with('login','These credentials do not match our records.');
    	}
 	  }
  }
}

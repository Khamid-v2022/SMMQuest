<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Hash;
use Session;

class LoginBasic extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.authentications.auth-login', ['pageConfigs' => $pageConfigs]);
  }

  public function login(Request $request){
    $request->validate([
        'email' => 'required',
        'password' => 'required',
    ]);

    $credentials = $request->only('email', 'password');
    if (Auth::attempt($credentials)) {
        return response()->json(['code'=>200, 'message'=>'You have successfully logged in'], 200);
    }

    return response()->json(['code'=>401, 'message'=>'You have entered invalid login details'], 401);
  }

  public function logout(){
    Session::flush();
    Auth::logout();

    return Redirect('/auth/login');
  }
}

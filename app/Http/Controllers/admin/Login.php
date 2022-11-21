<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

use Hash;
use Session;

class Login extends Controller
{
  public function __construct(){
    $this->middleware('guest:admin')->except('logout');
  }
  
  public function index()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.adminside.login', ['pageConfigs' => $pageConfigs]);
  }

  public function signin(Request $request){
    $request->validate([
      'name' => 'required',
      'password' => 'required',
    ]);

    $credentials = $request->only('name', 'password');

    if (Auth::guard('admin')->attempt($credentials)) {
      return response()->json(['code'=>200, 'message'=>'You have successfully logged in'], 200);
    }
    return response()->json(['code'=>401, 'message'=>'You have entered invalid login details'], 401);
  }

  public function signout(){
    Session::flush();
    if (Auth::check()) {
      Auth::logout();
    }
    return Redirect('/admin-page/login');

    // Auth::guard('admin')->logout();
 
    // $request->session()->invalidate();

    // return redirect('/admin/login');
  }

  protected function guard(){
    return Auth::guard('admin');
  }
}

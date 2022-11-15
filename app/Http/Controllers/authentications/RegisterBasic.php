<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Hash;

class RegisterBasic extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.authentications.auth-register', ['pageConfigs' => $pageConfigs]);
  }

  public function register(Request $request) {
    $request->validate([
      'email'            => 'required',
      'password'         => 'required'
    ]);

    // check email has used or not
    $user = new User;
    $users = $user->where('email', strtolower($request->email))->get();
    if(count($users) > 0){
        return response()->json(['code'=>422, 'message'=>'The email address you entered is already in use by another user.'], 200);
    }

    $avatar = 'custom/img/avatars/default-' . rand(0, 10) . '.png';
    $user = User::updateOrCreate(['id' => $request->id], [
        'email' => strtolower($request->email),
        'password' => Hash::make($request->password),
        'avatar' => $avatar
    ]);

    return response()->json(['code'=>200, 'message'=>'Success', 'data'=>$user], 200);
  }
}

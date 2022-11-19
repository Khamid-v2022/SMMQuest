<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Hash;

use Illuminate\Support\Facades\Mail;
use App\Mail\Notify;

class RegisterBasic extends Controller
{
  public function index()
  {
    return "register page";
    // $pageConfigs = ['myLayout' => 'blank'];
    // return view('content.authentications.auth-register', ['pageConfigs' => $pageConfigs]);
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

    $verify_code = $this->randomString(99);
    $user->verify_code = $verify_code;
    $user->save();

    return response()->json(['code'=>200, 'message'=>'Success', 'verify_code'=>$verify_code], 200);
  }

  public function sendVerifyEmail($email){
    $user = User::where('email', $email)->first();
    if(!$user){
      return redirect('/failed-email-verify');
    }

    if($user->verified){
      $pageConfigs = ['myLayout' => 'blank'];
      return view('content.authentications.verify-success', ['pageConfigs' => $pageConfigs]);
    }

    $verify_code = $this->randomString(99);
    $user->verify_code = $verify_code;
    $user->save();

    $active_link = route('email-verify', ['unique_str' => $verify_code]);
    $details = [
      'title' => 'Verification',
      'body' => 'Please confirm email address for your account. Click the link below to confirm your email:<br/>' 
                  . '<a href="' . $active_link . '" target="_black">' . $active_link . '</a>'
    ];
    
    try {
      Mail::to($user->email) -> send(new Notify($details));
    } catch (Exception $e) {
      if (count(Mail::failures()) > 0) {
          return redirect('/failed-email-verify');
      }
    }
    
    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.authentications.verify-email', ['pageConfigs' => $pageConfigs, 'email'=> $user->email]);
  }

  public function resendVerifyEmail($email){
    $user = User::where('email', $email)->first();
    if(!$user){
      return response()->json(['code'=>201, 'message'=>'You are not our member!'], 201);
    }

    $verify_code = $this->randomString(99);
    $user->verify_code = $verify_code;
    $user->save();

    $active_link = route('email-verify', ['unique_str' => $verify_code]);
    $details = [
      'title' => 'Verification',
      'body' => 'Please confirm email address for your account. Click the link below to confirm your email:<br/>' 
                  . '<a href="' . $active_link . '" target="_black">' . $active_link . '</a>'
    ];
    
    try {
      Mail::to($user->email) -> send(new Notify($details));
    } catch (Exception $e) {
      if (count(Mail::failures()) > 0) {
        return response()->json(['code'=>400, 'message'=>'Successfully resent!'], 400);
      }
    }
    
    return response()->json(['code'=>200, 'message'=>'Successfully resent!'], 200);
  }

  public function verifyEmail($verify_code){
    $user = User::where('verify_code', $verify_code)->first();
    
    if(!$user){
      return redirect('/pages/misc-error');
    }
    
    $user->verified = true;
    $user->email_verified_at = date("Y-m-d H:i:s");
    $user->verify_code = "";
    $user->save();
    
    $login_link = route('pages-home');
    $details = [
      'title' => 'Verification Success',
      'body' => 'Your account is activated. Click the link below to login:<br/>' 
                  . '<a href="' . $login_link . '" target="_black">' . $login_link . '</a>'
    ];
    
    try {
      Mail::to($user->email) -> send(new Notify($details));
    } catch (Exception $e) {
      if (count(Mail::failures()) > 0) {
          return redirect('/failed-email-verify');
      }
    }
    
    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.authentications.verify-success', ['pageConfigs' => $pageConfigs]);
    // return redirect('/auth/login');
  }

  public function failedVerify(Request $request){    
    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.authentications.verify-failed', ['pageConfigs' => $pageConfigs]);
  }
}

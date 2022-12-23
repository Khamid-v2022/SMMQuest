<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\LoginHistory;

use Stevebauman\Location\Facades\Location;
use Jenssegers\Agent\Facades\Agent;

use Illuminate\Support\Facades\Mail;
use App\Mail\Notify;

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

    $user = User::where('email', strtolower($request->email))->first();
    if($user->is_delete == 1){
      return response()->json(['code'=>201, 'message'=>'This account is not activated.'], 201);
    }
    if(Hash::check($request->password, $user->password) && $user->verified == false){
      return response()->json(['code'=>201, 'message'=>'This account is not verified. Please check your email box.'], 201);
    }

    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
      // set login history
      $user = Auth::user();
      $user->last_auth_at = date("Y-m-d H:i:s");
      $user->save();

      $history = new LoginHistory;
      $history->user_id = $user->id;
      $history->agency = $request->header('User-Agent');
      $history->ip_address = $request->ip();
      $history->device = Agent::device();
      $history->browser = Agent::browser();
      $history->platform = Agent::platform();
      $location = Location::get($request->ip());
      if($location){
        if( $location->countryName == "Estonia")
          $history->location = "Latvia";
        else
          $history->location = $location->countryName;
      }
      $history->loggedin_at = date("Y-m-d H:i:s");
      $history->save();
      
      return response()->json(['code'=>200, 'message'=>'You have successfully logged in'], 200);
    }

    return response()->json(['code'=>401, 'message'=>'You have entered invalid login details'], 401);
  }

  public function logout(){
    Session::flush();
    if (Auth::check()) {
      Auth::logout();
    }

    return Redirect('/auth/login');
  }

  public function forgotPassword(Request $request){
    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.authentications.forgot-password', ['pageConfigs' => $pageConfigs]);
  }

  public function sendMailToResetPasswordLink(Request $request){
    $request->validate([
      'email' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();
    if(!$user)
      return response()->json(['code'=>201, 'message'=>'This is an unregistered email.'], 200);
    
    // send email
    $verify_code = $this->randomString(99);
    $user->verify_code = $verify_code;
    $user->save();

    $active_link = route('reset-password', ['unique_str' => $verify_code]);
    $details = [
      'title' => 'Reset Password',
      'body' => ' Click the link below to reset your password:<br/>' 
                  . '<a href="' . $active_link . '" target="_black">' . $active_link . '</a>'
    ];
    
    try {
      Mail::to($user->email) -> send(new Notify($details));
    } catch (Exception $e) {
      if (count(Mail::failures()) > 0) {
        return redirect('/pages/misc-error');
      }
    }
    return response()->json(['code'=>200, 'message'=>'Please check your email box'], 200);
  }

  public function resetPasswordPage($verify_code){
    $user = User::where('verify_code', $verify_code)->first();
    
    if(!$user){
      return redirect('/pages/misc-error');
    }

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.authentications.reset-password', ['pageConfigs' => $pageConfigs, 'email' => $user->email ]);
  }

  public function resetPassword(Request $request){
    $request->validate([
      'email' => 'required',
      'password' => 'required',
    ]);

    $user = User::where('email', strtolower($request->email))->first();
    if(!$user){
      return response()->json(['code'=>400, 'message'=>'Invalid email address'], 400);
    }
    $user->password = Hash::make($request->password);
    $user->save();

    return response()->json(['code'=>200, 'message'=>'Success', 'data'=>$user], 200);
  }
}

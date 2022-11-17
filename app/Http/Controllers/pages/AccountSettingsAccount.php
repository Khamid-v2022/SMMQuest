<?php

namespace App\Http\Controllers\pages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

use Illuminate\Support\Facades\Mail;
use App\Mail\Notify;

class AccountSettingsAccount extends MyController
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    return view('content.pages.pages-account-settings', ['pageConfigs'=> $pageConfigs]);
  }

  public function update(Request $request) {
    if($request->file('file')){
      $validator = Validator::make($request->all(), [
        'file' => 'required|mimes:png,jpg,jpeg,csv,txt,pdf|max:2048'
      ]);
      if ($validator->fails()) {
        return response()->json(['code'=>400, 'message'=> $validator->errors()->first('file')], 400);
      } else {
        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();

        // File extension
        $extension = $file->getClientOriginalExtension();

        // File upload location
        $location = 'custom/img/avatars/';

        // Upload file
        $file->move($location, $filename);
        $filepath = url($location . $filename);
      } 
    } 
    
    $user = Auth::user();
    if(isset($filepath))
      $user->avatar = $filepath;
    $user->first_name = $request->first_name;
    $user->last_name = $request->last_name;
    $user->organization = $request->organization;
    $user->phone = $request->phone;
    $user->address = $request->address;
    $user->state = $request->state;
    $user->zip_code = $request->zip_code;
    $user->country = $request->country;
    $user->timezone = $request->timezone;
    $user->save();
    
    return response()->json(['code'=>200, 'message'=>'Updated'], 200);
  }

  public function delete(Request $request) { 
    $user = Auth::user();
    $user->delete();
    return response()->json(['code'=>200, 'message'=>'Deleted'], 200);
  } 

  public function verifyEmail(Request $request){
    $user = Auth::user();
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
          return redirect('/pages/misc-error');
      }
    }
    
    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.authentications.verify-email', ['pageConfigs' => $pageConfigs, 'email'=> $user->email]);
  }

  public function sendVerifyEmail(){
    $user = Auth::user();
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
          return redirect('/pages/misc-error');
      }
    }

    return response()->json(['code'=>200, 'message'=>'Successfully resent!'], 200);
  }

  private function randomString($length) {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < $length; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass);
  }
}
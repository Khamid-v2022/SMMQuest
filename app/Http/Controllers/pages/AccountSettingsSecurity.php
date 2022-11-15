<?php

namespace App\Http\Controllers\pages;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LoginHistory;
use Illuminate\Support\Facades\Auth;
use Hash;

class AccountSettingsSecurity extends MyController
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];
    $history = LoginHistory::where('user_id', Auth::user()->id)->orderBy('loggedin_at', 'desc')->get();
    
    return view('content.pages.pages-account-security', 
      [
        'pageConfigs'=> $pageConfigs,
        'history' => $history
      ]);
  }

  public function update(Request $request){
    $request->validate([
      'current_password' => 'required',
      'new_password' => 'required',
    ]);
  
    if(!(Hash::check($request->current_password, Auth::user()->password))){
        return response()->json(['code'=>401, 'message'=>'Authentication failed'], 200);
    }

    if(strcmp($request->current_password, $request->new_password) == 0){
        //Current password and new password are same
        return response()->json(['code'=>402, 'message'=>'New password cannot be the same as your current password. Choose a different password.'], 200);
    }
    
    // change password
    $user = Auth::user();
    $user->password = Hash::make($request->new_password);
    $user->save();

    return response()->json(['code'=>200, 'message'=>'Password updated'], 200);
  }
}

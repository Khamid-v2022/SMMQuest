<?php

namespace App\Http\Controllers\pages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

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
    $user->phone = $request->phone;
    $user->address = $request->address;
    $user->zip_code = $request->zip_code;
    $user->country = $request->country;
    $user->save();
    
    return response()->json(['code'=>200, 'message'=>'Updated'], 200);
  }
  public function delete(Request $request) { 
    $user = Auth::user();
    $user->delete();
    return response()->json(['code'=>200, 'message'=>'Deleted'], 200);
  } 
}
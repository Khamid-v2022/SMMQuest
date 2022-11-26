<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Subscriber;

class MiscComingSoon extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.pages.pages-misc-comingsoon', ['pageConfigs' => $pageConfigs]);
  }

  public function addSubscriber(Request $request) {
    $request->validate([
        'email' => 'required',
    ]);

    // checking is our member
    $user = User::where('email', strtolower($request->email))->first();
    if($user){
        return response()->json(['code'=>201, 'message'=>'You are aready our member!'], 201); 
    }

    $subscriber = Subscriber::where('email', strtolower($request->email))->first();
    if($subscriber){
        return response()->json(['code'=>201, 'message'=>'You are aready subscribed!'], 201); 
    }
    Subscriber::create([
        'email' => strtolower($request->email)
    ]);
    
    return response()->json(['code'=>200, 'message'=>'Thank you.'], 200);
  }
}

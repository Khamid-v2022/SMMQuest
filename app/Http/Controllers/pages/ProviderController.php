<?php

namespace App\Http\Controllers\pages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserProvider;
use App\Models\Provider;

use Illuminate\Support\Facades\Mail;
use App\Mail\Notify;

class ProviderController extends MyController
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];

    $providers = UserProvider::with(['user', 'provider'])
      ->where('user_id', Auth::user()->id)->get();
    
      return view('content.pages.pages-user-provider', [
        'pageConfigs'=> $pageConfigs, 
        'providers' => $providers
      ]);
  }

  public function createNewProvider(Request $request){
    $request->validate([
      'domain' => 'required'
    ]);

    // check aready registred 

    $provider = Provider::where('domain', $request->domain)->get();
    if(count($provider) > 0){
      // check aready registred in this account
      $is_exist = UserProvider::where('provider_id', $provider[0]->id)
          ->where('user_id', Auth::user()->id)->first();
      if($is_exist){
        return response()->json(['code'=>422, 'message'=>'Already registred.'], 200);
      }


      $user_provider = UserProvider::create([
        'user_id' => Auth::user()->id,
        'provider_id' => $provider[0]->id,
        'is_favorite' => ($request->favorite ? 1 : 0),
        'api_key' => $request->api_key,
        'created_at' => date("Y-m-d H:i:s")
      ]);

      return response()->json(['code'=>200, 'message'=>'Sussess'], 200);
    } else {
      // create new provider but with not activated
      $new_provider = Provider::create([
        'domain' => $request->domain,
        'endpoint' => '/api/v2?action=services',
        'request_by' => Auth::user()->id,
        'created_at' => date("Y-m-d H:i:s"),
        'is_activated' => 0
      ]);

      $user_provider = UserProvider::create([
        'user_id' => Auth::user()->id,
        'provider_id' => $new_provider->id,
        'is_favorite' => ($request->favorite ? 1 : 0),
        'api_key' => $request->api_key,
        'created_at' => date("Y-m-d H:i:s")
      ]);

      // SEND an Email to Admin
      $details = [
        'title' => 'New Provider request',
        'body' => 'There is a request to add a new provider from the following user:<br/>' 
                  . 'User Name: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . '<br/>'
                  . 'Email: ' . Auth::user()->email . '<br/>'
                  . 'Provider: '  . '<a href="' . $request->domain . '" target="_black">' . $request->domain . '</a>'
      ];
      
      try {
        Mail::to(env('ADMIN_MAIL')) -> send(new Notify($details));
      } catch (Exception $e) {
      }

      return response()->json(['code'=>200, 'message'=>'A request has been sent to the administrator to activate the new provider.'], 200);
    }

  }

  public function deleteProvider($id){
    // check this service is inactiveated or not
    // $user_provider = UserProvider::where('id', $id)->first();
    UserProvider::where('id', $id)->delete();
    return response()->json(['code'=>200, 'message'=>'Deleted successfully'], 200);
  }

  public function favoriteProvider(Request $request){
    UserProvider::where('id', $request->selected_id)->update(['is_favorite' => $request->favorite]);
    return response()->json(['code'=>200, 'message'=>'Updated successfully'], 200);
  }

  public function changeAPIKey(Request $request){
    UserProvider::where('id', $request->selected_id)->update(['api_key' => $request->api_key]);
    return response()->json(['code'=>200, 'message'=>'Updated successfully'], 200);
  }
}

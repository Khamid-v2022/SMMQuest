<?php

namespace App\Http\Controllers\pages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserProvider;
use App\Models\Provider;

use Illuminate\Support\Facades\Mail;
use App\Mail\Notify;


use App\Http\Controllers\admin\APItemplates\PerfectPanel;
use App\Http\Controllers\admin\APItemplates\SmmPanel;

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
    
    // remove http://, https://, remove / from last of url
    $domain = rtrim(preg_replace( "#^[^:/.]*[:/]+#i", "", $request->domain), '/');
    
    // check aready registred 
    $provider = Provider::where('domain', $domain)->get();
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
        'is_enabled' => 1,
        'is_valid_key' => 0,
        'created_at' => date("Y-m-d H:i:s")
      ]);

      return response()->json(['code'=>200, 'message'=>'Sussess'], 200);
    } else {
      // create new provider but with not activated
      $new_provider = Provider::create([
        'domain' => $domain,
        'is_valid_key' => 0,
        'is_activated' => 0,
        'request_by' => Auth::user()->id,
        'is_hold' => 1,
        'created_at' => date("Y-m-d H:i:s")
      ]);

      $user_provider = UserProvider::create([
        'user_id' => Auth::user()->id,
        'provider_id' => $new_provider->id,
        'is_favorite' => ($request->favorite ? 1 : 0),
        'is_enabled' => 1,
        'is_valid_key' => 0,
        'created_at' => date("Y-m-d H:i:s")
      ]);

      // SEND an Email to Admin
      $details = [
        'title' => 'New Provider request',
        'body' => 'There is a request to add a new provider from the following user:<br/>' 
                  . 'User Name: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . '<br/>'
                  . 'Email: ' . Auth::user()->email . '<br/>'
                  . 'Provider: '  . '<a href="' . $domain . '" target="_black">' . $domain . '</a>'
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
    $user_provider = UserProvider::where('id', $request->selected_id)->first();
    $user_provider->api_key = $this->encrypt(trim( $request->api_key));
    $user_provider->save();
    
    // checking API key is working or not
    $provider = Provider::where('id', $user_provider->provider_id)->first();
    
    if($provider['is_activated'] == 1){
      $url = rtrim($this->check_protocol($provider['domain']), '/');
      $api_check = $this->checkKey($url . $provider['endpoint'], trim($request->api_key), $provider['api_template']);
      if($api_check){
        $user_provider->is_valid_key = 1;
        $user_provider->save();
        return response()->json(['code'=>200, 'message'=>'The API Key verified.'], 200);
      } else {
        $user_provider->is_valid_key = 0;
        $user_provider->save();
        return response()->json(['code'=>400, 'message'=>'The API Key cannot be verified. Please make sure your key'], 200);
      }
    }

    return response()->json(['code'=>400, 'message'=>'Since the site is inactive, the API Key cannot be verified.
    please try again later'], 200);
  }

  private function encrypt($message)
  {
      $cipher_method = 'aes-128-ctr';
      
      $enc_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher_method));
      $crypted_key = openssl_encrypt($message, $cipher_method, env('ENCRYPT_KEY'), 0, $enc_iv) . "::" . bin2hex($enc_iv);

      return $crypted_key;
  }

  private function checkKey($url, $key, $template) {
    switch($template){
      case 'PerfectPanel':
          $perfectPanel = new PerfectPanel($url, $key);

          $balance = $perfectPanel->balance();
          $balance = json_decode( json_encode($balance), true );

          if($balance && !isset($balance['error'])){
              return true;
          }
          break;
      case 'SmmPanel':
          // https://smmpanele.ru/api/v2
          $smmPanel = new SmmPanel($url, $key);
          $services = $smmPanel->services();
          $services = json_decode( json_encode($services), true );

          if(is_array($services) && count($services) > 0 && isset($services[0]['name'])){
              return true;
          }
          break;
    }

    return false;
  }


  private function check_protocol($domain){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $domain);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    curl_exec($ch);

    $real_url =  curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    return $real_url;
  }
}

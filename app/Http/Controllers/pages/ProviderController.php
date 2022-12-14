<?php

namespace App\Http\Controllers\pages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserProvider;
use App\Models\ProviderHold;
use App\Models\Provider;

use Illuminate\Support\Facades\Mail;
use App\Mail\Notify;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\admin\APItemplates\PerfectPanel;
use App\Http\Controllers\admin\APItemplates\SmmPanel;

class ProviderController extends MyController
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'horizontal'];

    // $providers = UserProvider::with([
    //     'provider', 
    //     'provider.services' => function($q) {
    //       $q->where('status', '1');
    //     }
    //   ])
    //   ->where('user_id', Auth::user()->id)
    //   ->get();

    $providers = UserProvider::getProviderListWithDetail(Auth::user()->id);

    $hold_providers = ProviderHold::select('domain')
      ->where('request_by_id', Auth::user()->id)
      ->where('request_by_admin', 0)
      ->groupBy('domain')->get();
    
    return view('content.pages.pages-user-provider', [
      'pageConfigs'=> $pageConfigs, 
      'providers' => $providers,
      'hold_providers' => $hold_providers
    ]);
  }

  public function createNewProvider(Request $request){
    $request->validate([
      'domain' => 'required'
    ]);
    
    $domain = $this->getDomain($request->domain);
    
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
        'is_favorite' => $request->favorite,
        'is_enabled' => 1,
        'is_valid_key' => 0,
        'created_at' => date("Y-m-d H:i:s")
      ]);

      return response()->json(['code'=>200, 'message'=>'Success'], 200);
    } else {
      // check domain is valid URL or not
      $url = rtrim($this->check_protocol($domain), '/');
      $response = $this->urlExists($url);
      if(!$response) {
        return response()->json(['code'=>400, 'message'=>'This domain name is not exist'], 200);
      }
      // create new provider but with not activated
      $new_provider = Provider::create([
        'domain' => $domain,
        'real_url' => $url,
        'is_valid_key' => 0,
        'is_activated' => 0,
        'request_by' => Auth::user()->id,
        'is_hold' => 1,
        'created_at' => date("Y-m-d H:i:s")
      ]);

      $user_provider = UserProvider::create([
        'user_id' => Auth::user()->id,
        'provider_id' => $new_provider->id,
        'is_favorite' => $request->favorite,
        'is_enabled' => 1,
        'is_valid_key' => 0,
        'created_at' => date("Y-m-d H:i:s")
      ]);

      // SEND an Email to Admin
      // $details = [
      //   'title' => 'New Provider request',
      //   'body' => 'There is a request to add a new provider from the following user:<br/>' 
      //             . 'User Name: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . '<br/>'
      //             . 'Email: ' . Auth::user()->email . '<br/>'
      //             . 'Provider: '  . '<a href="' . $domain . '" target="_black">' . $domain . '</a>'
      // ];
      
      // try {
      //   Mail::to(env('ADMIN_MAIL')) -> send(new Notify($details));
      // } catch (Exception $e) {
      // }

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
      // $url = rtrim($this->check_protocol($provider['domain']), '/');
      $api_check = $this->checkKey($provider['real_url'] . $provider['endpoint'], trim($request->api_key), $provider['api_template']);
      if($api_check['status'] == 1){

        $user_provider->is_valid_key = 1;
        $user_provider->user_balance = $api_check['balance'];
        $user_provider->balance_currency = $api_check['currency'];

        $user_provider->save();

        return response()->json(['code'=>200, 'message'=>'The API Key verified.'], 200);
      } else if($api_check['status'] == 2) {
        $user_provider->is_valid_key = 0;
        $user_provider->user_balance = $api_check['balance'];
        $user_provider->balance_currency = $api_check['currency'];
        $user_provider->save();
        return response()->json(['code'=>400, 'message'=>'The API Key cannot be verified. Please make sure your key'], 200);
      }
      
    }

    return response()->json(['code'=>400, 'message'=>'Since the site is inactive, the API Key cannot be verified.
    please try again later'], 200);
  }


  private function checkKey($url, $key, $template) {
    switch($template){
      case 'PerfectPanel':
          $perfectPanel = new PerfectPanel($url, $key);

          $balance = $perfectPanel->balance();
          $balance = json_decode( json_encode($balance), true );

          if($balance){
            if(!isset($balance['error'])){
              return array (
                'status'=> 1, 
                'balance' => $balance['balance'],
                'currency' => $balance['currency']
              );
            } else {
              if($balance['error'] == "Incorrect request"){
                // Frozon status
                return array (
                    'status'=> 3, 
                    'balance' => NULL,
                    'currency' => NULL
                );
              } else {
                  // wrong API key
                  return array (
                      'status'=> 2, 
                      'balance' => NULL,
                      'currency' => NULL
                  );
              }
            }
          }
          break;
      case 'SmmPanel':
          // https://smmpanele.ru/api/v2
          $smmPanel = new SmmPanel($url, $key);
          $services = $smmPanel->services();
          $services = json_decode( json_encode($services), true );

          if(is_array($services) && count($services) > 0 && isset($services[0]['name'])){
            return array (
              'status'=> 1,
              'balance' => NULL,
              'currency' => NULL
            );
          } else {
            return array (
              'status'=> 2,
              'balance' => NULL,
              'currency' => NULL
            );
          }
          break;
    }

    // wrong url or endpoint
    return array (
      'status'=> 0
    );
  }

  public function changeBalanceAlertLimit(Request $request){
    $user_provider = UserProvider::where('id', $request->selected_id)->first();
    $user_provider->balance_alert_limit = $request->limit;
    $user_provider->save();
    return response()->json(['code'=>200, 'message'=>'Changed'], 200);
  }
  

  public function importList(Request $request){
    set_time_limit(300);
    $providers = json_decode($request->list);
    $added_count = 0;
    $created_count = 0;

    $new_request_domains = [];

    foreach($providers as $item){
      $domain = $this->getDomain($item->domain);
      if($domain){
        // check aready registred 
        $provider = Provider::where('domain', $domain)->first();
        if($provider){
          // check aready registred in this account
          $is_exist = UserProvider::where('provider_id', $provider->id)
              ->where('user_id', Auth::user()->id)->first();
          
          if(!$is_exist){
            
            if($item->key){
              // save to hold_provider table to check API key in cronjob
              ProviderHold::updateOrCreate(['domain' => $domain,'request_by_id' => Auth::user()->id, 'request_by_admin' => 0 ], [
                'domain' => $domain,
                'api_key' => $this->encrypt(trim($item->key)),
                'request_by_admin' => 0,                //user request
                'request_by_id' => Auth::user()->id,
                'is_only_key_check' => 1,
                'created_at' => date("Y-m-d H:i:s")
              ]);
            } else {
              $user_provider = UserProvider::create([
                'user_id' => Auth::user()->id,
                'provider_id' => $provider->id,
                'is_favorite' => 0,
                'is_enabled' => 1,
                'is_valid_key' => 0,
                'created_at' => date("Y-m-d H:i:s")
              ]);
            }
            
            $added_count++;
          } else {
            // if old registred provider key is wrong and inputed key is exist then update key
            if($is_exist->is_valid_key == 0 && $item->key){
              UserProvider::where('id', $is_exist->id)->update(['api_key' => $this->encrypt(trim($item->key))]);
              ProviderHold::updateOrCreate(['domain' => $domain,'request_by_id' => Auth::user()->id, 'request_by_admin' => 0 ], [
                'domain' => $domain,
                'api_key' => $this->encrypt(trim($item->key)),
                'request_by_admin' => 0,                //user request
                'request_by_id' => Auth::user()->id,
                'is_only_key_check' => 1,
                'created_at' => date("Y-m-d H:i:s")
              ]);
            }
          }
          
        } else {
          // store hold table
          $api_key = NULL;

          if($item->key){
            $api_key = $this->encrypt(trim($item->key));
          }
          
          ProviderHold::updateOrCreate(['domain' => $domain,'request_by_id' => Auth::user()->id, 'request_by_admin' => 0 ], [
            'domain' => $domain,
            'request_by_admin' => 0,                //user request
            'api_key' => $api_key,
            'request_by_id' => Auth::user()->id,
            'is_only_key_check' => 0,
            'created_at' => date("Y-m-d H:i:s")
          ]);

          $created_count++;
          array_push($new_request_domains, $domain);
        }
      }
    }
  
    if($created_count > 0){
      $porvider_str = "";
      foreach($new_request_domains as $item){
        $porvider_str .= '<a href="' . $item . '" target="_black">' . $item . '</a><br>';
      }

      // // SEND an Email to Admin
      // $details = [
      //   'title' => 'New Provider request',
      //   'body' => 'There is a request to add a new provider from the following user:<br/>' 
      //             . 'User Name: ' . Auth::user()->first_name . ' ' . Auth::user()->last_name . '<br/>'
      //             . 'Email: ' . Auth::user()->email . '<br/>'
      //             . 'Provider: '  . $porvider_str
      // ];

      // try {
      //   Mail::to(env('ADMIN_MAIL')) -> send(new Notify($details));
      // } catch (Exception $e) {
      // }

      return response()->json(['code'=>200, 'message'=>'A request has been sent to the administrator to activate the new provider.'], 200);
    } else {
      return response()->json(['code'=>200, 'message'=>$added_count . ' providers added and sent a new request of ' . $created_count . ' providers '], 200);
    }
  }

}

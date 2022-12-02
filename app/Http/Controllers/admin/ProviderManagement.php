<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderHold;

use Illuminate\Support\Facades\Http;

use App\Http\Controllers\admin\APItemplates\PerfectPanel;
use App\Http\Controllers\admin\APItemplates\SmmPanel;
use App\Http\Controllers\admin\APItemplates\MonksmmPanel;

class ProviderManagement extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];

        $providers = Provider::get();
        $hold_providers = ProviderHold::select('domain')->groupBy('domain')->get();

        return view('content.adminside.provider-management', [
            'pageConfigs'=> $pageConfigs, 
            'providers' => $providers,
            'hold_providers' => $hold_providers
        ]);
    }

    public function addProvider(Request $request) {
        $request->validate([
          'domain' => 'required'
        ]);

        // remove http://, https://, remove / from last of url
        $domain = rtrim(preg_replace('#^www\.(.+\.)#i', '$1', preg_replace( "#^[^:/.]*[:/]+#i", "", $request->domain)), '/');
        $url = rtrim($this->check_protocol($domain), '/');
        $end_point = '/' . rtrim(ltrim($request->end_point, '/'), '/');

        if($request->action_type == "add") {
            // check aready registred 
            $provider = Provider::where('domain', $domain)->first();
            if($provider){
                return response()->json(['code'=>422, 'message'=>'Already registred.'], 200);
            }
        } 

        // checking domain is working or not
        $response = $this->urlExists($url);
        if($response) {
            // check API key working or not
            $api_check = $this->checkAPITemplate($url . $end_point, trim($request->api_key));  
            if($api_check['status'] > 0 ){
                $is_valid_key = 0;
                if($api_check['status'] == 1){
                    $is_valid_key = 1;
                } else {
                    // invalid key
                    $is_valid_key = 0;
                }

                if($request->action_type == "add"){
                    $user_provider = Provider::create([
                        'domain' => $domain,
                        'is_activated' => 1,
                        'api_key' => $this->encrypt(trim($request->api_key)),
                        'is_valid_key' => $is_valid_key,
                        'api_template' =>  $api_check['apiTemplate'],
                        'balance' =>  $api_check['balance'],
                        'currency' =>  $api_check['currency'],
                        'endpoint' => $end_point,
                        'is_hold' => 0,
                        'created_at' => date("Y-m-d H:i:s")
                    ]);
                } else {
                    $user_provider = Provider::where('id', $request->selected_id)->update([
                        'domain' => $domain,
                        'is_activated' => 1,
                        'api_key' => $this->encrypt(trim($request->api_key)),
                        'is_valid_key' => $is_valid_key,
                        'api_template' =>  $api_check['apiTemplate'],
                        'balance' =>  $api_check['balance'],
                        'currency' =>  $api_check['currency'],
                        'endpoint' => $end_point,
                        'is_hold' => 0,
                        'updated_at' => date("Y-m-d H:i:s")
                    ]);
                }
            } else {
                return response()->json(['code'=>400, 'message'=>'API key/End Point is not correct!'], 200);
            }

            return response()->json(['code'=>200, 'message'=>'Sussess'], 200);

        } else {
            return response()->json(['code'=>400, 'message'=>'This domain name is not exist'], 200);
        }
    }

    public function deleteProvider(Request $request) {
        Provider::where('id', $request->id)->delete();
        return response()->json(['code'=>200, 'message'=>'Deleted successfully'], 200);
    }

    private function checkAPITemplate($url, $key) {
        $status = false;
        $apiTemplate = '';
        $currentBalance = '';
        $currency = '';

        // perfect panel
        $perfectPanel = new PerfectPanel($url, $key);

        $balance = $perfectPanel->balance();
        $balance = json_decode(json_encode($balance), true );

        if($balance){
            if(!isset($balance['error'])){
                return array (
                    'status'=> 1, 
                    'apiTemplate'=> 'PerfectPanel', 
                    'balance' => $balance['balance'],
                    'currency' => $balance['currency']
                );
            } else {
                // wrong API key
                return array (
                    'status'=> 2, 
                    'apiTemplate'=> 'PerfectPanel', 
                    'balance' => 0,
                    'currency' => ''
                );
            }
        }

        // SmmPanel     https://smmpanele.ru/api/v2
        $smmPanel = new SmmPanel($url, $key);
        $response = $smmPanel->services();
   
        if($response){     
            $services = json_decode( json_encode($response), true );
            if(is_array($services) && count($services) > 0 && isset($services[0]['name'])){
                return array (
                    'status'=> 1, 
                    'apiTemplate'=> 'SmmPanel', 
                    'balance' => null,
                    'currency' => null
                );
            } else {
                return array (
                    'status'=> 2, 
                    'apiTemplate'=> 'SmmPanel', 
                    'balance' => 0,
                    'currency' => ''
                );
            }
        } 

        

        // https://monksmm.tech/api/v1  -- Same with Perfect panel
        // $monksmmPanel = new MonksmmPanel($url, $key);
        // $balance = $monksmmPanel->balance();
        // $balance = json_decode( json_encode($balance), true );

        // if($balance){
        //     if(!isset($balance['error']))
        //         return array (
        //             'status'=> 1, 
        //             'apiTemplate'=> 'PerfectPanel', 
        //             'balance' => $balance['balance'],
        //             'currency' => $balance['currency']
        //         );
        //     else
        //         return array (
        //             'status'=> 2, 
        //             'apiTemplate'=> 'PerfectPanel', 
        //             'balance' => 0,
        //             'currency' => ''
        //         );
                
        // }


        // wrong url or endpoint
        return array (
            'status'=> 0
        );
    }

    public function importList(Request $request){
        $providers = $request->list;
        $added_count = 0;

        foreach($providers as $item){
            // remove http://, https://, remove / from last of url
            $domain = rtrim(preg_replace('#^www\.(.+\.)#i', '$1', preg_replace( "#^[^:/.]*[:/]+#i", "", $item['domain'])), '/');  
            $end_point = '/' . rtrim(ltrim($item['end_point'], '/'), '/');
            
            // check aready registred 
            $provider = Provider::where('domain', $domain)->first();
            if(!$provider){
                if($item['key']){
                    // save to hold_provider table
                    ProviderHold::updateOrCreate(['domain' => $domain, 'request_by_admin' => 1 ], [
                        'domain' => $domain,
                        'endpoint' => $end_point,
                        'api_key' => $this->encrypt(trim($item['key'])),
                        'request_by_admin' => 1,                //user request
                        'request_by_id' => Auth::user()->id,
                        'is_only_key_check' => 0,
                        'created_at' => date("Y-m-d H:i:s")
                    ]);
                } else {
                    // save to hold_provider table
                    ProviderHold::updateOrCreate(['domain' => $domain, 'request_by_admin' => 1 ], [
                        'domain' => $domain,
                        'endpoint' => $end_point,
                        'api_key' => NULL,
                        'request_by_admin' => 1,                //user request
                        'request_by_id' => Auth::user()->id,
                        'is_only_key_check' => 0,
                        'created_at' => date("Y-m-d H:i:s")
                    ]);
                }
                $added_count++;
            }
        }
        if( $added_count > 0){
            return response()->json(['code'=>200, 'message'=>'Added ' . $added_count . ' providers'], 200);
        }
        else {
            return response()->json(['code'=>400, 'message'=>'Invalid providers'], 200);
        }
    }

    // public function importList_backup(Request $request){
    //     $providers = $request->list;
    //     $added_count = 0;
    //     $wrong_api_count = 0;
    //     $wrong_domain_count = 0;

    //     foreach($providers as $item){
    //         // remove http://, https://, remove / from last of url
    //         $domain = rtrim(preg_replace('#^www\.(.+\.)#i', '$1', preg_replace( "#^[^:/.]*[:/]+#i", "", $item['domain'])), '/');
    //         $url = rtrim($this->check_protocol($domain), '/');
    //         $end_point = '/' . rtrim(ltrim($item['end_point'], '/'), '/');

    //         // check aready registred 
    //         $provider = Provider::where('domain', $domain)->first();
    //         if(!$provider){
    //             // checking domain is working or not
    //             $response = $this->urlExists($url);
    //             if($response) {
    //                 // check API key working or not
    //                 $api_check = $this->checkAPITemplate($url . $end_point, trim($item['key']));  
    //                 if($api_check['status'] > 0 ){
    //                     $is_valid_key = 0;
    //                     if($api_check['status'] == 1){
    //                         $is_valid_key = 1;
    //                     } else {
    //                         // invalid key
    //                         $is_valid_key = 0;
    //                     }

    //                     $user_provider = Provider::create([
    //                         'domain' => $domain,
    //                         'is_activated' => 1,
    //                         'api_key' => $this->encrypt(trim($item['key'])),
    //                         'is_valid_key' => $is_valid_key,
    //                         'api_template' =>  $api_check['apiTemplate'],
    //                         'balance' =>  $api_check['balance'],
    //                         'currency' =>  $api_check['currency'],
    //                         'endpoint' => $end_point,
    //                         'is_hold' => 0,
    //                         'created_at' => date("Y-m-d H:i:s")
    //                     ]);
    //                     $added_count ++;
    //                 } else {
    //                     // API key/End Point is not correct
    //                     $wrong_api_count ++;
    //                 }
    //             } else {
    //                 // Domain is not exist
    //                 $wrong_domain_count++;
    //             }
    //         }
    //     }
    //     if( $added_count > 0){
    //         return response()->json(['code'=>200, 'message'=>'Added ' . $added_count . ' providers'], 200);
    //     }
    //     else {
    //         return response()->json(['code'=>400, 'message'=>'Invalid providers'], 200);
    //     }
    // }
}

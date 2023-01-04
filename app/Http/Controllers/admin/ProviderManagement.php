<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderHold;
use App\Models\Service;
use App\Models\Currency;

use Illuminate\Support\Facades\Http;

use App\Http\Controllers\admin\APItemplates\PerfectPanel;
use App\Http\Controllers\admin\APItemplates\SmmPanel;
use App\Http\Controllers\admin\APItemplates\MonksmmPanel;

class ProviderManagement extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'horizontal'];

        return view('content.adminside.provider-management', [
            'pageConfigs'=> $pageConfigs, 
        ]);
    }

    public function providerList(Request $request){
        $providers = Provider::get();
        $hold_providers = ProviderHold::select('domain')->where('is_only_key_check', '1')->groupBy('domain')->get();

        return response()->json(['code'=>200, 'providers'=>$providers, 'hold_providers'=>$hold_providers], 200);
    }

    public function addProvider(Request $request) {
        $request->validate([
          'domain' => 'required'
        ]);

        // get only domain name without http://, https://, www.
        $domain = $this->getDomain($request->domain);
        if($domain == '' )
            return response()->json(['code'=>400, 'message'=>'Wrong domain name'], 200);

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
            $is_valid_key = 0;
            $is_frozon = 0;

            if($api_check['status'] > 0 ){
                if($api_check['status'] == 1){
                    $is_valid_key = 1;
                } else if($api_check['status'] == 3){
                    // frozon status
                    $is_frozon = 1;
                }
                
                if($request->action_type == "add"){
                    $user_provider = Provider::create([
                        'domain' => $domain,
                        'is_activated' => 1,
                        'api_key' => $this->encrypt(trim($request->api_key)),
                        'is_valid_key' => $is_valid_key,
                        'is_frozon' => $is_frozon,
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
                        'is_frozon' => $is_frozon,
                        'api_template' =>  $api_check['apiTemplate'],
                        'balance' =>  $api_check['balance'],
                        'currency' =>  $api_check['currency'],
                        'endpoint' => $end_point,
                        'is_hold' => 0,
                        'updated_at' => date("Y-m-d H:i:s")
                    ]);
                }
            } else {
                return response()->json(['code'=>400, 'message'=>'Domain / API End Point is not correct!'], 200);
            }
            if($is_valid_key)
                return response()->json(['code'=>200, 'message'=>'Success'], 200);
            else
                return response()->json(['code'=>201, 'message'=>'Added/Updated! But API key is not correct!'], 200);
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
                if($balance['error'] == "Incorrect request"){
                    // Frozon status
                    return array (
                        'status'=> 3, 
                        'apiTemplate'=> 'PerfectPanel', 
                        'balance' => NULL,
                        'currency' => NULL
                    );
                } else {
                    // wrong API key
                    return array (
                        'status'=> 2, 
                        'apiTemplate'=> 'PerfectPanel', 
                        'balance' => NULL,
                        'currency' => NULL
                    );
                }
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
                    'balance' => NULL,
                    'currency' => NULL
                );
            } else {
                return array (
                    'status'=> 2, 
                    'apiTemplate'=> 'SmmPanel', 
                    'balance' => NULL,
                    'currency' => NULL
                );
            }
        } 

        // wrong url or endpoint
        return array (
            'status'=> 0
        );
    }

    public function importList(Request $request){
        $providers = json_decode($request->list);
        $added_count = 0;
        set_time_limit(300);

        foreach($providers as $item){
            // get only domain name without http://, https://, www.
            $domain = $this->getDomain($item->domain);
            $end_point = '/' . rtrim(ltrim($item->end_point, '/'), '/');
            
            $decrypted_key = NULL;
            if($item->key){
                $decrypted_key = $this->encrypt(trim($item->key));
            } 
            // check aready registred 
            $provider = Provider::where('domain', $domain)->first();
            if(!$provider){
                // save to hold_provider table
                ProviderHold::updateOrCreate(['domain' => $domain, 'request_by_admin' => 1 ], [
                    'domain' => $domain,
                    'endpoint' => $end_point,
                    'api_key' => $decrypted_key,
                    'request_by_admin' => 1,
                    'request_by_id' => Auth::user()->id,
                    'is_only_key_check' => 0
                ]);
                $added_count++;
            } else {
                // if registred alredy then only add to hold table to update in case of registred key is invalid
                if($provider->is_valid_key == 0){
                    // update key request
                    ProviderHold::updateOrCreate(['domain' => $domain, 'request_by_admin' => 1 ], [
                        'domain' => $domain,
                        'endpoint' => $end_point,
                        'api_key' => $decrypted_key,
                        'request_by_admin' => 1,
                        'request_by_id' => Auth::user()->id,
                        'is_only_key_check' => 1
                    ]);
                    $added_count++;
                }
            }
        }
        if( $added_count > 0){
            return response()->json(['code'=>200, 'message'=>'Added ' . $added_count . ' providers'], 200);
        }
        else {
            return response()->json(['code'=>400, 'message'=>'Invalid providers'], 200);
        }
    }

   
    public function importOneProviderServiceList($provider_id){
        // set max running time as 20min
        ini_set('max_execution_time', 1200);
        // 
        $provider = Provider::where('id', $provider_id)->first();
        if(!$provider['endpoint'] || !$provider['api_key']){
            return response()->json(['code'=>400, 'message'=>'Invalid API Key/Endpoint'], 200);
        }

        $api_key = $this->decrypt($provider['api_key']);
        
        $domain = $provider['domain'];
        $url = rtrim($this->check_protocol($domain), '/');
        $end_point = $provider['endpoint'];
        $response = $this->urlExists($url);
        
        if(!$response) {
            $provider->is_activated = 0;
            $provider->save();
            return response()->json(['code'=>400, 'message'=>'Domain name is not exist'], 200);
        }
       
        // check API template from DB
        if($provider['api_template']){
            if(!$provider['real_url']){
                $provider->real_url = $url;
                $provider->save();
            }

            $result_status = $this->scraping_services($url . $end_point, $api_key, $provider);
            if($result_status){
                $provider->service_count = $result_status;
                $provider->save();
                return response()->json(['code'=>200, 'message'=>'Successfully added ' . $result_status . ' records', 'added_count' => $result_status], 200);
            }
            else 
                return response()->json(['code'=>400, 'message'=>'Invalid API Key/EndPoint or Need to add API Template.'], 200); 
        } 

        // check API template
        $api_check = $this->checkAPITemplate($url . $end_point, $api_key);  
                    
        if($api_check['status'] != 1){
            return response()->json(['code'=>400, 'message'=>'Make sure APIKey/Endpoint or Add API template'], 200);
        } 

        $provider->api_template = $api_check['apiTemplate'];
        $provider->real_url = $url;
        $provider->save();
        
        // start scraping
        $result_status = $this->scraping_services($url . $end_point, $api_key, $provider);
        if($result_status){
            $provider->service_count = $result_status;
            $provider->save();
            return response()->json(['code'=>200, 'message'=>'Successfully added ' . $result_status . ' records', 'added_count' => $result_status], 200); 
        }
             
        return response()->json(['code'=>400, 'message'=>'Invalid API Key/EndPoint or Need to add API Template'], 200); 
    }

    private function scraping_services($url, $key, $provider){
        $pro = null;
        $added_count = 0;
        switch($provider['api_template']){
            case 'PerfectPanel':
                $pro = new PerfectPanel($url, $key);
                break;
            case 'SmmPanel':
                $pro = new SmmPanel($url, $key);
                break;
        }
        if(!$pro)
            return false;
      
        $response = $pro->services();  
        $services = json_decode( json_encode($response), true );

        // base_carrency table load
        $base_currencies = Currency::where("id", 1)->first();

        // check if API is working correctly
        if(is_array($services) && count($services) > 0 && isset($services[0]['name'])){
            foreach ($services as $item) {
                $service  = Service::where('provider_id', $provider['id'])->where('service', $item['service'])->first();
                         
                $rate = ((float) $item['rate']);       
                // have a over flow bug
                if($rate > 99999999999)
                    $rate = 99999999999;

                // convert currency to USD 
                $rate_usd = $rate;
                if(strtoupper($provider['currency']) != "USD" && $base_currencies[$provider['currency']] && $base_currencies[$provider['currency']] != 0 ){
                    $rate_usd = $rate / $base_currencies[$provider['currency']];
                }

                Service::updateOrCreate(['provider_id' => $provider['id'], 'service' => $item['service']], [
                    'provider_id' => $provider['id'],
                    'provider' => $provider['domain'],
                    'default_currency' => $provider['currency'],
                    'service' => $item['service'],
                    'name' => str_replace("\\", "\\\\", str_replace("'", "''", $item['name'])),
                    'type' => (isset($item['type']) ? $item['type'] : "NULL"),
                    'rate' => $rate,
                    'rate_usd' => $rate_usd,
                    'min' =>  (isset($item['min']) ? ((int)$item['min']) : "NULL"),
                    'max' =>  (isset($item['max']) ? ((int)$item['max']) : "NULL"),
                    'dripfeed' => (isset($item['dripfeed']) ? $item['dripfeed'] ? 1 : 0 : 0),
                    'refill' => (isset($item['refill']) ? $item['refill'] ? 1 : 0 : 0),
                    'cancel' => (isset($item['cancel']) ? $item['cancel'] ? 1 : 0 : 0),
                    'category' => str_replace("\\", "\\\\", str_replace("'", "''", $item['category'])),
                    'status' => "1",
                    'created_at' => date("Y-m-d H:i:s")
                ]);
                $added_count++;
            }
        } else {
            return false;
        }
        
        return $added_count;
    }
}

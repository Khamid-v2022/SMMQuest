<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\Provider;

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
        
        return view('content.adminside.provider-management', [
            'pageConfigs'=> $pageConfigs, 
            'providers' => $providers
        ]);
    }

    public function addProvider(Request $request) {
        $request->validate([
          'domain' => 'required'
        ]);

        // remove http://, https://, remove / from last of url
        $domain = rtrim(preg_replace( "#^[^:/.]*[:/]+#i", "", $request->domain), '/');
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
            $api_check = $this->checkAPITemplate($url . $end_point, $request->api_key);
            if($api_check['status']){
                if($request->action_type == "add"){
                    $user_provider = Provider::create([
                        'domain' => $domain,
                        'is_activated' => ($request->is_activated ? 1 : 0),
                        'api_key' => $this->encrypt($request->api_key),
                        'is_valid_key' => 1,
                        'api_template' =>  $api_check['apiTemplate'],
                        'balance' =>  $api_check['balance'],
                        'currency' =>  $api_check['currency'],
                        'endpoint' => $end_point,
                        'created_at' => date("Y-m-d H:i:s")
                    ]);
                } else {
                    $user_provider = Provider::where('id', $request->selected_id)->update([
                        'domain' => $domain,
                        'is_activated' => ($request->is_activated ? 1 : 0),
                        'api_key' => $this->encrypt($request->api_key),
                        'is_valid_key' => 1,
                        'api_template' =>  $api_check['apiTemplate'],
                        'balance' =>  $api_check['balance'],
                        'currency' =>  $api_check['currency'],
                        'endpoint' => $end_point,
                        'updated_at' => date("Y-m-d H:i:s")
                    ]);
                }

                return response()->json(['code'=>200, 'message'=>'Sussess'], 200);
            } else {
                return response()->json(['code'=>400, 'message'=>'The API key or endpoint is incorrect. Please check again!'], 200);
            }
        } else {
            return response()->json(['code'=>400, 'message'=>'This domain name is not exist'], 200);
        }
    }

    public function deleteProvider(Request $request) {
        Provider::where('id', $request->id)->delete();
        return response()->json(['code'=>200, 'message'=>'Deleted successfully'], 200);
    }

    public function changeAPIKey(Request $request) {
        Provider::where('id', $request->selected_id)->update(['api_key' => $this->encrypt($request->api_key, env('ENCRYPT_KEY'))]);
        return response()->json(['code'=>200, 'message'=>'Updated successfully'], 200);
    }

    public function updateActivate(Request $request) {
        Provider::where('id', $request->selected_id)->update(['is_activated' => $request->is_active]);
        return response()->json(['code'=>200, 'message'=>'Updated successfully'], 200);
    }

    private function urlExists($url = NULL) {
        $headers = @get_headers($url);
        if(!$headers || strpos($headers[0], '404')) {
            $exists = false;
        }
        else {
            $exists = true;
        }
        return $exists;
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
        if($balance && !isset($balance['error'])){
            return array (
                'status'=> true, 
                'apiTemplate'=> 'PerfectPanel', 
                'balance' => $balance['balance'],
                'currency' => $balance['currency']
            );
        }

        // SmmPanel     https://smmpanele.ru/api/v2
        $smmPanel = new SmmPanel($url, $key);
        $services = $smmPanel->services();
        $services = json_decode( json_encode($services), true );

        if(is_array($services) && count($services) > 0){
            if(isset($services[0]['name'])){
                return array (
                    'status'=> true, 
                    'apiTemplate'=> 'SmmPanel', 
                    'balance' => null,
                    'currency' => null
                );
            }
        }

        // https://monksmm.tech/api/v1  -- Same with Perfect panel
        // $monksmmPanel = new MonksmmPanel($url, $key);
        // $balance = $monksmmPanel->balance();
        // $balance = json_decode( json_encode($balance), true );

        // if(!isset($balance['error'])){
        //     return array (
        //         'status'=> true, 
        //         'apiTemplate'=> 'MonksmmPanel', 
        //         'balance' => $balance['balance'],
        //         'currency' => $balance['currency']
        //     );
        // }



        return array (
            'status'=> false
        );
    }

    private function encrypt($message)
    {
        $cipher_method = 'aes-128-ctr';
        
        $enc_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher_method));
        $crypted_key = openssl_encrypt($message, $cipher_method, env('ENCRYPT_KEY'), 0, $enc_iv) . "::" . bin2hex($enc_iv);

        return $crypted_key;
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

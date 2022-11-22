<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\Provider;

use Illuminate\Support\Facades\Http;

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

    public function addProvider(Request $request){
        $request->validate([
          'domain' => 'required'
        ]);

        // remove http://, https://
        $url = preg_replace( "#^[^:/.]*[:/]+#i", "", $request->domain);

        $domain = "http://" . $url;
        if($request->action_type == "add"){
            // check aready registred 
            $provider = Provider::where('domain', $url)->first();
            if($provider){
                return response()->json(['code'=>422, 'message'=>'Already registred.'], 200);
            }
        } 

        // checking domain is working or not
        $response = $this->urlExists($domain);
        if($response) {
            // check API key working or not
            $api_check = $this->checkAPI($domain, $request->api_key);
            
            if($api_check){
                if($request->action_type == "add"){
                    $user_provider = Provider::create([
                        'domain' => $url,
                        'is_activated' => ($request->is_activated ? 1 : 0),
                        'api_key' => $this->encrypt($request->api_key, env('ENCRYPT_KEY')),
                        'is_valid_key' => 1,
                        'endpoint' => '/api/v2',
                        'created_at' => date("Y-m-d H:i:s")
                    ]);
                } else {
                    $user_provider = Provider::where('id', $request->selected_id)->update([
                        'domain' => $url,
                        'is_activated' => ($request->is_activated ? 1 : 0),
                        'api_key' => $this->encrypt($request->api_key, env('ENCRYPT_KEY')),
                        'is_valid_key' => 1,
                        'endpoint' => '/api/v2',
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

    public function deleteProvider(Request $request){
        Provider::where('id', $request->id)->delete();
        return response()->json(['code'=>200, 'message'=>'Deleted successfully'], 200);
    }

    public function changeAPIKey(Request $request){
        Provider::where('id', $request->selected_id)->update(['api_key' => $this->encrypt($request->api_key, env('ENCRYPT_KEY'))]);
        return response()->json(['code'=>200, 'message'=>'Updated successfully'], 200);
    }

    public function updateActivate(Request $request){
        Provider::where('id', $request->selected_id)->update(['is_activated' => $request->is_active]);
        return response()->json(['code'=>200, 'message'=>'Updated successfully'], 200);
    }

    private function urlExists($url = NULL)
    {
        $url = preg_replace( "#^[^:/.]*[:/]+#i", "", $url);
        $url = "http://" . $url;
        $headers = @get_headers($url);
        if(!$headers || strpos($headers[0], '404')) {
            $exists = false;
        }
        else {
            $exists = true;
        }
        return $exists;
    }

    private function checkAPI($url, $key){
        $url = $url . '/api/v2?action=services&key=' . $key;
        $json = file_get_contents($url);
        
        $response = json_decode($json, true);
  
        if(is_array($response) && count($response) > 0){
            if(isset($response[0]['name']))
                return true;
        }
        return false;
    }

    private function encrypt($message, $key, $encode = false)
    {
        $nonceSize = openssl_cipher_iv_length('aes-256-ctr');
        $nonce = openssl_random_pseudo_bytes($nonceSize);
        
        $ciphertext = openssl_encrypt(
            $message,
            'aes-256-ctr',
            $key,
            OPENSSL_RAW_DATA,
            $nonce
        );
        
        // Now let's pack the IV and the ciphertext together
        // Naively, we can just concatenate
        if ($encode) {
            return base64_encode($nonce.$ciphertext);
        }
        return $nonce.$ciphertext;
    }

    private function decrypt($message, $key, $encoded = false)
    {
        if ($encoded) {
            $message = base64_decode($message, true);
            if ($message === false) {
                throw new Exception('Encryption failure');
            }
        }

        $nonceSize = openssl_cipher_iv_length('aes-256-ctr');
        $nonce = mb_substr($message, 0, $nonceSize, '8bit');
        $ciphertext = mb_substr($message, $nonceSize, null, '8bit');
        
        $plaintext = openssl_decrypt(
            $ciphertext,
            'aes-256-ctr',
            $key,
            OPENSSL_RAW_DATA,
            $nonce
        );
        
        return $plaintext;
    }
}

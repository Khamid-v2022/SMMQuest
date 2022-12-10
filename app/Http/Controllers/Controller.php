<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function __construct(){
       
    }

    protected function randomString($length) {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }

    protected function encrypt($message)
    {
        $cipher_method = 'aes-128-ctr';
        
        $enc_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher_method));
        $crypted_key = openssl_encrypt($message, $cipher_method, env('ENCRYPT_KEY'), 0, $enc_iv) . "::" . bin2hex($enc_iv);

        return $crypted_key;
    }

    protected function getDomain($url){
        // remove http://, https://, www
        $url = preg_replace('#^www\.(.+\.)#i', '$1', preg_replace( "#^[^:/.]*[:/]+#i", "", $url));
        $url = 'https://' . $url;

        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        $domain = str_replace('www.', '', $domain);
        return $domain;
    }

    protected function decrypt($message)
    {
        list($crypted_token, $enc_iv) = explode("::", $message);
        $cipher_method = 'aes-128-ctr';
        $token = openssl_decrypt($crypted_token, $cipher_method, env('ENCRYPT_KEY'), 0, hex2bin($enc_iv));
        return $token;
    }

    protected function check_protocol($domain){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $domain);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_exec($ch);
    
        $real_url =  curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        return $real_url;
    }
    
    protected function urlExists($url = NULL) {
        $headers = @get_headers($url);
        if(!$headers || strpos($headers[0], '404')) {
            $exists = false;
        }
        else {
            $exists = true;
        }
        return $exists;
    }
}

<?php 
    require_once "../../vendor/autoload.php";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();

    function decrypt_key($message)
    {
        list($crypted_token, $enc_iv) = explode("::", $message);
        $cipher_method = 'aes-128-ctr';
        $token = openssl_decrypt($crypted_token, $cipher_method, $_ENV['ENCRYPT_KEY'], 0, hex2bin($enc_iv));
        return $token;
    }

    // get real url with http/https from domain name 
    function check_protocol($domain){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $domain);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_exec($ch);
    
        $real_url =  curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        return $real_url;
    }

    // check URL is working or not(404 error)
    function urlExists($url) {
        $headers = @get_headers($url);
        if(!$headers || strpos($headers[0], '404')) {
            $exists = false;
        }
        else {
            $exists = true;
        }
        return $exists;
    }
?>
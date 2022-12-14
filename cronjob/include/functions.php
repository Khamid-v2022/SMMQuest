<?php 
    require_once "../../vendor/autoload.php";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();

    // providers
    require_once "providers/PerfectPanel.php";
    require_once "providers/SmmPanel.php";

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

    function checkAPIKeyWithTemplate($url, $key, $template) {
        
        switch($template){
            case 'PerfectPanel':
                $perfectPanel = new PerfectPanel($url, $key);

                $balance = $perfectPanel->balance();
                $balance = json_decode( json_encode($balance), true );

                if($balance){
                    if(!isset($balance['error'])){
                        // valid Key
                        return array (
                            'status'=> 1,
                            'balance' => $balance['balance'],
                            'currency' => isset($balance['currency']) ? $balance['currency'] : NULL
                        );
                    } else {
                        if($balance['error'] == "Incorrect request"){
                            // frozon status
                            return array (
                                'status'=> 3,
                                'balance' => "NULL",
                                'currency' => NULL
                            );
                        } else {    
                            // invalid Key
                            return array (
                                'status'=> 2,
                                'balance' => "NULL",
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
                if($services){
                    if(is_array($services) && count($services) > 0 && isset($services[0]['name'])){
                        // return true;
                        return array (
                            'status'=> 1,
                            'balance' => "NULL",
                            'currency' => NULL
                        );
                    } else {
                        // invalid key
                        return array (
                            'status'=> 2,
                            'balance' => "NULL",
                            'currency' => NULL
                        );
                    }
                } 
               
                break;
        }

        // return false;
        return array (
            'status'=> 0,
            'balance' => "NULL",
            'currency' => NULL
        );
    }


    function detectAPITemplate($url, $key){
        // perfect panel
        $perfectPanel = new PerfectPanel($url, $key);

        $balance = $perfectPanel->balance();
        $balance = json_decode(json_encode($balance), true );

        if($balance){
            if(!isset($balance['error'])){
                return array (
                    'status'=> 1, 
                    'apiTemplate'=> 'PerfectPanel', 
                    'balance' => isset($balance['balance']) ? $balance['balance'] : "NULL",
                    'currency' => isset($balance['currency']) ? $balance['currency'] : NULL
                );
            } else {
                if($balance['error'] == "Incorrect request"){
                    // Frozon status
                    return array (
                        'status'=> 3, 
                        'apiTemplate'=> 'PerfectPanel', 
                        'balance' => "NULL",
                        'currency' => NULL
                    );
                } else {
                    // wrong API key
                    return array (
                        'status'=> 2, 
                        'apiTemplate'=> 'PerfectPanel', 
                        'balance' => "NULL",
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
                    'balance' => "NULL",
                    'currency' => NULL
                );
            } else {
                return array (
                    'status'=> 2, 
                    'apiTemplate'=> 'SmmPanel', 
                    'balance' => "NULL",
                    'currency' => NULL
                );
            }
        } 

        // wrong url or endpoint
        return array (
            'status'=> 0
        );
    }

    function getServicesFromPanel($url, $key, $template){
        $pro = null;
        switch($template){
            case 'PerfectPanel':
                $pro = new PerfectPanel($url, $key);
                break;
            case 'SmmPanel':
                $pro = new SmmPanel($url, $key);
                break;
        }

        if($pro){
            $response = $pro->services();  
            return json_decode( json_encode($response), true );
        }
        return false;
    }
?>
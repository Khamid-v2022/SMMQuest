<?php 
    require_once "include/db_configure.php";
    require_once "include/functions.php";
    
    // providers
    require_once "providers/PerfectPanel.php";
    require_once "providers/SmmPanel.php";

    checkHoldProvider();
    $conn->close();

    function checkHoldProvider() {
        echo "CHECK HOLD PROVIDER STARTED: " . date("Y-m-d H:i:s") . PHP_EOL . "<br/>";

        global $conn;

        $sql = "SELECT * FROM hold_providers ORDER BY request_by_admin DESC, created_at";

        $result = $conn->query($sql);
        if($result->num_rows == 0){
            echo "No holded providers" . PHP_EOL . "<br/>";
            echo "FUNCTION ENDED: " . date("Y-m-d H:i:s");
            return;
        }

        while($row = $result->fetch_assoc()){
            if($row['is_only_key_check'] == 1){
                // domain is working, only need to verify key
                // get provider
                $sql_provider = "SELECT * FROM providers WHERE domain = '" . $row['domain'] . "'";
                $result_provider = $conn->query($sql);
                
                if($result_provider->num_rows > 0){
                    $sel_provider = $result_provider->fetch_assoc();
                    
                    if($row['api_key']){
                        $api_key = decrypt_key($row['api_key']);
                        $api_check = checkAPIKey($sel_provider['real_url'] . $sel_provider['endpoint'], $api_key, $sel_provider['api_template']);
                        if($api_check) {
                            $sql_add = "INSERT INTO user_provider( user_id, provider_id, is_favorite, api_key, is_enabled, is_valid_key, created_at) VALUES (" 
                                . $row['request_by_id'] . ", " 
                                . $sel_provider['id'] . ", " 
                                . "0, '" 
                                . $row['api_key'] . "', " 
                                . "1, 1, '" . $item['created_at'] . "')";
                        } else {
                            $sql_add = "INSERT INTO user_provider( user_id, provider_id, is_favorite, api_key, is_enabled, is_valid_key, created_at) VALUES (" 
                                . $row['request_by_id'] . ", " 
                                . $sel_provider['id'] . ", " 
                                . "0, '" 
                                . $row['api_key'] . "', " 
                                . "1, 0, '" . $item['created_at'] . "')";
                        }
                    } else {
                        $sql_add = "INSERT INTO user_provider( user_id, provider_id, is_favorite, api_key, is_enabled, is_valid_key, created_at) VALUES (" 
                            . $row['request_by_id'] . ", " 
                            . $sel_provider['id'] . ", " 
                            . "0, '" 
                            . $row['api_key'] . "', " 
                            . "1, 0, '" . $item['created_at'] . "')";
                    }

                    $conn->query($sql_add);
                } 
                
            } 
            else {
                // check domain aready exist or not
                $exist_sql = "SELECT * FROM providers WHERE domain = '" . $row['domain'] . "' ";
                $result_exist = $conn->query($exist_sql);
                
                // added by admin?
                if($row['request_by_admin'] == 1){
                    if($result_exist->num_rows == 0){
                        $real_url = rtrim(check_protocol($row['domain']), '/');
                        // checking domain is working or not
                        $response = urlExists($real_url);

                        if($response) {
                            if($row['api_key']){
                                // check API key working or not
                                $api_key = decrypt_key($row['api_key']);
                                $api_check = checkAPITemplate($real_url . $row['endpoint'], $api_key);
                                if($api_check['status'] > 0 ){
                                    $is_valid_key = 0;
                                    if($api_check['status'] == 1){
                                        $is_valid_key = 1;
                                    } else {
                                        // invalid key
                                        $is_valid_key = 0;
                                    }

                                    $sql_add_by_admin = "INSERT INTO providers(domain, real_url, endpoint, api_key, is_valid_key, is_activated, api_template, balance, currency, activated_at, is_hold, created_at) VALUES ('"
                                        . $row['domain'] . "', '"
                                        . $real_url . "', '"
                                        . $row['endpoint'] . "', '"
                                        . $row['api_key'] . "', "
                                        . $is_valid_key . ", 1, '"
                                        . $api_check['apiTemplate'] . "', "
                                        . $api_check['balance'] . ", '"
                                        . $api_check['currency'] . "', '"
                                        . date("Y-m-d H:i:s") . "', 0, '"
                                        . $row['created_at']
                                        . "')";

                                } else {
                                    $sql_add_by_admin = "INSERT INTO providers(domain, real_url, endpoint, api_key, is_valid_key, is_activated, is_hold, created_at) VALUES ('"
                                        . $row['domain'] . "', '"
                                        . $real_url . "', '"
                                        . $row['endpoint'] . "', '"
                                        . $row['api_key'] . "', "
                                        . "0, 0, 0, '"
                                        . $row['created_at']
                                        . "')";
                                }

                            } else {
                                // empty api_key
                                $sql_add_by_admin = "INSERT INTO providers(domain, real_url, endpoint, api_key, is_valid_key, is_activated, is_hold, created_at) VALUES ('"
                                        . $row['domain'] . "', '"
                                        . $real_url . "', '"
                                        . $row['endpoint'] . "', "
                                        . "NULL, 0, 0, 0, '"
                                        . $row['created_at']
                                        . "')";
                            }

                            $conn->query($sql_add_by_admin);
                        } else {
                            // domain name is not exist - fake
                        }
                    }
                } else {
                    if($result_exist->num_rows == 0){
                        $real_url = rtrim(check_protocol($row['domain']), '/');
                        // checking domain is working or not
                        $response = urlExists($real_url);

                        // added by user
                        if($response) { 
                            // add new provider
                            $sql_add_by_user = "INSERT INTO providers(domain, real_url, is_valid_key, is_activated, is_hold, request_by, created_at) VALUES ('"
                                        . $row['domain'] . "', '"
                                        . $real_url . "', "
                                        . " 0, 0, 1, " 
                                        . $row['request_by_id'] . ", '"
                                        . $row['created_at']
                                        . "')";
                            if ($conn->query($sql_add_by_user) == TRUE) {
                                $last_id = $conn->insert_id;
                                $sql_user_provider = "INSERT INTO user_provider(user_id, provider_id, is_favorite, api_key, is_enabled, is_valid_key, created_at) VALUES (" 
                                    . $row['request_by_id'] . ", "
                                    . $last_id . ", 0, '"
                                    . $row['api_key'] . "', 1, 0, '"
                                    . $row['created_at']
                                    . "')";
                                $conn->query($sql_user_provider);
                            }

                        }
                        else {
                            // domain name is not exist - fake
                        }
                    } else {
                        // add only user-provider
                        $row_exist = $result_exist->fetch_assoc();
                        
                        $is_valid_key = 0;

                        if($row_exist['is_activated'] == "1"){
                            // check API key is valid
                            $api_key = decrypt_key($row['api_key']);
                            $api_check = checkAPIKey($row_exist['real_url'] . $row_exist['endpoint'], $api_key, $row_exist['api_template']);
                            if($api_check) {
                                $is_valid_key = 1;
                            }
                        }
                       

                        $sql_user_provider = "INSERT INTO user_provider(user_id, provider_id, is_favorite, api_key, is_enabled, is_valid_key, created_at) VALUES (" 
                                . $row['request_by_id'] . ", "
                                . $row_exist['id'] . ", 0, '"
                                . $row['api_key'] . "', 1, " . $is_valid_key . ", '"
                                . $row['created_at']
                                . "')";
                        $conn->query($sql_user_provider);
                    }

                }

            }
            // delete from temp table
            
            $delete_sql = "DELETE FROM hold_providers WHERE domain = '" . $row['domain'] . "' AND request_by_admin = " . $row['request_by_admin'] . " AND request_by_id = " . $row['request_by_id'];
            $conn->query($delete_sql);
          
        }
     
        echo "FUNCTION ENDED: " . date("Y-m-d H:i:s");
    }

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

    function checkAPIKey($url, $key, $template) {
        
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

    function checkAPITemplate($url, $key) {
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

        // wrong url or endpoint
        return array (
            'status'=> 0
        );
    }

?>
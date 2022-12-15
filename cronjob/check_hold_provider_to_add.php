<?php 
    require_once "include/db_configure.php";
    require_once "include/functions.php";
    


    checkHoldProvider();
    $conn->close();

    function checkHoldProvider() {
        global $conn;

        $sql = "SELECT * FROM hold_providers ORDER BY request_by_admin DESC, created_at";

        $result = $conn->query($sql);
        if($result->num_rows == 0){
            return;
        }
        
        echo "HOLD STARTED: " . date("Y-m-d H:i:s") . PHP_EOL;
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
                        $api_check = checkAPIKeyWithTemplate($sel_provider['real_url'] . $sel_provider['endpoint'], $api_key, $sel_provider['api_template']);

                        $valid_key = 0;
                        if($api_check['status'] == 1) {
                            $valid_key = 1;
                            
                        } else if($api_check['status'] == 2){
                            // invalid
                            $valid_key = 0;
                        }

                        if($row['request_by_admin'] == 1){
                            // requested by admin
                            $sql_update = "UPDATE providers SET api_key='" . $row['api_key'] . "', balance=" . $api_check['balance'] . ", currency='" . ($api_check['currency'] ? $api_check['currency'] : "") . "', is_valid_key=" . $valid_key . ", updated_at='" . $row['created_at'] . "' WHERE id = " . $sel_provider['id'];
                            $conn->query($sql_update);
                        }
                        else{
                            // requested by user
                            // check provider is registred to this user already
                            $sql_exist = "SELECT * FROM user_provider WHERE user_id = " . $row['request_by_id'] . " AND provider_id = " . $sel_provider['id'];
                            $result_exist = $conn->query($sql_exist);
                            
                            if($result_exist->num_rows == 0){
                                $sql_add = "INSERT INTO user_provider( user_id, provider_id, is_favorite, api_key, user_balance, balance_currency, is_enabled, is_valid_key, created_at) VALUES (" 
                                        . $row['request_by_id'] . ", " 
                                        . $sel_provider['id'] . ", " 
                                        . "0, '" 
                                        . $row['api_key'] . "', " 
                                        . $api_check['balance'] . ", '"
                                        . ($api_check['currency'] ? $api_check['currency'] : "") . "', "
                                        . "1, " . $valid_key . ", '" . $row['created_at'] . "')";
                                $conn->query($sql_add);
                            } else {
                                // update
                                $sql_update = "UPDATE user_provider SET api_key='" . $row['api_key'] . "', user_balance=" . $api_check['balance'] . ", balance_currency='" . ($api_check['currency'] ? $api_check['currency'] : "") . "', is_enabled=1, is_valid_key=" . $valid_key . ", updated_at='" . $row['created_at'] . "' WHERE user_id = " . $row['request_by_id'] . " AND provider_id = " . $sel_provider['id'];
                                $conn->query($sql_update);
                            }
                        }                       
                    } else {
                        if($row['request_by_admin'] == 1){
                            // requested by admin
                            // No meaning
                        } else {
                            // requested by user
                            // No meaning
                        }
                    }
                } 
            } 
            else {
                // check domain aready exist or not
                $exist_sql = "SELECT * FROM providers WHERE domain = '" . $row['domain'] . "'";
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
                                $api_check = detectAPITemplate($real_url . $row['endpoint'], $api_key);
                                if($api_check['status'] > 0 ){
                                    $is_valid_key = 0;
                                    $is_frozon = 0;
                                    if($api_check['status'] == 1){
                                        $is_valid_key = 1;
                                    } else if($api_check['status'] == 3) {
                                       $is_frozon = 1;
                                    }

                                    $sql_add_by_admin = "INSERT INTO providers(domain, real_url, endpoint, api_key, is_valid_key, is_activated, is_frozon, api_template, balance, currency, activated_at, is_hold, created_at) VALUES ('"
                                        . $row['domain'] . "', '"
                                        . $real_url . "', '"
                                        . $row['endpoint'] . "', '"
                                        . $row['api_key'] . "', "
                                        . $is_valid_key . ", 1, " . $is_frozon . ", '"
                                        . $api_check['apiTemplate'] . "', "
                                        . $api_check['balance'] . ", '"
                                        . ($api_check['currency'] ? $api_check['currency'] : "") . "', '"
                                        . date("Y-m-d H:i:s") . "', 0, '"
                                        . $row['created_at']
                                        . "')";

                                } else {
                                    // wrong EndPoint
                                    $sql_add_by_admin = "INSERT INTO providers(domain, real_url, endpoint, api_key, is_valid_key, is_activated, is_hold, created_at) VALUES ('"
                                        . $row['domain'] . "', '"
                                        . $real_url . "', '"
                                        . $row['endpoint'] . "', '"
                                        . $row['api_key'] . "', "
                                        . "0, 1, 0, '"
                                        . $row['created_at']
                                        . "')";
                                }

                            } else {
                                // empty api_key
                                $sql_add_by_admin = "INSERT INTO providers(domain, real_url, endpoint, api_key, is_valid_key, is_activated, is_hold, created_at) VALUES ('"
                                        . $row['domain'] . "', '"
                                        . $real_url . "', '"
                                        . $row['endpoint'] . "', "
                                        . "NULL, 0, 1, 0, '"
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
                                        . " 0, 1, 1, " 
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

                        $sql_up_exist = "SELECT * FROM user_provider WHERE user_id = " . $row['request_by_id'] . " AND provider_id = " . $row_exist['id'];
                        $result_up_exist = $conn->query($sql_up_exist);
                            



                        if($row_exist['is_activated'] == "1"){
                            // check API key is valid
                            $api_key = decrypt_key($row['api_key']);
                            $api_check = checkAPIKeyWithTemplate($row_exist['real_url'] . $row_exist['endpoint'], $api_key, $row_exist['api_template']);
                            if($api_check['status'] == 1) {
                                $is_valid_key = 1;
                            }

                            if($result_up_exist->num_rows == 0){
                                $sql_user_provider = "INSERT INTO user_provider(user_id, provider_id, is_favorite, api_key, user_balance, balance_currency, is_enabled, is_valid_key, created_at) VALUES (" 
                                    . $row['request_by_id'] . ", "
                                    . $row_exist['id'] . ", 0, '"
                                    . $row['api_key'] . "', "
                                    . $api_check['balance'] . ", '"
                                    . ($api_check['currency'] ? $api_check['currency'] : "")
                                    . "', 1, " . $is_valid_key . ", '"
                                    . $row['created_at']
                                    . "')";
                            } else {
                                $sql_user_provider =  "UPDATE user_provider SET api_key='" . $row['api_key'] . "', user_balance=" . $api_check['balance'] . ", balance_currency='" . ($api_check['currency'] ? $api_check['currency'] : "") . "', is_enabled=1, is_valid_key=" . $is_valid_key . ", updated_at='" . $row['created_at'] . "' WHERE user_id = " . $row['request_by_id'] . " AND provider_id = " . $row_exist['id'];
                            }
                            
                        } else {
                            if($result_up_exist->num_rows == 0){
                                $sql_user_provider = "INSERT INTO user_provider(user_id, provider_id, is_favorite, api_key, is_enabled, is_valid_key, created_at) VALUES (" 
                                    . $row['request_by_id'] . ", "
                                    . $row_exist['id'] . ", 0, '"
                                    . $row['api_key'] . "', "
                                    . ", 0, 0, '"
                                    . $row['created_at']
                                    . "')";
                            } else {
                                $sql_user_provider =  "UPDATE user_provider SET api_key='" . $row['api_key'] . "', is_enabled=0, is_valid_key=0, updated_at='" . $row['created_at'] . "' WHERE user_id = " . $row['request_by_id'] . " AND provider_id = " . $row_exist['id'];
                            }
                        }
                        $conn->query($sql_user_provider);
                    }

                }

            }
            // delete from temp table
            
            $delete_sql = "DELETE FROM hold_providers WHERE domain = '" . $row['domain'] . "' AND request_by_admin = " . $row['request_by_admin'] . " AND request_by_id = " . $row['request_by_id'];
            $conn->query($delete_sql);  
        }
        echo "HOLD ENDED: " . date("Y-m-d H:i:s") . PHP_EOL;
    }

?>
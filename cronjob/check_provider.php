<?php 
    require_once "include/db_configure.php";
    require_once "include/functions.php";
    

    // check New providers at first
    checkNewProviders();

    // check old providers
    checkProvider();

    $conn->close();


    function checkNewProviders() {
        
        global $conn;
        
        $sql = "SELECT * FROM providers WHERE domain IS NOT NULL AND ENDPOINT IS NOT NULL AND api_key IS NOT NULL AND (is_valid_key = 0 OR api_template IS NULL OR is_activated = 0)";
        $result = $conn->query($sql);
        
        if($result->num_rows == 0){
            return;
        }

        echo "New STARTED: " . date("Y-m-d H:i:s") . PHP_EOL;

        while($row = $result->fetch_assoc()){

            $real_url = '';
            if($row['real_url']){
                $real_url = $row['real_url'];
            } else {
                // get real_url
                $real_url = rtrim(check_protocol($row['domain']), '/');
                $response = urlExists($real_url);
                
                if(!$response) {
                    // website is not working
                    $sql_update = "UPDATE providers SET real_url = '" . $real_url . "', is_activated = 0, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                    $conn->query($sql_update);

                    // skip
                    continue;
                }
            }

            // detect API template with $real_url
            $api_key = decrypt_key($row['api_key']);
            $api_check = detectAPITemplate($real_url . $row['endpoint'], $api_key);

            if($api_check['status'] > 0){
                $api_is_valid = 0;
                $is_frozon = 0;
                if($api_check['status'] == 1){
                  $api_is_valid = 1;
                } else if($api_check['status'] == 3) {
                   $is_frozon = 1;
                }

                $sql_update = "UPDATE providers SET real_url = '" . $real_url . "', is_activated = 1, is_valid_key = " . $api_is_valid . ", is_frozon = " . $is_frozon . ", api_template = '" . $api_check['apiTemplate'] . "', updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
            } else {
                // endpoint is wrong
                $response = urlExists($real_url);
                if(!$response) {
                    // website is not working
                    $sql_update = "UPDATE providers SET real_url = '" . $real_url . "', is_activated = 0, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                    continue;
                } else {
                    // website is working but endpoint is wrong
                    $sql_update = "UPDATE providers SET real_url = '" . $real_url . "', is_activated = 1, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                }
               
            }
            $conn->query($sql_update);
        }
        echo "New ENDED: " . date("Y-m-d H:i:s") . PHP_EOL;
    }

    function checkProvider() {
        echo "STARTED: " . date("Y-m-d H:i:s") . PHP_EOL;
        $time_start = microtime(true);
        global $conn;

        $sql_total = "SELECT * FROM providers WHERE api_key IS NOT NULL AND is_hold = 0";
        $result_total = $conn->query($sql_total);
        if($result_total->num_rows == 0){
            echo "No providers" . PHP_EOL;
            echo "ENDED: " . date("Y-m-d H:i:s") . PHP_EOL;
            return;
        }

        // get last check providers
        $sql_cron = "SELECT * FROM cron_check";
        $result_cron = $conn->query($sql_cron);
        if($result_cron->num_rows == 0){
            echo "cron_check table emplty" . PHP_EOL;
            return;
        }

        $cron_config = $result_cron->fetch_assoc();

        $sql = "SELECT * FROM providers WHERE api_key IS NOT NULL AND is_hold = 0 AND id > " . $cron_config['last_provider_check_id'];

        $result = $conn->query($sql);
        
        if($result->num_rows == 0){
            // update cron config table
            $cron_update_sql = "UPDATE cron_check SET last_provider_check_id = 0 WHERE id = " . $cron_config['id'];
            $conn->query($cron_update_sql);

            $sql = "SELECT * FROM providers WHERE api_key IS NOT NULL AND is_hold = 0" ;
            $result = $conn->query($sql);
        }

        $enabled = 0;
        $disabled = 0;

        while($row = $result->fetch_assoc()){

            // check with db url 
            // check API key working or not
            if($row['api_key'] && $row['endpoint'] && $row['api_template']){
                if($row['real_url']){
                    $api_key = decrypt_key($row['api_key']);
                    $api_check = checkAPIKeyWithTemplate($row['real_url'] . $row['endpoint'], $api_key, $row['api_template']);
                    if($api_check['status'] == 1) {
                        // valid key
                        $sql = "UPDATE providers SET is_activated = 1, is_valid_key = 1, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                        if($row['is_activated'] == 0){
                            $enabled++;
                        }
                        $conn->query($sql);
                    } else if($api_check['status'] == 3) {
                        // frozon status
                        $sql = "UPDATE providers SET is_activated = 1, is_valid_key = 0, is_frozon = 1, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                        $conn->query($sql);
                    }
                    else if($api_check['status'] == 2) {
                        // Invalid key but need to check real_url is valid first
                        $url = rtrim(check_protocol($row['domain']), '/');
                        $response = urlExists($url);
                        if($response){
                            // website is working 
                            if($url == $row['real_url']){
                                // website is working but invalid key
                                $sql = "UPDATE providers SET is_activated = 1, is_valid_key = 0, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                                $conn->query($sql);
                            } else {
                                // check again with updated $url
                                $api_key = decrypt_key($row['api_key']);
                                $api_check_again = checkAPIKeyWithTemplate($url . $row['endpoint'], $api_key, $row['api_template']);
                                if($api_check_again['status'] == 1) {
                                    $sql = "UPDATE providers SET real_url = '" . $url . "', is_activated = 1, is_valid_key = 1, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                                } else if($api_check['status'] == 2){
                                    $sql = "UPDATE providers SET real_url = '" . $url . "', is_activated = 1, is_valid_key = 0, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                                }
                                
                                $conn->query($sql);
                                if($row['is_activated'] == 0){
                                    $enabled++;
                                }
                            }
                            
                        } else { 
                            // website is not working
                            $sql = "UPDATE providers SET real_url = '" . $url . "', is_activated = 0, is_valid_key = 0, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                            $conn->query($sql);
                        }
                    }
                } else {
                    $url = rtrim(check_protocol($row['domain']), '/');
                    $response = urlExists($url);
                    if($response){
                        // website is working 
                        // check with $url
                        $api_key = decrypt_key($row['api_key']);
                        $api_check = checkAPIKeyWithTemplate($url . $row['endpoint'], $api_key, $row['api_template']);
                        if($api_check['status'] == 1) {
                            $sql = "UPDATE providers SET real_url = '" . $url . "', is_activated = 1, is_valid_key = 1, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                        } else if($api_check['status'] == 2){
                            // invalid key
                            $sql = "UPDATE providers SET real_url = '" . $url . "', is_activated = 1, is_valid_key = 0, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                        } else if($api_check['status'] == 3){
                            // frozon
                            $sql = "UPDATE providers SET real_url = '" . $url . "', is_activated = 1, is_valid_key = 0, is_frozon = 1, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                        }
                        
                        $conn->query($sql);
                        if($row['is_activated'] == 0){
                            $enabled++;
                        }
                    } else { 
                        // website is not working
                        $sql = "UPDATE providers SET real_url = '" . $url . "', is_activated = 0, is_valid_key = 0, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                        $conn->query($sql);
                    }
                }
            } else {
                // mean is that key is not provided, couse domain and endpoint is required
                // if($row['endpoint'] && $row['api_template']){
                    // check domain is working
                    $url = rtrim(check_protocol($row['domain']), '/');
                    $response = urlExists($url);
                    if($response){
                        $sql = "UPDATE providers SET real_url = '" . $url . "', is_activated = 1, is_valid_key = 0, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                    } else {
                        $sql = "UPDATE providers SET real_url = '" . $url . "', is_activated = 0, is_valid_key = 0, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                    }
                // } else {
                //     $sql = "UPDATE providers SET is_activated = 0, is_valid_key = 0, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                // }
                $conn->query($sql);
            }

            // update cron config table
            $cron_update_sql = "UPDATE cron_check SET last_provider_check_id = " . $row['id'] . " WHERE id = " . $cron_config['id'];
            $conn->query($cron_update_sql);
            
            $time_end = microtime(true);

            // set running time as 1 hour
            if(($time_end - $time_start)/60 > 60){
                break;
            }
        }

        echo "ENDED: " . date("Y-m-d H:i:s") . PHP_EOL;
    }

   

?>
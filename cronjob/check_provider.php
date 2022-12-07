<?php 
    require_once "include/db_configure.php";
    require_once "include/functions.php";
    
    // providers
    require_once "providers/PerfectPanel.php";
    require_once "providers/SmmPanel.php";

    checkProvider();
    // getAllProviders();
    $conn->close();

    function checkProvider() {
        echo "CHECK PROVIDER FUNCTION STARTED: " . date("Y-m-d H:i:s") . PHP_EOL . "<br/>";
        $time_start = microtime(true);
        global $conn;

        $sql_total = "SELECT * FROM providers WHERE api_key IS NOT NULL AND is_hold = 0";
        $result_total = $conn->query($sql_total);
        if($result_total->num_rows == 0){
            echo "No providers" . PHP_EOL . "<br/>";
            echo "FUNCTION ENDED: " . date("Y-m-d H:i:s");
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
                    $api_check = checkAPITemplate($row['real_url'] . $row['endpoint'], $api_key, $row['api_template']);
                    if($api_check) {
                        $sql = "UPDATE providers SET is_activated = 1, is_valid_key = 1, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                        if($row['is_activated'] == 0){
                            $enabled++;
                        }
                        $conn->query($sql);
                    } else {
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
                                $api_check_again = checkAPITemplate($url . $row['endpoint'], $api_key, $row['api_template']);
                                if($api_check_again) {
                                    $sql = "UPDATE providers SET real_url = '" . $url . "', is_activated = 1, is_valid_key = 1, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                                } else {
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
                        $api_check = checkAPITemplate($url . $row['endpoint'], $api_key, $row['api_template']);
                        if($api_check) {
                            $sql = "UPDATE providers SET real_url = '" . $url . "', is_activated = 1, is_valid_key = 1, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
                        } else {
                            $sql = "UPDATE providers SET real_url = '" . $url . "', is_activated = 1, is_valid_key = 0, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row['id'];
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
                // key is not provided
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

        echo "FUNCTION ENDED: " . date("Y-m-d H:i:s");
    }

    

    function checkAPITemplate($url, $key, $template) {
        
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


    // function getAllProviders(){
    //     global $conn;
    //     $sql_total = "SELECT * FROM providers WHERE api_key IS NOT NULL AND is_hold = 0";
    //     $result_total = $conn->query($sql_total);

    //     $temp = [];

    //     $total_count = 0;
    //     while($row = $result_total->fetch_assoc()){

    //         if(!in_array($row['domain'], $temp)){
    //             $api_key = decrypt_key($row['api_key']);
    //             echo $row['domain'] . ";" . $row['endpoint'] . ";" . $api_key . "<br>";
    //             array_push($temp, $row['domain']);
    //             $total_count++;
    //         }
            
    //     }

    // }

?>
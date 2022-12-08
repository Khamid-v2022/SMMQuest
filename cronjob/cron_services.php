<?php 
    require_once "include/db_configure.php";
    // require_once "include/variable_config.php";
    require_once "include/functions.php";

    // providers
    require_once "providers/PerfectPanel.php";
    require_once "providers/SmmPanel.php";

    loadServices();
    $conn->close();

    function loadServices() {
        echo "FUNCTION STARTED: " . date("Y-m-d H:i:s") . PHP_EOL . "<br/>";
        // $time_start = microtime(true);
        
        global $conn;
        $inserted_count = 0;

        $sql_total = "SELECT * FROM providers WHERE api_key IS NOT NULL AND is_activated = 1 AND is_valid_key = 1 AND is_hold = 0";
        
        $result_total = $conn->query($sql_total);

        if($result_total->num_rows == 0){
            echo "No providers" . PHP_EOL . "<br/>";
            echo "FUNCTION ENDED: " . date("Y-m-d H:i:s");
            return;
        }

        // get last check provider id
        $sql_cron = "SELECT * FROM cron_check";
        $result_cron = $conn->query($sql_cron);
        if($result_cron->num_rows == 0){
            echo "cron_check table emplty" . PHP_EOL;
            return;
        }

        $cron_config = $result_cron->fetch_assoc();


        $sql = "SELECT * FROM providers WHERE api_key IS NOT NULL AND is_activated = 1 AND is_valid_key = 1 AND is_hold = 0 AND id > " . $cron_config['last_service_check_provider_id'];
        
        $result = $conn->query($sql);
        if($result->num_rows == 0){
            // update cron config table
            $cron_update_sql = "UPDATE cron_check SET last_service_check_provider_id = 0 WHERE id = " . $cron_config['id'];
            $conn->query($cron_update_sql);

            $sql = "SELECT * FROM providers WHERE api_key IS NOT NULL AND is_activated = 1 AND is_valid_key = 1 AND is_hold = 0";
            $result = $conn->query($sql);
        }

        while($row = $result->fetch_assoc()){
            $api_key = decrypt_key($row['api_key']);

            $sql = "UPDATE services SET status = 0, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE provider_id = " . $row['id'];
            $conn->query($sql);

            if($row['real_url']){
                $pro = null;
                switch($row['api_template']){
                    case 'PerfectPanel':
                        $pro = new PerfectPanel($row['real_url'] . $row['endpoint'], $api_key);
                        break;
                    case 'SmmPanel':
                        $pro = new SmmPanel($row['real_url'] . $row['endpoint'], $api_key);
                        break;
                }

                if($pro){
                    $response = $pro->services();  
                    $services = json_decode( json_encode($response), true );

                    // check if API is working correctly
                    if(is_array($services) && count($services) > 0 && isset($services[0]['name'])){
                        foreach ($services as $item) {
                            
                            
                           
                            $sql_service = "SELECT * FROM services WHERE provider_id = " . $row['id'] . " AND service = '" . $item['service'] . "'";
                            
                            $result_service = $conn->query($sql_service);
                                                     
                            $rate = isset($item['rate']) ? ((float) $item['rate']) : 'NULL';
                            $category = isset($item['category']) ? str_replace("\\", "\\\\", str_replace("'", "''", $item['category'])) : 'NULL';
                            
                            // have a over flow bug
                            if($rate > 99999999999)
                                $rate = 99999999999;
                            if($result_service->num_rows == 0){

                                $sql_insert = "INSERT INTO services(provider_id, service, name, type, rate, min, max, dripfeed, refill, cancel, category, status, created_at) VALUES (" 
                                    . $row['id'] . ", '" 
                                    . $item['service'] . "', '" 
                                    . str_replace("\\", "\\\\", str_replace("'", "''", $item['name'])) . "', '" 
                                    . (isset($item['type']) ? $item['type'] : "NULL") . "', " 
                                    . $rate . ", " 
                                    . (isset($item['min']) ? ((int)$item['min']) : "NULL") . ", " 
                                    . (isset($item['max']) ? ((int)$item['max']) : "NULL")  . ", " 
                                    . (isset($item['dripfeed']) ? $item['dripfeed'] ? 1 : 0 : 0) . ", " 
                                    . (isset($item['refill']) ? $item['refill'] ? 1 : 0 : 0) . ", " 
                                    . (isset($item['cancel']) ? $item['cancel'] ? 1 : 0 : 0) . ", '" 
                                    . $category . "', 1, '" 
                                    . date("Y-m-d H:i:s") . "')";

                                $conn->query($sql_insert);
                                $inserted_count++;
                            } else {
                                $sql_update = "UPDATE services SET name = '" . str_replace("\\", "\\\\", str_replace("'", "''", $item['name'])) . "', type = '" . (isset($item['type']) ? $item['type'] : "NULL") . "', rate = '" . $rate . "', min = " . (isset($item['min']) ? ((int)$item['min']) : "NULL") . ", max = " . (isset($item['max']) ? ((int)$item['max']) : "NULL") . ", dripfeed = " . (isset($item['dripfeed']) ? $item['dripfeed'] ? 1 : 0 : 0) . ", refill = " . (isset($item['refill']) ? $item['refill'] ? 1 : 0 : 0) . ", cancel = " . (isset($item['cancel']) ? $item['cancel'] ? 1 : 0 : 0) . ", category = '" . $category . "', status = 1, updated_at = '" . date("Y-m-d H:i:s") . "' WHERE provider_id = " . $row['id'] . " AND service = '" . $item['service'] . "'";
                              
                                $conn->query($sql_update);
                            }
                        }
                    }
                }
            }

            // update cron config table
            $cron_update_sql = "UPDATE cron_check SET last_service_check_provider_id = " . $row['id'] . " WHERE id = " . $cron_config['id'];
            $conn->query($cron_update_sql);

            // $time_end = microtime(true);

            // // set running time as 1 hour
            // if(($time_end - $time_start)/60 > 60){
            //     break;
            // }
        }

        echo "INSERTED: " . $inserted_count . "ROWS" . PHP_EOL . "<br>";
        echo "FUNCTION ENDED: " . date("Y-m-d H:i:s");
    }

?>
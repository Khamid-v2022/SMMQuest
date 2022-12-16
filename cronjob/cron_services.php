<?php 
    require_once "include/db_configure.php";
    require_once "include/functions.php";

    loadServices();
    $conn->close();

    function loadServices() {
        echo "Service STARTED: " . date("Y-m-d H:i:s") . PHP_EOL;
        // $time_start = microtime(true);
        
        global $conn;
        $inserted_count = 0;

        $sql_total = "SELECT * FROM providers WHERE api_key IS NOT NULL AND is_activated = 1 AND is_valid_key = 1 AND is_hold = 0";
        
        $result_total = $conn->query($sql_total);

        if($result_total->num_rows == 0){
            echo "No providers" . PHP_EOL;
            echo "FUNCTION ENDED: " . date("Y-m-d H:i:s") . PHP_EOL;
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

        // get currency table
        $sql_currency = "SELECT * FROM base_currency WHERE id = 1";
        $result_currency = $conn->query($sql_currency);
        $currency_conversion_rate = $result_currency->fetch_assoc();

        while($row = $result->fetch_assoc()){
            $api_key = decrypt_key($row['api_key']);

            $sql = "UPDATE services SET status = 0, updated_at = '" . date('Y-m-d H:i:s') . "' WHERE provider_id = " . $row['id'];
            $conn->query($sql);

            if($row['real_url']){
                $services = getServicesFromPanel($row['real_url'] . $row['endpoint'], $api_key, $row['api_template']);
               
                // check if API is working correctly
                if(is_array($services) && count($services) > 0 && isset($services[0]['name'])){

                    // update service count of provider table 
                    $update_sql = "UPDATE providers SET service_count = " . count($services) . " WHERE id = " . $row['id'];
                    $conn->query($update_sql);

                    foreach ($services as $item) {
                        
                        $sql_service = "SELECT * FROM services WHERE provider_id = " . $row['id'] . " AND service = '" . $item['service'] . "'";
                        
                        $result_service = $conn->query($sql_service);
                                                 
                        $rate = isset($item['rate']) ? ((float) $item['rate']) : 'NULL';
                        $category = isset($item['category']) ? str_replace("\\", "\\\\", str_replace("'", "''", $item['category'])) : 'NULL';
                        
                        // have a over flow bug
                        if($rate != 'NULL' && $rate > 99999999999){
                            $rate = 99999999999;
                            $rate_usd = $rate;
                        } else {
                            $rate_usd = $rate;
                            // covert rate(price) to usd
                            if($rate != 'NULL' && $row['currency'] && $row['currency'] != '1' && strtoupper($row['currency']) != 'USD' && $currency_conversion_rate[strtoupper($row['currency'])] != 0){
                                $rate_usd = $rate / $currency_conversion_rate[strtoupper($row['currency'])];
                            }
                        }

                        if($result_service->num_rows == 0){

                            $sql_insert = "INSERT INTO services(provider_id, service, name, type, rate, rate_usd, min, max, dripfeed, refill, cancel, category, status, created_at) VALUES (" 
                                . $row['id'] . ", '" 
                                . $item['service'] . "', '" 
                                . str_replace("\\", "\\\\", str_replace("'", "''", $item['name'])) . "', '" 
                                . (isset($item['type']) ? $item['type'] : "NULL") . "', " 
                                . $rate . ", " . $rate_usd . ", " 
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
                            $sql_update = "UPDATE services SET name = '" . str_replace("\\", "\\\\", str_replace("'", "''", $item['name'])) . "', type = '" . (isset($item['type']) ? $item['type'] : "NULL") . "', rate = " . $rate . ", rate_usd = " . $rate_usd . ", min = " . (isset($item['min']) ? ((int)$item['min']) : "NULL") . ", max = " . (isset($item['max']) ? ((int)$item['max']) : "NULL") . ", dripfeed = " . (isset($item['dripfeed']) ? $item['dripfeed'] ? 1 : 0 : 0) . ", refill = " . (isset($item['refill']) ? $item['refill'] ? 1 : 0 : 0) . ", cancel = " . (isset($item['cancel']) ? $item['cancel'] ? 1 : 0 : 0) . ", category = '" . $category . "', status = 1, updated_at = '" . date("Y-m-d H:i:s") . "' WHERE provider_id = " . $row['id'] . " AND service = '" . $item['service'] . "'";
                          
                            $conn->query($sql_update);
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

        echo "INSERTED: " . $inserted_count . "ROWS" . PHP_EOL;
        echo "SERVICE ENDED: " . date("Y-m-d H:i:s") . PHP_EOL;
    }

?>
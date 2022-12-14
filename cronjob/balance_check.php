<?php 
    require_once "include/db_configure.php";
    require_once "include/functions.php";

    check_balance();
    $conn->close();

    function check_balance() {
        echo "STARTED: " . date("Y-m-d H:i:s") . PHP_EOL;
        $time_start = microtime(true);
        
        global $conn;
        $inserted_count = 0;

        $sql_total = "SELECT `up`.`id`, `p`.`domain`, `p`.`real_url`, `p`.`endpoint`, `p`.`api_template`,  `up`.`api_key` 
                        FROM( 
                            SELECT `id`, `provider_id`, `api_key` 
                            FROM `user_provider` WHERE `is_enabled` = 1 AND `is_valid_key` = 1 AND `api_key` IS NOT NULL AND `api_key` NOT LIKE ''
                        ) `up` 
                        LEFT JOIN `providers` `p` ON `up`.`provider_id` = `p`.`id` AND `is_hold` = 0 AND `is_activated` = 1 AND `api_template` IS NOT NULL AND `endpoint` IS NOT NULL";
        
        $result_total = $conn->query($sql_total);

        if($result_total->num_rows == 0){
            echo "No providers to check balance " . PHP_EOL;
            echo "ENDED: " . date("Y-m-d H:i:s") . PHP_EOL;
            return;
        }

        // get last check id
        $sql_cron = "SELECT * FROM cron_check";
        $result_cron = $conn->query($sql_cron);
        if($result_cron->num_rows == 0){
            echo "cron_check table emplty" . PHP_EOL;
            return;
        }

        $cron_config = $result_cron->fetch_assoc();


        $sql = "SELECT `up`.`id`, `p`.`domain`, `p`.`real_url`, `p`.`endpoint`, `p`.`api_template`,  `up`.`api_key` 
                        FROM( 
                            SELECT `id`, `provider_id`, `api_key` 
                            FROM `user_provider` WHERE `is_enabled` = 1 AND `is_valid_key` = 1 AND `api_key` IS NOT NULL AND `api_key` NOT LIKE ''
                        ) `up` 
                        LEFT JOIN `providers` `p` ON `up`.`provider_id` = `p`.`id` AND `is_hold` = 0 AND `is_activated` = 1 AND `api_template` IS NOT NULL AND `endpoint` IS NOT NULL WHERE `up`.`id` > " . $cron_config['last_balance_check_user_provider_id'];
        
        $result = $conn->query($sql);
        if($result->num_rows == 0){
            // update cron config table
            $cron_update_sql = "UPDATE cron_check SET last_balance_check_user_provider_id = 0 WHERE id = " . $cron_config['id'];
            $conn->query($cron_update_sql);

            $result = $conn->query($sql_total);
        }

        while($row = $result->fetch_assoc()){
            $api_key = decrypt_key($row['api_key']);
            if($row['real_url']){
                $api_check = checkAPIKeyWithTemplate($row['real_url'] . $row['endpoint'], $api_key, $row['api_template'] );
                
                if($api_check['status']) {
                    $update_sql = "UPDATE user_provider SET user_balance = " . $api_check['balance'] . ", balance_currency = '" . ($api_check['currency'] ? $api_check['currency'] : "") . "' WHERE id = " . $row['id'];
                    $conn->query($update_sql);
                }
            }

            // update cron config table
            $cron_update_sql = "UPDATE cron_check SET last_balance_check_user_provider_id = " . $row['id'] . " WHERE id = " . $cron_config['id'];
            $conn->query($cron_update_sql);

            $time_end = microtime(true);

            // set running time as 1 hour
            if(($time_end - $time_start)/60 > 60){
                break;
            }
        }

        echo "BALACE ENDED: " . date("Y-m-d H:i:s") . PHP_EOL;
    }

?>
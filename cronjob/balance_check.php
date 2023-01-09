<?php 
    require_once "include/db_configure.php";
    require_once "include/functions.php";

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    require_once "../vendor/autoload.php";

    check_balance();
    sendEmail();

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


    function sendEmail() {
        global $conn;
        echo "EMAIL STARTED: " . date("Y-m-d H:i:s") . PHP_EOL . "<br/>";
        // get users
        $sql_users = "SELECT id, first_name, last_name, email FROM users WHERE verified = 1 AND is_delete = 0";
        $result_users = $conn->query($sql_users);

        if($result_users->num_rows == 0){
            echo "No users to send email" . PHP_EOL . "<br/>";
            echo "EMAIL ENDED: " . date("Y-m-d H:i:s") . PHP_EOL;
            return;
        }

        while($user = $result_users->fetch_assoc()){
            // get alert providers
            $sql = "SELECT p.domain, up.* FROM (
                    SELECT provider_id, user_balance, balance_currency, balance_alert_limit 
                    FROM user_provider 
                    WHERE balance_alert_limit IS NOT NULL AND balance_alert_limit > 0 
                        AND (user_balance IS NULL OR user_balance < balance_alert_limit) 
                        AND user_id = " . $user['id'] . ") up 
                    LEFT JOIN providers p ON up.provider_id = p.id";
            $result = $conn->query($sql);
            
            if($result->num_rows > 0){
                // email content
                $mail_content = "<p>Add funds to your providers websites, so orders are not being Failed on your panel.</p><ul>";
                while($provider = $result->fetch_assoc()){
                    $mail_content .= "<li><a href='https://" . $provider['domain'] . "'>" . $provider['domain'] . "</a> (" . $provider['balance_alert_limit'] . $provider['balance_currency'] . ") - Current Balance: " . $provider['user_balance'] . $provider['balance_currency'] . "</li>";
                }
                $mail_content .= "</ul>";
                $mail_content .= '<p>You are receiving this email because you have configured an "Email Balance Alert" at SMMQuest.com.</p>';

                // send email
                send_email($user['email'], $mail_content);
            }
        }


        echo "EMAIL ENDED: " . date("Y-m-d H:i:s") . PHP_EOL;
    }

    function send_email($email, $mail_content){
        $mail = new PHPMailer(true); 

        try {
            $mail->SMTPDebug = 4;                                       
            $mail->isSMTP();                                            
            $mail->Host       = $_ENV['MAIL_HOST'];                    
            $mail->SMTPAuth   = true;                             
            $mail->Username   = $_ENV['MAIL_USERNAME'];                 
            $mail->Password   = $_ENV['MAIL_PASSWORD'];                        
            $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];                              
            $mail->Port       = $_ENV['MAIL_PORT'];  
        
            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], "Balance Alert");           
            $mail->addAddress($email);
            
            $mail->isHTML(true);                                  
            $mail->Subject = 'SMMQuest.com - Balance Alert';
            $mail->Body = $mail_content;

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

?>
<?php 
    require_once "include/db_configure.php";
    require_once "include/functions.php";
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    require_once "../vendor/autoload.php";

    // sendEmail();
    $conn->close();

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
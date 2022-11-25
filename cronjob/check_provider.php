<?php 
    require_once "include/db_configure.php";
    require_once "include/functions.php";
    
    // providers
    require_once "providers/PerfectPanel.php";
    require_once "providers/SmmPanel.php";

    checkProvider();
    $conn->close();

    function checkProvider() {
        echo "CHECK PROVIDER FUNCTION STARTED: " . date("Y-m-d H:i:s") . PHP_EOL . "<br/>";

        global $conn;

        $sql = "SELECT * FROM providers WHERE api_key IS NOT NULL AND is_hold = 0";
        $result = $conn->query($sql);
        if($result->num_rows == 0){
            echo "No providers" . PHP_EOL . "<br/>";
            echo "FUNCTION ENDED: " . date("Y-m-d H:i:s");
            return;
        }

        $enabled = 0;
        $disabled = 0;
        while($row = $result->fetch_assoc()){
            $url = rtrim(check_protocol($row['domain']), '/');
            $response = urlExists($url);
            
            if($response){
                // website is working
                // check API key working or not
                $api_key = decrypt_key($row['api_key']);
                $api_check = checkAPITemplate($url . $row['endpoint'], $api_key, $row['api_template']);
                if($api_check) {
                    $sql = "UPDATE providers SET is_activated = 1, is_valid_key = 1 WHERE id = " . $row['id'];
                    if($row['is_activated'] == 0){
                        $enabled++;
                    }
                } else {
                    $sql = "UPDATE providers SET is_activated = 1, is_valid_key = 0 WHERE id = " . $row['id'];
                }
                $conn->query($sql);
            } else if (!$response && $row['is_activated'] == 1){
                // website is not working
                $sql = "UPDATE providers SET is_activated = 0 WHERE id = " . $row['id'];
                $conn->query($sql);
                $disabled++;
            }
        }
        echo "ENABLED: " . $enabled . PHP_EOL . "<br/>";
        echo "DISABLED: " . $disabled . PHP_EOL . "<br/>";
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

?>
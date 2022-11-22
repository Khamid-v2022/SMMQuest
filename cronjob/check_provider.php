<?php 
    require_once "include/db_configure.php";
    checkProvider();
    $conn->close();

    function checkProvider() {
        echo "CHECK PROVIDER FUNCTION STARTED: " . date("Y-m-d H:i:s") . PHP_EOL . "<br/>";

        global $conn;

        $sql = "SELECT * FROM providers WHERE api_key IS NOT NULL";
        $result = $conn->query($sql);
        if($result->num_rows == 0){
            echo "No providers" . PHP_EOL . "<br/>";
            echo "FUNCTION ENDED: " . date("Y-m-d H:i:s");
            return;
        }

        $enabled = 0;
        $disabled = 0;
        while($row = $result->fetch_assoc()){
            $response = urlExists($row['domain']);
            
            if($response){
            // website is working
                // check API key working or not
                $api_check = checkAPI($row['domain'], $row['endpoint'], $row['api_key']);
                if($api_check > 0) {
                    $sql = "";
                    if($api_check == 1 && $row['is_valid_key'] == 0)
                        $sql = "UPDATE providers SET is_activated = 1, is_valid_key = 1 WHERE id = " . $row['id'];
                    else if($api_check == 2 && $row['is_valid_key'] == 1)
                        $sql = "UPDATE providers SET is_activated = 1, is_valid_key = 0 WHERE id = " . $row['id'];
                    else if($row['is_activated'] == 0) 
                        $sql = "UPDATE providers SET is_activated = 1 WHERE id = " . $row['id'];
                    if($sql){
                        $conn->query($sql);
                        $enabled++;
                    }
                } else if ($api_check == 0 && $row['is_activated'] == 1){
                    // api no response
                    $sql = "UPDATE providers SET is_activated = 0 WHERE id = " . $row['id'];
                    $conn->query($sql);
                    $disabled++;
                }
            } else if (!$response && $row['is_activated'] == 1){
                // website is not working
                $sql = "UPDATE providers SET is_activated = 0 WHERE id = " . $row['id'];
                $conn->query($sql);
                $disabled++;
            }
        }
        echo "UPDATED: " . $enabled . PHP_EOL . "<br/>";
        echo "DISABLED: " . $disabled . PHP_EOL . "<br/>";
        echo "FUNCTION ENDED: " . date("Y-m-d H:i:s");
    }

    function urlExists($url) {
        $url = preg_replace( "#^[^:/.]*[:/]+#i", "", $url);
        $url = "http://" . $url;
        $headers = @get_headers($url);
        if(!$headers || strpos($headers[0], '404')) {
            $exists = false;
        }
        else {
            $exists = true;
        }
        return $exists;
    }

    function checkAPI($url, $end_point, $key){
        $url = preg_replace( "#^[^:/.]*[:/]+#i", "", $url);
        $url = "http://" . $url;

        $url = $url . $end_point . '?action=services&key=' . $key;
        $json = file_get_contents($url);
        
        $response = json_decode($json, true);
  
        if(is_array($response) && count($response) > 0){
            if(isset($response[0]['name']))
                return 1;       // api, key working
            else 
                return 2;       // api_key is wrong
        }
        // endpoint wrong or api don't work
        return 0;
    }

?>
<?php 
    require_once "include/db_configure.php";
    // require_once "include/variable_config.php";
    require_once "include/functions.php";

    loadServices();
    $conn->close();

    function loadServices() {
        echo "FUNCTION STARTED: " . date("Y-m-d H:i:s") . PHP_EOL . "<br/>";

        global $conn;
        $inserted_count = 0;
        $updated_count = 0;

        $sql = "SELECT * FROM providers WHERE api_key IS NOT NULL AND is_activated = 1 AND is_valid_key = 1";
        $result = $conn->query($sql);
        if($result->num_rows == 0){
            echo "No providers" . PHP_EOL . "<br/>";
            echo "FUNCTION ENDED: " . date("Y-m-d H:i:s");
            return;
        }

        $providers = [];
        while($row = $result->fetch_assoc()){
            $api_key = decrypt_key($row['api_key']);
            array_push($providers, array('id' => $row['id'], 'endpoint' => "https://" . $row['domain'] . $row['endpoint'], 'key' => $api_key));
        }
        
        // set all services status as disabled
        $sql = "UPDATE services SET STATUS = 0";
        $conn->query($sql);

        foreach($providers as $provider){
            $url = $provider['endpoint'] . '?action=services&key=' . $provider['key'];
            $json = file_get_contents($url);
            
            $response = json_decode($json, true);

            foreach ($response as $item) {
                $sql = "SELECT * FROM services WHERE provider_id = " . $provider['id'] . " AND service = " . $item['service'];
                $result = $conn->query($sql);
                
                if($result->num_rows == 0){
                    $sql = "INSERT INTO services(provider_id, service, name, type, rate, min, max, dripfeed, refill, cancel, category, status, created_at) VALUES (" . $provider['id'] . ", " . $item['service'] . ", '" . $item['name'] . "', '" . $item['type'] . "', " . $item['rate'] . ", " . $item['min'] . ", " . $item['max'] . ", " . ($item['dripfeed'] ? 1 : 0) . ", " . ($item['refill'] ? 1 : 0) . ", " . ($item['cancel'] ? 1 : 0) . ", '" . $item['category'] . "', 1, '" . date("Y-m-d H:i:s") . "')";
                    $conn->query($sql);
                    $inserted_count++;
                } else {
                    $row = $result->fetch_assoc();
                    // if(
                    //     $row['name'] != $item['name'] || 
                    //     $row['type'] != $item['type'] || 
                    //     $row['rate'] != $item['rate'] || 
                    //     $row['min'] != $item['min'] || 
                    //     $row['max'] != $item['max'] || 
                    //     $row['dripfeed'] != $item['dripfeed'] || 
                    //     $row['refill'] != $item['refill'] || 
                    //     $row['cancel'] != $item['cancel'] ||
                    //     $row['category'] != $item['category'] ){

                        $sql = "UPDATE services SET name = '" . $item['name'] . "', type = '" . $item['type'] . "', rate = " . $item['rate'] . ", min = " . $item['min'] . ", max = " . $item['max'] . ", dripfeed = " . ($item['dripfeed'] ? 1 : 0) . ", refill = " . ($item['refill'] ? 1 : 0) . ", cancel = " . ($item['cancel'] ? 1 : 0) . ", category = '" . $item['category'] . "', status = 1, updated_at = '" . date("Y-m-d H:i:s") . "' WHERE provider_id = " . $provider['id'] . " AND service = " . $item['service'];
                        $conn->query($sql);
                        $updated_count++;
                    // }
                    
                }
            }
        }
        echo "INSERTED: " . $inserted_count . "ROWS" . PHP_EOL . "<br>";
        echo "UPDATED: " . $updated_count . "ROWS" . PHP_EOL . "<br>";
        echo "FUNCTION ENDED: " . date("Y-m-d H:i:s");
    }

?>
<?php 
    require_once "db_configure.php";
    require_once "variable_config.php";

    loadServicesFromFollowiz();
    $conn->close();
    
    function loadServicesFromFollowiz() {
        echo "FUNCTION STARTED: " . date("Y-m-d H:i:s") . PHP_EOL . "<br/>";

        global $services_apis, $conn;
        $inserted_count = 0;
        $updated_count = 0;

        foreach($services_apis as $api){
            $url = $api['endpoint'] . '?action=services&key=' . $api['key'];
            $json = file_get_contents("$url");
            
            $source_url = substr($api['endpoint'], 0, -7);
            $response = json_decode($json, true);

            foreach ($response as $item) {
                $sql = "SELECT * FROM services WHERE service_id = " . $api['service_id'] . " AND service = " . $item['service'];
                $result = $conn->query($sql);
                
                if($result->num_rows == 0){
                    $sql = "INSERT INTO services(service_id, source_url, service, name, type, rate, min, max, dripfeed, refill, cancel, category) VALUES (" . $api['service_id'] . ", '" . $source_url . "', " . $item['service'] . ", '" . $item['name'] . "', '" . $item['type'] . "', " . $item['rate'] . ", " . $item['min'] . ", " . $item['max'] . ", " . ($item['dripfeed']?1:0) . ", " . ($item['refill']?1:0) . ", " . ($item['cancel']?1:0) . ", '" . $item['category'] . "')";
                    $conn->query($sql);
                    $inserted_count++;
                } else {
                    $row = $result->fetch_assoc();
                    if(
                        $row['name'] != $item['name'] || 
                        $row['type'] != $item['type'] || 
                        $row['rate'] != $item['rate'] || 
                        $row['min'] != $item['min'] || 
                        $row['max'] != $item['max'] || 
                        $row['dripfeed'] != $item['dripfeed'] || 
                        $row['refill'] != $item['refill'] || 
                        $row['cancel'] != $item['cancel'] ||
                        $row['category'] != $item['category'] ){

                        $sql = "UPDATE services SET name = '" . $item['name'] . "', type = '" . $item['type'] . "', rate = " . $item['rate'] . ", min = " . $item['min'] . ", max = " . $item['max'] . ", dripfeed = " . ($item['dripfeed'] ? 1 : 0) . ", refill = " . ($item['refill'] ? 1 : 0) . ", cancel = " . ($item['cancel']?1:0) . ", category = '" . $item['category'] . "' WHERE service_id = " . $api['service_id'] . " AND service = " . $item['service'];
                        $conn->query($sql);
                        echo $sql;
                        $updated_count++;
                    }
                    
                }
            }
        }
        echo "INSERTED: " . $inserted_count . "ROWS" . "<br>";
        echo "UPDATED: " . $updated_count . "ROWS" . "<br>";
        echo "FUNCTION ENDED: " . date("Y-m-d H:i:s");
    }

    

?>
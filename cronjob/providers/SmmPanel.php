<?php 
class SmmPanel 
{
    private $api_url = 'https://domain/api/v2';     // API URL
    private $api_key = '';                         // Your API key

    public function __construct($url, $key)
    {
        $this->api_url = $url;
        $this->api_key = $key;
    }

    // add order
    public function order($data) 
    { 
        $post = array_merge(array('key' => $this->api_key, 'action' => 'add'), $data);
        return json_decode($this->connect($post));
    }

    // get order status
    public function status($order_id) 
    { 
        return json_decode($this->connect(array(
          'key' => $this->api_key,
          'action' => 'status',
          'id' => $order_id
        )));
    }

    // get services
    public function services() 
    { 
        return json_decode(
            $this->connect(array(
                'key' => $this->api_key,
                'action' => 'services'
            )));
    }


    private function connect($post) {
        $_post = [];
        if (is_array($post)) {
            foreach ($post as $name => $value) {
                $_post[] = $name.'='.urlencode($value);
            }
        }
        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if (is_array($post)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $_post));
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        $result = curl_exec($ch);
        if (curl_errno($ch) != 0 && empty($result)) {
            $result = false;
        }
        curl_close($ch);
        return $result;
    }
}

?>
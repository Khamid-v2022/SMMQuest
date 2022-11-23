<?php 
    require_once "../../vendor/autoload.php";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();

    function decrypt_key($message)
    {
        list($crypted_token, $enc_iv) = explode("::", $message);
        $cipher_method = 'aes-128-ctr';
        $token = openssl_decrypt($crypted_token, $cipher_method, $_ENV['ENCRYPT_KEY'], 0, hex2bin($enc_iv));
        return $token;
    }
?>
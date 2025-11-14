<?php
class Security {
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    public static function validateTelegramData($init_data) {
        $secret_key = hash_hmac('sha256', BOT_TOKEN, "WebAppData", true);
        $check_hash = $init_data['hash'] ?? '';
        unset($init_data['hash']);
        
        ksort($init_data);
        $data_check_string = implode("\n", array_map(
            function ($k, $v) { return "$k=$v"; },
            array_keys($init_data),
            $init_data
        ));
        
        $hash = bin2hex(hash_hmac('sha256', $data_check_string, $secret_key, true));
        return $hash === $check_hash;
    }
    
    public static function encrypt($data) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
    
    public static function decrypt($data) {
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
    }
}
?>
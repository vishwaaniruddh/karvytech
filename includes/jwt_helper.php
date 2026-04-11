<?php
class JWTHelper {
    private static $secret_key = 'your-secret-key-change-this-in-production';
    private static $algorithm = 'HS256';
    
    public static function encode($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$algorithm]);
        $payload = json_encode($payload);
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, self::$secret_key, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    public static function decode($jwt) {
        $tokenParts = explode('.', $jwt);
        
        if (count($tokenParts) !== 3) {
            return false;
        }
        
        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
        $signatureProvided = $tokenParts[2];
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, self::$secret_key, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        if ($base64Signature === $signatureProvided) {
            return json_decode($payload, true);
        }
        
        return false;
    }
    
    public static function generateToken($userId, $username, $role, $vendorId = null) {
        $payload = [
            'user_id' => $userId,
            'username' => $username,
            'role' => $role,
            'vendor_id' => $vendorId,
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ];
        
        return self::encode($payload);
    }
    
    public static function validateToken($token) {
        $decoded = self::decode($token);
        
        if (!$decoded) {
            return false;
        }
        
        // Check if token is expired
        if (isset($decoded['exp']) && $decoded['exp'] < time()) {
            return false;
        }
        
        return $decoded;
    }
}
?>
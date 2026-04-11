<?php
// Application logging utility

class Logger {
    const LEVEL_DEBUG = 'DEBUG';
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_CRITICAL = 'CRITICAL';
    
    private static $logFile = 'logs/application.log';
    private static $minLevel = self::LEVEL_INFO;
    
    public static function setLogFile($file) {
        self::$logFile = $file;
    }
    
    public static function setMinLevel($level) {
        self::$minLevel = $level;
    }
    
    public static function debug($message, $context = []) {
        self::log(self::LEVEL_DEBUG, $message, $context);
    }
    
    public static function info($message, $context = []) {
        self::log(self::LEVEL_INFO, $message, $context);
    }
    
    public static function warning($message, $context = []) {
        self::log(self::LEVEL_WARNING, $message, $context);
    }
    
    public static function error($message, $context = []) {
        self::log(self::LEVEL_ERROR, $message, $context);
    }
    
    public static function critical($message, $context = []) {
        self::log(self::LEVEL_CRITICAL, $message, $context);
    }
    
    private static function log($level, $message, $context = []) {
        if (!self::shouldLog($level)) {
            return;
        }
        
        // Ensure log directory exists
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $userId = $_SESSION['user_id'] ?? 'guest';
        $ipAddress = self::getClientIP();
        
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message,
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'context' => $context
        ];
        
        $logLine = json_encode($logEntry) . PHP_EOL;
        
        // Write to log file
        file_put_contents(self::$logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    private static function shouldLog($level) {
        $levels = [
            self::LEVEL_DEBUG => 0,
            self::LEVEL_INFO => 1,
            self::LEVEL_WARNING => 2,
            self::LEVEL_ERROR => 3,
            self::LEVEL_CRITICAL => 4
        ];
        
        return $levels[$level] >= $levels[self::$minLevel];
    }
    
    private static function getClientIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
    
    public static function logUserLogin($username, $success = true) {
        $message = $success ? "User login successful" : "User login failed";
        $context = ['username' => $username, 'success' => $success];
        
        if ($success) {
            self::info($message, $context);
        } else {
            self::warning($message, $context);
        }
    }
    
    public static function logUserLogout($username) {
        self::info("User logout", ['username' => $username]);
    }
    
    public static function logDatabaseQuery($query, $params = [], $executionTime = null) {
        $context = [
            'query' => $query,
            'params' => $params
        ];
        
        if ($executionTime !== null) {
            $context['execution_time'] = $executionTime . 'ms';
        }
        
        self::debug("Database query executed", $context);
    }
    
    public static function logFileUpload($filename, $size, $success = true) {
        $message = $success ? "File upload successful" : "File upload failed";
        $context = [
            'filename' => $filename,
            'size' => $size,
            'success' => $success
        ];
        
        if ($success) {
            self::info($message, $context);
        } else {
            self::error($message, $context);
        }
    }
    
    public static function logSecurityEvent($event, $details = []) {
        self::warning("Security event: $event", $details);
    }
}
?>
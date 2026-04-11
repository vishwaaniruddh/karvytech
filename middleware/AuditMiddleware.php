<?php
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/../config/auth.php';

class AuditMiddleware {
    private static $startTime;
    private static $auditLog;
    
    /**
     * Initialize audit tracking
     */
    public static function start() {
        self::$startTime = microtime(true);
        self::$auditLog = new AuditLog();
        
        // Update session activity if user is logged in
        if (Auth::isLoggedIn()) {
            self::$auditLog->updateSessionActivity();
        }
    }
    
    /**
     * Log page access
     */
    public static function logPageAccess() {
        if (!self::$auditLog) {
            self::start();
        }
        
        $user = Auth::getCurrentUser();
        $endpoint = $_SERVER['REQUEST_URI'] ?? 'unknown';
        
        // Don't log certain endpoints to reduce noise
        $excludePatterns = [
            '/assets/',
            '/uploads/',
            '.css',
            '.js',
            '.jpg',
            '.png',
            '.gif',
            '.ico'
        ];
        
        foreach ($excludePatterns as $pattern) {
            if (strpos($endpoint, $pattern) !== false) {
                return;
            }
        }
        
        self::$auditLog->log([
            'user_id' => $user['id'] ?? null,
            'username' => $user['username'] ?? 'guest',
            'user_role' => $user['role'] ?? 'guest',
            'action_type' => 'page_access',
            'endpoint' => $endpoint,
            'http_method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'status_code' => http_response_code() ?: 200,
            'execution_time' => self::getExecutionTime()
        ]);
    }
    
    /**
     * Log API call
     */
    public static function logAPICall($endpoint, $statusCode = 200, $requestData = null, $responseData = null, $errorMessage = null) {
        if (!self::$auditLog) {
            self::start();
        }
        
        $user = Auth::getCurrentUser();
        $executionTime = self::getExecutionTime();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Log the API call
        self::$auditLog->log([
            'user_id' => $user['id'] ?? null,
            'username' => $user['username'] ?? 'guest',
            'user_role' => $user['role'] ?? 'guest',
            'action_type' => 'api_call',
            'endpoint' => $endpoint,
            'http_method' => $method,
            'status_code' => $statusCode,
            'request_data' => $requestData ? json_encode($requestData) : null,
            'response_data' => $responseData ? json_encode($responseData) : null,
            'error_message' => $errorMessage,
            'execution_time' => $executionTime
        ]);
        
        // Update API statistics
        self::$auditLog->updateAPIStats($endpoint, $method, $statusCode, $executionTime);
    }
    
    /**
     * Log user action
     */
    public static function logAction($actionType, $endpoint, $statusCode = 200, $additionalData = []) {
        if (!self::$auditLog) {
            self::start();
        }
        
        $user = Auth::getCurrentUser();
        
        $logData = array_merge([
            'user_id' => $user['id'] ?? null,
            'username' => $user['username'] ?? 'guest',
            'user_role' => $user['role'] ?? 'guest',
            'action_type' => $actionType,
            'endpoint' => $endpoint,
            'http_method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'status_code' => $statusCode,
            'execution_time' => self::getExecutionTime()
        ], $additionalData);
        
        self::$auditLog->log($logData);
    }
    
    /**
     * Get execution time
     */
    private static function getExecutionTime() {
        if (!self::$startTime) {
            return null;
        }
        return round(microtime(true) - self::$startTime, 4);
    }
    
    /**
     * Shutdown handler to log page access automatically
     */
    public static function registerShutdownHandler() {
        register_shutdown_function([self::class, 'logPageAccess']);
    }
}

// Auto-start audit tracking
AuditMiddleware::start();
?>

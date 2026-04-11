<?php
// Error handling and logging system

class ErrorHandler {
    private static $logFile = 'logs/error.log';
    private static $auditLogFile = 'logs/audit.log';
    private static $debugMode = true; // Set to false in production
    
    public static function init() {
        // Ensure log directory exists
        if (!is_dir('logs')) {
            mkdir('logs', 0755, true);
        }
        
        // Set custom error handler
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleFatalError']);
        
        // Configure error reporting based on debug mode
        error_reporting(E_ALL);
        if (self::$debugMode) {
            ini_set('display_errors', 1); // Show errors for debugging
            ini_set('display_startup_errors', 1);
        } else {
            ini_set('display_errors', 0); // Hide errors in production
            ini_set('display_startup_errors', 0);
        }
        ini_set('log_errors', 1);
        ini_set('error_log', self::$logFile);
    }
    
    public static function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorTypes = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE ERROR',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE ERROR',
            E_CORE_WARNING => 'CORE WARNING',
            E_COMPILE_ERROR => 'COMPILE ERROR',
            E_COMPILE_WARNING => 'COMPILE WARNING',
            E_USER_ERROR => 'USER ERROR',
            E_USER_WARNING => 'USER WARNING',
            E_USER_NOTICE => 'USER NOTICE',
            E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER DEPRECATED'
        ];
        
        // E_STRICT is deprecated in PHP 8.4+, so we no longer include it
        
        $errorType = $errorTypes[$severity] ?? 'UNKNOWN ERROR';
        
        self::logError($errorType, $message, $file, $line);
        
        // For critical errors, show appropriate error page based on debug mode
        if ($severity === E_ERROR || $severity === E_CORE_ERROR || $severity === E_COMPILE_ERROR) {
            if (self::$debugMode) {
                self::showDetailedErrorPage($errorType, $message, $file, $line);
            } else {
                self::showErrorPage();
            }
        }
        
        return true;
    }
    
    public static function handleException($exception) {
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTraceAsString();
        
        self::logError('EXCEPTION', $message, $file, $line, $trace);
        if (self::$debugMode) {
            self::showDetailedErrorPage('EXCEPTION', $message, $file, $line, $trace);
        } else {
            self::showErrorPage();
        }
    }
    
    public static function handleFatalError() {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            self::logError('FATAL ERROR', $error['message'], $error['file'], $error['line']);
            if (self::$debugMode) {
                self::showDetailedErrorPage('FATAL ERROR', $error['message'], $error['file'], $error['line']);
            } else {
                self::showErrorPage();
            }
        }
    }
    
    private static function logError($type, $message, $file, $line, $trace = null) {
        $timestamp = date('Y-m-d H:i:s');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $ipAddress = self::getClientIP();
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'Unknown';
        
        $logEntry = [
            'timestamp' => $timestamp,
            'type' => $type,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'request_uri' => $requestUri
        ];
        
        if ($trace) {
            $logEntry['trace'] = $trace;
        }
        
        $logLine = json_encode($logEntry) . PHP_EOL;
        
        // Write to log file
        file_put_contents(self::$logFile, $logLine, FILE_APPEND | LOCK_EX);
        
        // Also log to database if possible
        try {
            self::logToDatabase($logEntry);
        } catch (Exception $e) {
            // If database logging fails, just continue
        }
    }
    
    private static function logToDatabase($logEntry) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO audit_logs (user_id, action, old_values, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $userId = $_SESSION['user_id'] ?? null;
        $action = "ERROR: {$logEntry['type']}";
        $details = json_encode([
            'message' => $logEntry['message'],
            'file' => $logEntry['file'],
            'line' => $logEntry['line'],
            'request_uri' => $logEntry['request_uri']
        ]);
        
        $stmt->execute([
            $userId,
            $action,
            $details,
            $logEntry['ip_address'],
            $logEntry['user_agent'],
            $logEntry['timestamp']
        ]);
    }
    
    public static function logUserAction($action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $userId = $_SESSION['user_id'] ?? null;
            $ipAddress = self::getClientIP();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            $stmt->execute([
                $userId,
                $action,
                $tableName,
                $recordId,
                $oldValues ? json_encode($oldValues) : null,
                $newValues ? json_encode($newValues) : null,
                $ipAddress,
                $userAgent
            ]);
            
            // Also log to file
            $logEntry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'user_id' => $userId,
                'action' => $action,
                'table_name' => $tableName,
                'record_id' => $recordId,
                'ip_address' => $ipAddress
            ];
            
            $logLine = json_encode($logEntry) . PHP_EOL;
            file_put_contents(self::$auditLogFile, $logLine, FILE_APPEND | LOCK_EX);
            
        } catch (Exception $e) {
            // If audit logging fails, log the error but don't break the application
            error_log("Audit logging failed: " . $e->getMessage());
        }
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
    
    public static function setDebugMode($enabled) {
        self::$debugMode = $enabled;
        
        if ($enabled) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
        } else {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
        }
    }
    
    public static function isDebugMode() {
        return self::$debugMode;
    }
    
    public static function debugLog($message, $context = []) {
        if (self::$debugMode) {
            $timestamp = date('Y-m-d H:i:s');
            $debugEntry = [
                'timestamp' => $timestamp,
                'type' => 'DEBUG',
                'message' => $message,
                'context' => $context,
                'file' => debug_backtrace()[0]['file'] ?? 'Unknown',
                'line' => debug_backtrace()[0]['line'] ?? 'Unknown'
            ];
            
            $logLine = json_encode($debugEntry) . PHP_EOL;
            file_put_contents('logs/debug.log', $logLine, FILE_APPEND | LOCK_EX);
        }
    }
    
    public static function dumpAndDie($variable, $label = 'Debug Dump') {
        if (self::$debugMode) {
            echo '<!DOCTYPE html>
<html>
<head>
    <title>Debug Dump</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">🔍 ' . htmlspecialchars($label) . '</h1>
        <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm overflow-auto">
            <pre>' . htmlspecialchars(print_r($variable, true)) . '</pre>
        </div>
        <div class="mt-4 space-x-2">
            <button onclick="history.back()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Go Back
            </button>
            <button onclick="location.reload()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Reload Page
            </button>
        </div>
    </div>
</body>
</html>';
        }
        exit();
    }
    
    private static function showErrorPage() {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
        }
        
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Error</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white shadow-lg rounded-lg p-6">
            <div class="text-center">
                <div class="text-red-500 text-6xl mb-4">⚠️</div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">System Error</h1>
                <p class="text-gray-600 mb-4">
                    We apologize, but something went wrong. Our team has been notified and is working to fix the issue.
                </p>
                <div class="space-y-2">
                    <a href="/" class="block w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition duration-200">
                        Return to Home
                    </a>
                    <button onclick="history.back()" class="block w-full bg-gray-600 text-white py-2 px-4 rounded hover:bg-gray-700 transition duration-200">
                        Go Back
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
        exit();
    }
    
    private static function showDetailedErrorPage($errorType, $message, $file, $line, $trace = null) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $ipAddress = self::getClientIP();
        
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Error - ' . htmlspecialchars($errorType) . '</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .code-block {
            background-color: #1f2937;
            color: #f9fafb;
            padding: 1rem;
            border-radius: 0.5rem;
            font-family: "Courier New", monospace;
            font-size: 0.875rem;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .error-section {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background-color: #f9fafb;
        }
        .error-title {
            font-weight: 600;
            color: #dc2626;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-100 p-4">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-red-600 text-white p-4">
                <div class="flex items-center">
                    <div class="text-3xl mr-4">🐛</div>
                    <div>
                        <h1 class="text-2xl font-bold">Debug Mode: ' . htmlspecialchars($errorType) . '</h1>
                        <p class="text-red-100">Error occurred at ' . $timestamp . '</p>
                    </div>
                </div>
            </div>
            
            <!-- Error Details -->
            <div class="p-6">
                <!-- Error Message -->
                <div class="error-section">
                    <h2 class="error-title text-lg">Error Message</h2>
                    <div class="code-block bg-red-50 text-red-800 border border-red-200">
                        ' . htmlspecialchars($message) . '
                    </div>
                </div>
                
                <!-- File and Line -->
                <div class="error-section">
                    <h2 class="error-title text-lg">Location</h2>
                    <div class="space-y-2">
                        <p><strong>File:</strong> <code class="bg-gray-200 px-2 py-1 rounded">' . htmlspecialchars($file) . '</code></p>
                        <p><strong>Line:</strong> <code class="bg-gray-200 px-2 py-1 rounded">' . htmlspecialchars($line) . '</code></p>
                    </div>
                </div>';
        
        // Add stack trace if available
        if ($trace) {
            echo '
                <div class="error-section">
                    <h2 class="error-title text-lg">Stack Trace</h2>
                    <div class="code-block">
                        ' . htmlspecialchars($trace) . '
                    </div>
                </div>';
        }
        
        echo '
                <!-- Request Information -->
                <div class="error-section">
                    <h2 class="error-title text-lg">Request Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p><strong>URL:</strong> <code class="bg-gray-200 px-2 py-1 rounded text-sm">' . htmlspecialchars($requestUri) . '</code></p>
                            <p><strong>Method:</strong> <code class="bg-gray-200 px-2 py-1 rounded">' . ($_SERVER['REQUEST_METHOD'] ?? 'Unknown') . '</code></p>
                            <p><strong>IP Address:</strong> <code class="bg-gray-200 px-2 py-1 rounded">' . htmlspecialchars($ipAddress) . '</code></p>
                        </div>
                        <div>
                            <p><strong>User Agent:</strong></p>
                            <code class="bg-gray-200 px-2 py-1 rounded text-xs block mt-1">' . htmlspecialchars($userAgent) . '</code>
                        </div>
                    </div>
                </div>';
        
        // Add POST/GET data if available
        if (!empty($_POST)) {
            echo '
                <div class="error-section">
                    <h2 class="error-title text-lg">POST Data</h2>
                    <div class="code-block">
                        ' . htmlspecialchars(print_r($_POST, true)) . '
                    </div>
                </div>';
        }
        
        if (!empty($_GET)) {
            echo '
                <div class="error-section">
                    <h2 class="error-title text-lg">GET Data</h2>
                    <div class="code-block">
                        ' . htmlspecialchars(print_r($_GET, true)) . '
                    </div>
                </div>';
        }
        
        // Add session data if available
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION)) {
            echo '
                <div class="error-section">
                    <h2 class="error-title text-lg">Session Data</h2>
                    <div class="code-block">
                        ' . htmlspecialchars(print_r($_SESSION, true)) . '
                    </div>
                </div>';
        }
        
        echo '
                <!-- Actions -->
                <div class="flex space-x-4 mt-6">
                    <a href="/" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-200">
                        Return to Home
                    </a>
                    <button onclick="history.back()" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition duration-200">
                        Go Back
                    </button>
                    <button onclick="location.reload()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition duration-200">
                        Retry
                    </button>
                    <button onclick="copyErrorDetails()" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition duration-200">
                        Copy Error Details
                    </button>
                </div>
                
                <!-- Debug Info Toggle -->
                <div class="mt-4">
                    <button onclick="toggleDebugInfo()" class="text-sm text-gray-600 hover:text-gray-800">
                        Toggle Additional Debug Info
                    </button>
                    <div id="debugInfo" class="hidden mt-2">
                        <div class="error-section">
                            <h3 class="error-title">PHP Configuration</h3>
                            <div class="code-block text-xs">
PHP Version: ' . PHP_VERSION . '
Memory Limit: ' . ini_get('memory_limit') . '
Max Execution Time: ' . ini_get('max_execution_time') . '
Error Reporting: ' . error_reporting() . '
Display Errors: ' . (ini_get('display_errors') ? 'On' : 'Off') . '
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleDebugInfo() {
            const debugInfo = document.getElementById("debugInfo");
            debugInfo.classList.toggle("hidden");
        }
        
        function copyErrorDetails() {
            const errorDetails = `
Error Type: ' . $errorType . '
Message: ' . $message . '
File: ' . $file . '
Line: ' . $line . '
URL: ' . $requestUri . '
Time: ' . $timestamp . '
            `;
            
            navigator.clipboard.writeText(errorDetails.trim()).then(() => {
                alert("Error details copied to clipboard!");
            }).catch(() => {
                alert("Failed to copy error details");
            });
        }
    </script>
</body>
</html>';
        exit();
    }
}

// Initialize error handler
ErrorHandler::init();
?>
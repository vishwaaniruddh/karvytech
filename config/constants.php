<?php

// Set default timezone for India
date_default_timezone_set('Asia/Kolkata');

// Environment detection function (defined early)
if (!function_exists('getEnvironment')) {
    function getEnvironment()
    {
        // Always check server first for production detection
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';

        // Production domain detection (highest priority) - be more specific
        if (
            $host === 'karvy.sarsspl.com' || $host === 'www.karvy.sarsspl.com' ||
            strpos($host, 'sarsspl.com') !== false
        ) {
            return 'production';
        }



        // Check environment variables
        if (isset($_ENV['APP_ENV']) && in_array($_ENV['APP_ENV'], ['development', 'testing', 'production'])) {
            return $_ENV['APP_ENV'];
        }

        $envVar = getenv('APP_ENV');
        if ($envVar && in_array($envVar, ['development', 'testing', 'production'])) {
            return $envVar;
        }

        // Final fallback based on domain patterns
        if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
            return 'development';
        }

        if (strpos($host, 'test.') === 0 || strpos($host, 'staging.') === 0) {
            return 'testing';
        }

        if (preg_match('/\.(com|net|org)$/', $host)) {
            return 'production';
        }
        return 'development';
    }
}

// Get current environment
$currentEnv = getEnvironment();

// Environment-based URL configuration
$baseUrls = [
    'development' => 'http://localhost/project',
    'testing' => 'http://localhost/project',
    'production' => 'https://karvy.sarsspl.com'
];

// Set BASE_URL based on environment
$baseUrl = $baseUrls[$currentEnv];
define('BASE_URL', rtrim($baseUrl, '/'));

// Application constants
define('APP_NAME', 'Site Installation Management System');
define('APP_VERSION', '1.0.0');
define('APP_ENV', $currentEnv);

// URL helper functions
if (!function_exists('url')) {
    function url($path = '')
    {
        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        $path = ltrim($path, '/');
        return $path ? $baseUrl . '/' . $path : $baseUrl;
    }
}

if (!function_exists('asset')) {
    function asset($path)
    {
        return url($path);
    }
}

// Environment helper functions
if (!function_exists('isDevelopment')) {
    function isDevelopment()
    {
        return getEnvironment() === 'development';
    }
}

if (!function_exists('isTesting')) {
    function isTesting()
    {
        return getEnvironment() === 'testing';
    }
}

if (!function_exists('isProduction')) {
    function isProduction()
    {
        return getEnvironment() === 'production';
    }
}

// File upload settings
define('UPLOAD_DIR', 'assets/uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', [
    'jpg',
    'jpeg',
    'png',
    'gif',
    'webp', // Images
    'pdf',
    'doc',
    'docx',
    'txt', // Documents
    'xlsx',
    'xls',
    'csv', // Spreadsheets
    'dwg',
    'dxf', // CAD files
    'mp4',
    'avi',
    'mov',
    'wmv' // Videos
]);

// Pagination settings
define('ITEMS_PER_PAGE', 20);

// Security settings
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 3600);
}
if (!defined('BCRYPT_ROUNDS')) {
    define('BCRYPT_ROUNDS', 12);
}

// User roles
if (!defined('ADMIN_ROLE')) {
    define('ADMIN_ROLE', 'admin');
}
if (!defined('VENDOR_ROLE')) {
    define('VENDOR_ROLE', 'contractor'); // Changed from 'vendor' to 'contractor' to match database
}
if (!defined('CONTRACTOR_ROLE')) {
    define('CONTRACTOR_ROLE', 'contractor');
}
if (!defined('MANAGER_ROLE')) {
    define('MANAGER_ROLE', 'manager');
}
if (!defined('SUPERADMIN_ROLE')) {
    define('SUPERADMIN_ROLE', 'superadmin');
}
if (!defined('ACCOUNTMANAGER_ROLE')) {
    define('ACCOUNTMANAGER_ROLE', 'accountmanager');
}

// Site status constants
define('SITE_STATUS_PENDING', 'pending');
define('SITE_STATUS_ASSIGNED', 'assigned');
define('SITE_STATUS_SURVEYED', 'surveyed');
define('SITE_STATUS_IN_PROGRESS', 'in_progress');
define('SITE_STATUS_COMPLETED', 'completed');

// Installation status constants
define('INSTALLATION_STATUS_ASSIGNED', 'assigned');
define('INSTALLATION_STATUS_ACKNOWLEDGED', 'acknowledged');
define('INSTALLATION_STATUS_IN_PROGRESS', 'in_progress');
define('INSTALLATION_STATUS_ON_HOLD', 'on_hold');
define('INSTALLATION_STATUS_COMPLETED', 'completed');
define('INSTALLATION_STATUS_CANCELLED', 'cancelled');

// Material request status constants
define('REQUEST_STATUS_PENDING', 'pending');
define('REQUEST_STATUS_APPROVED', 'approved');
define('REQUEST_STATUS_DISPATCHED', 'dispatched');
define('REQUEST_STATUS_DELIVERED', 'delivered');
define('REQUEST_STATUS_CANCELLED', 'cancelled');

// Dispatch acknowledgment status
define('ACK_STATUS_PENDING', 'pending');
define('ACK_STATUS_RECEIVED', 'received');
define('ACK_STATUS_PARTIAL', 'partial');

// Survey status constants
define('SURVEY_STATUS_PENDING', 'pending');
define('SURVEY_STATUS_SUBMITTED', 'submitted');
define('SURVEY_STATUS_APPROVED', 'approved');
define('SURVEY_STATUS_REJECTED', 'rejected');

// Priority levels
define('PRIORITY_LOW', 'low');
define('PRIORITY_MEDIUM', 'medium');
define('PRIORITY_HIGH', 'high');
define('PRIORITY_URGENT', 'urgent');

// Logging settings
define('LOG_LEVEL', 'info');
define('LOG_PATH', 'logs/app.log');
?>
<?php
/**
 * Compatibility Layer for Different PHP Versions and Environments
 * Provides fallbacks for missing dependencies or features
 */

// Check if we're in a cloud environment with restrictions
define('IS_CLOUD_ENVIRONMENT', !is_dir(__DIR__ . '/../vendor/') || !file_exists(__DIR__ . '/../vendor/autoload.php'));

// Try to load Composer autoloader if available
if (!IS_CLOUD_ENVIRONMENT) {
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        define('COMPOSER_AVAILABLE', true);
    } catch (Exception $e) {
        define('COMPOSER_AVAILABLE', false);
    }
} else {
    define('COMPOSER_AVAILABLE', false);
}

/**
 * Simple CSV Export Function (fallback for PHPSpreadsheet)
 */
function exportArrayToCSV($data, $filename, $headers = null) {
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add headers if provided
    if ($headers && is_array($headers)) {
        fputcsv($output, $headers);
    }
    
    // Add data rows
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

/**
 * Simple Excel-like CSV Reader (fallback for PHPSpreadsheet)
 */
function readCSVFileCompat($filepath, $hasHeaders = true) {
    if (!file_exists($filepath)) {
        throw new Exception("File not found: $filepath");
    }
    
    $data = [];
    $headers = [];
    
    if (($handle = fopen($filepath, "r")) !== FALSE) {
        $rowIndex = 0;
        
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($hasHeaders && $rowIndex === 0) {
                $headers = $row;
            } else {
                if ($hasHeaders && !empty($headers)) {
                    $data[] = array_combine($headers, $row);
                } else {
                    $data[] = $row;
                }
            }
            $rowIndex++;
        }
        fclose($handle);
    }
    
    return $data;
}

/**
 * Check if a specific feature is available
 */
function isFeatureAvailable($feature) {
    switch ($feature) {
        case 'phpspreadsheet':
            return COMPOSER_AVAILABLE && class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet');
        case 'advanced_excel':
            return isFeatureAvailable('phpspreadsheet');
        case 'basic_csv':
            return true; // Always available
        default:
            return false;
    }
}

/**
 * Get system information for debugging
 */
function getSystemInfo() {
    return [
        'php_version' => PHP_VERSION,
        'is_cloud' => IS_CLOUD_ENVIRONMENT,
        'composer_available' => COMPOSER_AVAILABLE,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
        'features' => [
            'phpspreadsheet' => isFeatureAvailable('phpspreadsheet'),
            'advanced_excel' => isFeatureAvailable('advanced_excel'),
            'basic_csv' => isFeatureAvailable('basic_csv')
        ]
    ];
}

/**
 * Log compatibility issues
 */
function logCompatibilityIssue($message) {
    $logFile = __DIR__ . '/../auth/logs/compatibility.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    
    @file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// Log system information on first load
if (!defined('COMPATIBILITY_LOGGED')) {
    $systemInfo = getSystemInfo();
    logCompatibilityIssue("System Info: " . json_encode($systemInfo));
    define('COMPATIBILITY_LOGGED', true);
}

?>
<?php
// Debug version to check what's happening
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Debug Settings</title></head><body>";
echo "<h1>Debug Information</h1>";

try {
    require_once __DIR__ . '/../config/auth.php';
    echo "<p>✓ Auth loaded</p>";
    
    // Check if user is authenticated
    if (isset($_SESSION['user_id'])) {
        echo "<p>✓ User authenticated: " . $_SESSION['user_id'] . "</p>";
    } else {
        echo "<p>✗ User not authenticated</p>";
    }
    
    // Check constants
    echo "<h2>Constants Check:</h2>";
    echo "<p>APP_NAME: " . (defined('APP_NAME') ? APP_NAME : 'NOT DEFINED') . "</p>";
    echo "<p>BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED') . "</p>";
    echo "<p>DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "</p>";
    echo "<p>SESSION_TIMEOUT: " . (defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 'NOT DEFINED') . "</p>";
    
    // Check current user
    if (class_exists('Auth')) {
        $currentUser = Auth::getCurrentUser();
        echo "<h2>Current User:</h2>";
        echo "<pre>" . print_r($currentUser, true) . "</pre>";
    }
    
    echo "<h2>PHP Info:</h2>";
    echo "<p>PHP Version: " . PHP_VERSION . "</p>";
    echo "<p>Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
    echo "<p>Upload Max: " . ini_get('upload_max_filesize') . "</p>";
    
    echo "<hr>";
    echo "<h2>Now loading actual settings page...</h2>";
    
    // Include the actual settings page
    include __DIR__ . '/settings.php';
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
?>

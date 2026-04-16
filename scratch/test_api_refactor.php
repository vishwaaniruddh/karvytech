<?php
// Mock GET parameters
$_GET['action'] = 'get_dispatch_data';
$_GET['request_id'] = 90;

// Mock session for Auth
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['login_time'] = time();

// Capture output
ob_start();
require_once __DIR__ . '/../api/material_requests.php';
$output = ob_get_clean();

// Parse and verify
$data = json_decode($output, true);

if ($data && $data['success']) {
    echo "API SUCCESS\n";
    foreach ($data['data']['items'] as $item) {
        echo "Item: " . $item['item_name'] . " | Stock: " . ($item['stock']['available_qty'] ?? 'NULL') . "\n";
    }
} else {
    echo "API FAILED\n";
    echo $output;
}
?>

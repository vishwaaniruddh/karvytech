<?php
// Mock a request to the API
$_GET['action'] = 'get_stats';
require_once __DIR__ . '/../api/inventory.php';
echo "\n\n";
$_GET['action'] = 'get_overview';
require_once __DIR__ . '/../api/inventory.php';

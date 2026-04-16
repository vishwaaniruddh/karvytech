<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Inventory.php';

$inv = new Inventory();
try {
    echo "Testing getDispatchDetails(81)...\n";
    $data = $inv->getDispatchDetails(81);
    print_r($data);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

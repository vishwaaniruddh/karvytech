<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Inventory.php';

$inventory = new Inventory();
$data = $inventory->getDispatches(1, 20);

echo "Total: " . $data['total'] . "\n";
echo "Count of Dispatches: " . count($data['dispatches']) . "\n";
if (!empty($data['dispatches'])) {
    echo "First dispatch row:\n";
    print_r($data['dispatches'][0]);
} else {
    echo "No dispatches returned from model.\n";
}
?>

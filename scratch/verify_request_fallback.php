<?php
require_once __DIR__ . '/../models/Inventory.php';

$inventory = new Inventory();
$dispatchId = 75;

echo "--- Verifying Request Fallback for Dispatch #$dispatchId ---\n";
$dispatch = $inventory->getDispatchDetails($dispatchId);

echo "Total Items (Header): " . $dispatch['total_items'] . "\n";
echo "Items count in Manifest: " . count($dispatch['items']) . "\n";
echo "Is Request Manifest? " . ($dispatch['is_request_manifest'] ? 'YES' : 'NO') . "\n";
if (count($dispatch['items']) > 0) {
    echo "First Item: " . $dispatch['items'][0]['item_name'] . " (" . $dispatch['items'][0]['quantity_dispatched'] . ")\n";
}

if (count($dispatch['items']) == 13) {
    echo "\nVerification SUCCESSFUL. Found all 13 items from Request #90.\n";
} else {
    echo "\nVerification FAILED. Expected 13 items, found " . count($dispatch['items']) . ".\n";
}
?>

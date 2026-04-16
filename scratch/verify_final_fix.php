<?php
require_once __DIR__ . '/../models/Inventory.php';

$inventory = new Inventory();
$dispatchId = 75;

echo "--- Final Verification for Dispatch #$dispatchId ---\n";
$dispatch = $inventory->getDispatchDetails($dispatchId);

echo "Total Items (Header): " . $dispatch['total_items'] . "\n";
echo "Items count in Manifest: " . count($dispatch['items']) . "\n";
echo "Is Request Manifest? " . (isset($dispatch['is_request_manifest']) && $dispatch['is_request_manifest'] ? 'YES' : 'NO') . "\n";

if (count($dispatch['items']) == 13) {
    echo "\nVerification SUCCESSFUL. Manifest now matches exactly the 13 items from the parent request.\n";
} else {
    echo "\nVerification FAILED. Expected 13 items, found " . count($dispatch['items']) . ".\n";
    foreach ($dispatch['items'] as $i) {
        echo "- " . $i['item_name'] . "\n";
    }
}
?>

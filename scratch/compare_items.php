<?php
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/MaterialRequest.php';

$inv = new Inventory();
$mr = new MaterialRequest();

$reqId = 90;
$dispId = 75;

$reqData = $mr->findWithDetails($reqId);
$reqItems = json_decode($reqData['items'], true);

$dispData = $inv->getDispatchDetails($dispId);
$dispItems = $dispData['items'];

echo "--- Requested Items (Req #$reqId) ---\n";
foreach ($reqItems as $i) {
    echo "- " . $i['material_name'] . " (Qty: " . $i['quantity'] . ")\n";
}

echo "\n--- Currently Showing in Dispatch #$dispId (Aggregated) ---\n";
foreach ($dispItems as $i) {
    echo "- " . $i['item_name'] . " (Qty: " . $i['quantity_dispatched'] . ")\n";
}
?>

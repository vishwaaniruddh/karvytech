<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Inventory.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$dispatchId = $_GET['dispatch_id'] ?? null;

if (!$dispatchId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dispatch ID is required']);
    exit;
}

$inventoryModel = new Inventory();

header('Content-Type: application/json');

try {
    // Verify this dispatch belongs to this vendor
    $details = $inventoryModel->getDispatchDetails($dispatchId);
    if (!$details || $details['vendor_id'] != $vendorId) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access to this dispatch']);
        exit;
    }

    $items = $inventoryModel->getDispatchItemsSummary($dispatchId);

    echo json_encode([
        'success' => true,
        'data' => $items
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$dispatchId = $_POST['dispatch_id'] ?? null;
$newStatus = $_POST['new_status'] ?? null;
$trackingNumber = $_POST['tracking_number'] ?? null;
$actualDeliveryDate = $_POST['actual_delivery_date'] ?? null;
$statusRemarks = $_POST['status_remarks'] ?? null;

if (!$dispatchId || !$newStatus) {
    echo json_encode(['success' => false, 'message' => 'Dispatch ID and new status are required']);
    exit;
}

// Validate status
$validStatuses = ['prepared', 'dispatched', 'in_transit', 'delivered', 'returned'];
if (!in_array($newStatus, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $inventoryModel = new Inventory();
    
    // Prepare update data
    $updateData = [
        'dispatch_status' => $newStatus
    ];
    
    // Add optional fields if provided (only use existing columns)
    if ($trackingNumber) {
        $updateData['tracking_number'] = $trackingNumber;
    }
    
    if ($actualDeliveryDate && $newStatus === 'delivered') {
        // Note: actual_delivery_date column doesn't exist, using delivery_remarks instead
        $updateData['delivery_remarks'] = ($statusRemarks ? $statusRemarks . ' | ' : '') . 'Delivered on: ' . $actualDeliveryDate;
    } elseif ($statusRemarks) {
        $updateData['delivery_remarks'] = $statusRemarks;
    }
    
    // Update the dispatch
    $result = $inventoryModel->updateDispatchStatus($dispatchId, $updateData);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Dispatch status updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update dispatch status'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating dispatch status: ' . $e->getMessage()
    ]);
}
?>
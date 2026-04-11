<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$dispatchId = $_GET['id'] ?? null;

if (!$dispatchId) {
    echo json_encode(['success' => false, 'message' => 'Dispatch ID is required']);
    exit;
}

try {
    $inventoryModel = new Inventory();
    $dispatch = $inventoryModel->getDispatchDetails($dispatchId);
    
    if (!$dispatch) {
        echo json_encode(['success' => false, 'message' => 'Dispatch not found']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'dispatch' => $dispatch
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving dispatch details: ' . $e->getMessage()
    ]);
}
?>
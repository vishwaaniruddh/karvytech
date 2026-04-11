<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

try {
    $inventoryModel = new Inventory();
    $receiptNumber = $inventoryModel->generateReceiptNumber();
    
    echo json_encode([
        'success' => true,
        'receipt_number' => $receiptNumber
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
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

try {
    $inventoryModel = new Inventory();
    
    // Validate required fields
    $requiredFields = ['receipt_number', 'receipt_date', 'supplier_name'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    // Validate items
    if (empty($_POST['items']) || !is_array($_POST['items'])) {
        throw new Exception('At least one item is required');
    }
    
    // Prepare receipt data
    $receiptData = [
        'receipt_number' => $_POST['receipt_number'],
        'receipt_date' => $_POST['receipt_date'],
        'supplier_name' => $_POST['supplier_name'],
        'supplier_contact' => $_POST['supplier_contact'] ?? null,
        'purchase_order_number' => $_POST['purchase_order_number'] ?? null,
        'invoice_number' => $_POST['invoice_number'] ?? null,
        'invoice_date' => $_POST['invoice_date'] ?? null,
        'total_amount' => floatval($_POST['total_amount'] ?? 0),
        'remarks' => $_POST['remarks'] ?? null
    ];
    
    // Create inward receipt
    $inwardId = $inventoryModel->createInwardReceipt($receiptData);
    
    if (!$inwardId) {
        throw new Exception('Failed to create inward receipt');
    }
    
    // Prepare items data
    $items = [];
    foreach ($_POST['items'] as $itemData) {
        if (empty($itemData['boq_item_id']) || empty($itemData['quantity_received']) || empty($itemData['unit_cost'])) {
            continue; // Skip incomplete items
        }
        
        $items[] = [
            'boq_item_id' => intval($itemData['boq_item_id']),
            'quantity_received' => floatval($itemData['quantity_received']),
            'unit_cost' => floatval($itemData['unit_cost']),
            'batch_number' => $itemData['batch_number'] ?? null,
            'serial_numbers' => !empty($itemData['serial_numbers']) ? explode(',', $itemData['serial_numbers']) : null,
            'expiry_date' => $itemData['expiry_date'] ?? null,
            'quality_status' => $itemData['quality_status'] ?? 'good',
            'remarks' => $itemData['remarks'] ?? null
        ];
    }
    
    if (empty($items)) {
        throw new Exception('No valid items found');
    }
    
    // Add items to receipt
    $result = $inventoryModel->addInwardItems($inwardId, $items);
    
    if (!$result) {
        throw new Exception('Failed to add items to receipt');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Inward receipt created successfully',
        'inward_id' => $inwardId
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
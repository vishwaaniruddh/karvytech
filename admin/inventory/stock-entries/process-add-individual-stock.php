<?php
// Suppress warnings to ensure clean JSON output
error_reporting(E_ERROR | E_PARSE);

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
    $requiredFields = ['boq_item_id', 'quantity', 'unit_cost'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    $boqItemId = intval($_POST['boq_item_id']);
    $quantity = intval($_POST['quantity']);
    $unitCost = floatval($_POST['unit_cost']);
    
    if ($quantity <= 0) {
        throw new Exception('Quantity must be greater than 0');
    }
    
    if ($unitCost <= 0) {
        throw new Exception('Unit cost must be greater than 0');
    }
    
    // Get serial numbers
    $serialNumbers = $_POST['serial_numbers'] ?? [];
    $serialNumbers = array_filter($serialNumbers, function($serial) {
        return !empty(trim($serial));
    });
    
    // Prepare base item data
    $baseData = [
        'unit_cost' => $unitCost,
        'batch_number' => $_POST['batch_number'] ?? null,
        'supplier_name' => $_POST['supplier_name'] ?? null,
        'purchase_date' => $_POST['purchase_date'] ?? null,
        'warranty_period' => !empty($_POST['warranty_period']) ? intval($_POST['warranty_period']) : null,
        'purchase_order_number' => $_POST['purchase_order_number'] ?? null,
        'notes' => $_POST['notes'] ?? null,
        'quality_status' => 'good',
        'location_type' => 'warehouse',
        'location_name' => 'Main Warehouse'
    ];
    
    $addedItems = [];
    $errors = [];
    
    // Add individual items
    for ($i = 0; $i < $quantity; $i++) {
        $itemData = $baseData;
        $itemData['boq_item_id'] = $boqItemId;
        
        // Set serial number if provided
        if (isset($serialNumbers[$i]) && !empty($serialNumbers[$i])) {
            $itemData['serial_number'] = trim($serialNumbers[$i]);
        }
        
        try {
            $stockId = $inventoryModel->addIndividualStockEntry($itemData);
            if ($stockId) {
                $addedItems[] = $stockId;
            } else {
                $errors[] = "Failed to add item " . ($i + 1);
            }
        } catch (Exception $e) {
            $errors[] = "Item " . ($i + 1) . ": " . $e->getMessage();
        }
    }
    
    if (empty($addedItems)) {
        throw new Exception('No items were added. Errors: ' . implode(', ', $errors));
    }
    
    $message = count($addedItems) . ' items added successfully';
    if (!empty($errors)) {
        $message .= '. Some items failed: ' . implode(', ', $errors);
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'added_count' => count($addedItems),
        'error_count' => count($errors),
        'stock_ids' => $addedItems
    ]);
    
} catch (Exception $e) {
    error_log('Add individual stock error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
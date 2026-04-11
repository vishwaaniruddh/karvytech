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
    $requiredFields = ['boq_item_id', 'quantity', 'unit_cost'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    $quantity = intval($_POST['quantity']);
    
    // Prepare stock entry data
    $stockData = [
        'boq_item_id' => intval($_POST['boq_item_id']),
        'unit_cost' => floatval($_POST['unit_cost']),
        'batch_number' => $_POST['batch_number'] ?? null,
        'serial_number' => $_POST['serial_number'] ?? null,
        'location_type' => $_POST['location_type'] ?? 'warehouse',
        'location_name' => $_POST['location_name'] ?? null,
        'supplier_name' => $_POST['supplier_name'] ?? null,
        'purchase_date' => $_POST['purchase_date'] ?? null,
        'expiry_date' => $_POST['expiry_date'] ?? null,
        'quality_status' => $_POST['quality_status'] ?? 'good',
        'warranty_period' => $_POST['warranty_period'] ?? null,
        'notes' => $_POST['notes'] ?? null
    ];
    
    // Validate values
    if ($quantity <= 0) {
        throw new Exception('Quantity must be greater than 0');
    }
    
    if ($stockData['unit_cost'] < 0) {
        throw new Exception('Unit cost cannot be negative');
    }
    
    // Handle serial number logic based on quantity
    $db = Database::getInstance()->getConnection();
    
    if ($quantity == 1) {
        // Single item - use provided serial number as-is
        if (!empty($stockData['serial_number'])) {
            // Check if serial number already exists
            $checkSql = "SELECT id FROM inventory_stock WHERE boq_item_id = ? AND serial_number = ?";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->execute([$stockData['boq_item_id'], $stockData['serial_number']]);
            
            if ($checkStmt->fetch()) {
                throw new Exception('Serial number already exists for this item');
            }
        }
    } else {
        // Multiple items - handle serial number generation
        if (!empty($stockData['serial_number'])) {
            // User provided a serial number for multiple items - use it as base
            $baseSerial = $stockData['serial_number'];
        } else {
            // No serial number provided - generate a base using timestamp and BOQ item ID
            $baseSerial = 'ITM' . $stockData['boq_item_id'] . '_' . date('YmdHis');
        }
        
        // Check if any of the generated serial numbers would conflict
        for ($i = 0; $i < $quantity; $i++) {
            $generatedSerial = $baseSerial . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            
            $checkSql = "SELECT id FROM inventory_stock WHERE boq_item_id = ? AND serial_number = ?";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->execute([$stockData['boq_item_id'], $generatedSerial]);
            
            if ($checkStmt->fetch()) {
                throw new Exception("Generated serial number '$generatedSerial' already exists. Please use a different base serial number or leave it empty for auto-generation.");
            }
        }
    }
    
    // Add multiple individual stock entries based on quantity
    $entryIds = [];
    
    for ($i = 0; $i < $quantity; $i++) {
        $itemData = $stockData;
        
        // Handle serial number for each item
        if ($quantity == 1) {
            // Single item - use serial number as provided (can be null)
            $itemData['serial_number'] = $stockData['serial_number'];
        } else {
            // Multiple items - generate unique serial numbers
            if (!empty($stockData['serial_number'])) {
                $baseSerial = $stockData['serial_number'];
            } else {
                $baseSerial = 'ITM' . $stockData['boq_item_id'] . '_' . date('YmdHis');
            }
            $itemData['serial_number'] = $baseSerial . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
        }
        
        $entryId = $inventoryModel->addIndividualStockEntry($itemData);
        
        if (!$entryId) {
            throw new Exception('Failed to add stock entry ' . ($i + 1) . (isset($itemData['serial_number']) ? ' (Serial: ' . $itemData['serial_number'] . ')' : ''));
        }
        
        $entryIds[] = $entryId;
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully added $quantity stock entries",
        'entry_ids' => $entryIds,
        'total_entries' => count($entryIds)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Inventory.php';

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
  //  $requiredFields = ['boq_item_id', 'minimum_stock', 'maximum_stock', 'unit_cost'];
    $requiredFields = ['boq_item_id', 'stock_quantity', 'unit_cost'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || $_POST[$field] === '') {
            throw new Exception("Field '$field' is required");
        }
    }
    
    $boqItemId = intval($_POST['boq_item_id']);
    $minStock = floatval($_POST['minimum_stock']);
    $maxStock = floatval($_POST['maximum_stock']);
    $unitCost = floatval($_POST['unit_cost']);
    $stockQuantity = intval($_POST['stock_quantity']);
    
    // Validate values
    /*
        if ($minStock < 0) {
            throw new Exception('Minimum stock cannot be negative');
        }
        
        if ($maxStock < $minStock) {
            throw new Exception('Maximum stock must be greater than or equal to minimum stock');
        }
    */
    if ($unitCost < 0) {
        throw new Exception('Unit cost cannot be negative');
    }
    
    // Update stock levels
   // $result = $inventoryModel->updateStockLevels($boqItemId, $minStock, $maxStock, $unitCost);
    $result = $inventoryModel->updateStockLevelsNew($boqItemId, $minStock, $maxStock, $unitCost, $stockQuantity);
    
    if (!$result) {
        throw new Exception('Failed to update stock levels');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Stock levels updated successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
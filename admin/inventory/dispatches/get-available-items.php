<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

try {
    $inventoryModel = new Inventory();
    
    // Get items with available stock
    $items = $inventoryModel->getStockOverview('', '', false);
    
    // Filter items with available stock > 0
    $availableItems = array_filter($items, function($item) {
        return $item['available_stock'] > 0;
    });
    
    echo json_encode([
        'success' => true,
        'items' => array_values($availableItems)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
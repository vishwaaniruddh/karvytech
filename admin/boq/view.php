<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/BoqItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$itemId = $_GET['id'] ?? null;

if (!$itemId) {
    echo json_encode(['success' => false, 'message' => 'Item ID is required']);
    exit;
}

try {
    $boqModel = new BoqItem();
    $item = $boqModel->find($itemId);
    
    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'BOQ item not found']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'item' => $item
    ]);
    
} catch (Exception $e) {
    error_log('BOQ item view error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while loading the BOQ item'
    ]);
}
?>
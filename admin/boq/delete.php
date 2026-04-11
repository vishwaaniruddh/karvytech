<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/BoqItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

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
    
    // Check if item is being used in any material requests
    // You might want to add this check based on your business logic
    
    // Delete BOQ item
    $success = $boqModel->delete($itemId);
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'BOQ item deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete BOQ item'
        ]);
    }
    
} catch (Exception $e) {
    error_log('BOQ item deletion error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while deleting the BOQ item'
    ]);
}
?>
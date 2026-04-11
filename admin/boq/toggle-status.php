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
$status = $_GET['status'] ?? null;

if (!$itemId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Item ID and status are required']);
    exit;
}

if (!in_array($status, ['active', 'inactive'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

try {
    $boqModel = new BoqItem();
    $item = $boqModel->find($itemId);
    
    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'BOQ item not found']);
        exit;
    }
    
    // Update status
    $success = $boqModel->updateStatus($itemId, $status);
    
    if ($success) {
        $action = $status === 'active' ? 'activated' : 'deactivated';
        echo json_encode([
            'success' => true,
            'message' => "BOQ item {$action} successfully"
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update BOQ item status'
        ]);
    }
    
} catch (Exception $e) {
    error_log('BOQ item status update error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating the BOQ item status'
    ]);
}
?>
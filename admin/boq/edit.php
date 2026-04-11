<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/BoqItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$boqModel = new BoqItem();
$itemId = $_GET['id'] ?? null;

if (!$itemId) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Item ID is required']);
    } else {
        header('Location: index.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $item = $boqModel->find($itemId);
        
        if (!$item) {
            echo json_encode(['success' => false, 'message' => 'BOQ item not found']);
            exit;
        }
        
        $data = [
            'item_code' => trim($_POST['item_code'] ?? ''),
            'item_name' => trim($_POST['item_name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'unit' => trim($_POST['unit'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'icon_class' => trim($_POST['icon_class'] ?? ''),
            'status' => $_POST['status'] ?? 'active',
            'need_serial_number' => isset($_POST['need_serial_number']) ? 1 : 0
        ];
        
        // Validate data
        $errors = $boqModel->validateItemData($data, true, $itemId);
        
        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors
            ]);
            exit;
        }
        
        // Update BOQ item
        $success = $boqModel->update($itemId, $data);
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'BOQ item updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update BOQ item'
            ]);
        }
        
    } catch (Exception $e) {
        error_log('BOQ item update error: ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while updating the BOQ item'
        ]);
    }
} else {
    // GET request - return item data for editing
    header('Content-Type: application/json');
    
    $item = $boqModel->find($itemId);
    
    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'BOQ item not found']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'item' => $item
    ]);
}
?>
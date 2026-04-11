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

try {
    $boqModel = new BoqItem();
    
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
    $errors = $boqModel->validateItemData($data);
    
    if (!empty($errors)) {
    // Convert errors array to a single string
    $errorMessage = implode(', ', $errors);

    echo json_encode([
        'success' => false,
        'message' => "Failed: $errorMessage"
    ]);
        exit;
    }
    
    // if (!empty($errors)) {
    //     echo json_encode([
    //         'success' => false,
    //         'message' => 'Validation failed',
    //         'errors' => $errors
    //     ]);
    //     exit;
    // }
    
    // Create BOQ item
    $itemId = $boqModel->create($data);
    
    if ($itemId) {
        echo json_encode([
            'success' => true,
            'message' => 'BOQ item created successfully',
            'item_id' => $itemId
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create BOQ item'
        ]);
    }
    
} catch (Exception $e) {
    error_log('BOQ item creation error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while creating the BOQ item'
    ]);
}
?>
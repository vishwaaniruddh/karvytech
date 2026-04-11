<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/MaterialRequest.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['request_id']) || !isset($input['items'])) {
        throw new Exception('Missing required fields');
    }
    
    $requestId = $input['request_id'];
    $items = $input['items'];
    
    if (empty($items)) {
        throw new Exception('Cannot update request with no items');
    }
    
    // Validate items
    foreach ($items as $item) {
        if (empty($item['boq_item_id']) || empty($item['quantity']) || $item['quantity'] <= 0) {
            throw new Exception('Invalid item data');
        }
    }
    
    $materialRequestModel = new MaterialRequest();
    
    // Get current request
    $request = $materialRequestModel->find($requestId);
    if (!$request) {
        throw new Exception('Request not found');
    }
    
    // Only allow editing for pending or approved requests
    if (!in_array($request['status'], ['pending', 'approved'])) {
        throw new Exception('Cannot edit items for requests with status: ' . $request['status']);
    }
    
    // Update items
    $data = [
        'items' => json_encode($items)
    ];
    
    $success = $materialRequestModel->update($requestId, $data);
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Items updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update items');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

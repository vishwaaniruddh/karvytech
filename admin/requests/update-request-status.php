<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/MaterialRequest.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['request_id']) || empty($input['status'])) {
        throw new Exception('Request ID and status are required');
    }
    
    $requestId = intval($input['request_id']);
    $status = $input['status'];
    $currentUser = Auth::getCurrentUser();
    
    // Validate status
    $validStatuses = ['pending', 'approved', 'rejected', 'dispatched', 'completed', 'cancelled'];
    if (!in_array($status, $validStatuses)) {
        throw new Exception('Invalid status');
    }
    
    $materialRequestModel = new MaterialRequest();
    
    // Update the request status
    $result = $materialRequestModel->updateStatus(
        $requestId, 
        $status, 
        $currentUser['id'], 
        date('Y-m-d H:i:s')
    );
    
    if (!$result) {
        throw new Exception('Failed to update request status');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Request status updated successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
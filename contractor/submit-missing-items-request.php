<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/MaterialRequest.php';

// Require vendor authentication
Auth::requireRole(VENDOR_ROLE);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $currentUser = Auth::getCurrentUser();
    $vendorId = $currentUser['vendor_id'];
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['missing_items']) || empty($input['site_id'])) {
        throw new Exception('Missing items and site ID are required');
    }
    
    $materialRequestModel = new MaterialRequest();
    
    // Verify access to original request
    if (!empty($input['original_request_id'])) {
        $originalRequest = $materialRequestModel->findWithDetails($input['original_request_id']);
        if (!$originalRequest || $originalRequest['vendor_id'] != $vendorId) {
            throw new Exception('Unauthorized access to original request');
        }
    }
    
    // Prepare material request data
    $requestData = [
        'site_id' => $input['site_id'],
        'vendor_id' => $vendorId,
        'request_date' => date('Y-m-d'),
        'required_date' => date('Y-m-d', strtotime('+3 days')), // Default to 3 days from now
        'priority' => $input['priority'] ?? 'high',
        'request_notes' => $input['notes'] ?? 'Follow-up request for missing items from partial delivery',
        'items' => json_encode($input['missing_items']),
        'status' => 'pending',
        'request_type' => 'missing_items_followup',
        'parent_request_id' => $input['original_request_id'] ?? null,
        'parent_dispatch_id' => $input['original_dispatch_id'] ?? null
    ];
    
    // Create the material request
    $requestId = $materialRequestModel->create($requestData);
    
    if (!$requestId) {
        throw new Exception('Failed to create missing items request');
    }
    
    // Update original dispatch status to indicate follow-up requested
    if (!empty($input['original_dispatch_id'])) {
        require_once __DIR__ . '/../models/Inventory.php';
        $inventoryModel = new Inventory();
        
        // Add a note to the original dispatch
        $inventoryModel->addDispatchNote(
            $input['original_dispatch_id'],
            "Follow-up request created for missing items (Request #{$requestId})",
            $currentUser['id']
        );
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Missing items request submitted successfully',
        'request_id' => $requestId
    ]);
    
} catch (Exception $e) {
    error_log('Missing items request error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
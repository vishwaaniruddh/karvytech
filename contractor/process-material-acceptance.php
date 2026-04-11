<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Inventory.php';

// Require vendor authentication
Auth::requireRole(VENDOR_ROLE);

$currentUser = Auth::getCurrentUser();
$vendorId = $currentUser['vendor_id'];

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['dispatch_id']) || !isset($input['action'])) {
        throw new Exception('Dispatch ID and action are required');
    }
    
    $dispatchId = (int)$input['dispatch_id'];
    $action = $input['action'];
    
    $inventoryModel = new Inventory();
    
    // Get dispatch details to verify vendor access
    $dispatch = $inventoryModel->getDispatchById($dispatchId);
    
    if (!$dispatch) {
        throw new Exception('Dispatch not found');
    }
    
    // Verify vendor has access to this dispatch
    if ($dispatch['vendor_id'] != $vendorId) {
        throw new Exception('Access denied');
    }
    
    // Check current status
    if ($dispatch['dispatch_status'] === 'confirmed') {
        throw new Exception('This receipt has already been confirmed');
    }
    
    if ($action === 'accept_all') {
        // Update dispatch status to 'delivered' (contractor confirms receipt)
        // This automatically updates it in admin section
        $updateData = [
            'dispatch_status' => 'delivered',
            'delivery_date' => date('Y-m-d H:i:s'),
            'received_by' => $currentUser['id'],
            'delivery_remarks' => $dispatch['delivery_remarks'] 
                ? $dispatch['delivery_remarks'] . "\n\n[" . date('Y-m-d H:i:s') . "] Materials received and confirmed by contractor"
                : 'Materials received and confirmed by contractor'
        ];
        
        $result = $inventoryModel->updateDispatchStatus($dispatchId, $updateData);
        
        if (!$result) {
            throw new Exception('Failed to update receipt status');
        }
        
        // Log the acceptance
        error_log("Dispatch #{$dispatchId} accepted by vendor #{$vendorId} (User: {$currentUser['username']})");
        
        echo json_encode([
            'success' => true,
            'message' => 'Materials accepted successfully. Status updated to Delivered.',
            'dispatch_id' => $dispatchId,
            'new_status' => 'delivered'
        ]);
    } else {
        throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

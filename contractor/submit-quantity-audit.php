<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../includes/audit_integration.php';

header('Content-Type: application/json');

// Require vendor authentication
Auth::requireRole(VENDOR_ROLE);

$currentUser = Auth::getCurrentUser();
$vendorId = $currentUser['vendor_id'];

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    $requiredFields = ['dispatch_id', 'boq_item_id', 'original_quantity', 'corrected_quantity', 'reason', 'request_id'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || $input[$field] === '') {
            throw new Exception("Missing required field: $field");
        }
    }
    
    $dispatchId = (int)$input['dispatch_id'];
    $boqItemId = (int)$input['boq_item_id'];
    $originalQuantity = (float)$input['original_quantity'];
    $correctedQuantity = (float)$input['corrected_quantity'];
    $reason = trim($input['reason']);
    $requestId = (int)$input['request_id'];
    
    // Validate quantities
    if ($correctedQuantity < 0) {
        throw new Exception('Corrected quantity cannot be negative');
    }
    
    if ($originalQuantity === $correctedQuantity) {
        throw new Exception('Corrected quantity is the same as original quantity');
    }
    
    if (strlen($reason) < 10) {
        throw new Exception('Reason must be at least 10 characters long');
    }
    
    $inventoryModel = new Inventory();
    
    // Verify dispatch belongs to this vendor
    $dispatch = $inventoryModel->getDispatchByRequestId($requestId);
    if (!$dispatch || $dispatch['id'] != $dispatchId) {
        throw new Exception('Invalid dispatch or access denied');
    }
    
    // Verify the dispatch has been confirmed (status should be confirmed or partially_delivered)
    if (!in_array($dispatch['dispatch_status'], ['confirmed', 'partially_delivered'])) {
        throw new Exception('Can only audit confirmed or partially delivered dispatches');
    }
    
    // Create audit record
    $db = Database::getInstance()->getConnection();
    
    // Insert into quantity_audit table
    $auditSql = "INSERT INTO quantity_audit (
        dispatch_id, 
        boq_item_id, 
        vendor_id, 
        original_quantity, 
        corrected_quantity, 
        reason, 
        status, 
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";
    
    $auditStmt = $db->prepare($auditSql);
    $auditStmt->execute([
        $dispatchId,
        $boqItemId,
        $vendorId,
        $originalQuantity,
        $correctedQuantity,
        $reason
    ]);
    
    $auditId = $db->lastInsertId();
    
    // Log the audit action
    auditLogAction(
        'quantity_audit_submitted',
        'contractor/submit-quantity-audit.php',
        200,
        [
            'dispatch_id' => $dispatchId,
            'boq_item_id' => $boqItemId,
            'original_quantity' => $originalQuantity,
            'corrected_quantity' => $correctedQuantity,
            'reason' => $reason,
            'audit_id' => $auditId
        ]
    );
    
    // TODO: Send notification to admin about the audit request
    // This could be implemented as an email notification or in-app notification
    
    echo json_encode([
        'success' => true,
        'audit_id' => $auditId,
        'message' => 'Quantity audit submitted successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
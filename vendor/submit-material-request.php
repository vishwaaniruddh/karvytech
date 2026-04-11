<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/MaterialRequest.php';
require_once __DIR__ . '/../models/Installation.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();
$currentUser = Auth::getCurrentUser();

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['installation_id']) || !isset($input['material_name']) || !isset($input['request_quantity'])) {
        throw new Exception('Installation ID, material name, and request quantity are required');
    }
    
    $installationId = (int)$input['installation_id'];
    $materialName = trim($input['material_name']);
    $requestQuantity = (float)$input['request_quantity'];
    $priority = $input['priority'] ?? 'medium';
    $reason = trim($input['reason'] ?? '');
    $currentRemaining = (float)($input['current_remaining'] ?? 0);
    
    // Validate inputs
    if ($requestQuantity <= 0) {
        throw new Exception('Request quantity must be greater than 0');
    }
    
    if (empty($reason)) {
        throw new Exception('Reason for request is required');
    }
    
    $installationModel = new Installation();
    $materialRequestModel = new MaterialRequest();
    
    // Verify vendor access to this installation
    $installation = $installationModel->getInstallationDetails($installationId);
    if (!$installation || $installation['vendor_id'] != $vendorId) {
        throw new Exception('Access denied');
    }
    
    // Prepare request data
    $requestData = [
        'vendor_id' => $vendorId,
        'site_id' => $installation['site_id'],
        'installation_id' => $installationId,
        'material_name' => $materialName,
        'quantity_requested' => $requestQuantity,
        'current_stock' => $currentRemaining,
        'priority' => $priority,
        'reason' => $reason,
        'request_type' => 'installation_shortage',
        'status' => 'pending',
        'requested_by' => $currentUser['id'],
        'request_date' => date('Y-m-d H:i:s')
    ];
    
    // Submit the request
    $requestId = $materialRequestModel->createRequest($requestData);
    
    if ($requestId) {
        echo json_encode([
            'success' => true,
            'message' => 'Material request submitted successfully',
            'request_id' => $requestId,
            'data' => [
                'material_name' => $materialName,
                'quantity' => $requestQuantity,
                'priority' => $priority,
                'status' => 'pending'
            ]
        ]);
    } else {
        throw new Exception('Failed to submit material request');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
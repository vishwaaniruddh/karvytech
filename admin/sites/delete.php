<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/SuperadminRequest.php';
require_once __DIR__ . '/../../models/Site.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid site ID']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

header('Content-Type: application/json');

try {
    $currentUser = Auth::getCurrentUser();
    
    // All users (including superadmin) create deletion requests
    $siteModel = new Site();
    $site = $siteModel->find($id);
    
    if (!$site) {
        echo json_encode(['success' => false, 'message' => 'Site not found']);
        exit;
    }
    
    $requestModel = new SuperadminRequest();
    
    // Create request data
    $requestData = [
        'request_type' => 'site_deletion',
        'request_title' => 'Delete Site: ' . $site['site_id'],
        'request_description' => 'Request to delete site ' . $site['site_id'] . ' (' . $site['location'] . ')',
        'requested_by' => $currentUser['id'],
        'requested_by_name' => $currentUser['username'],
        'requested_by_role' => $currentUser['role'],
        'request_data' => json_encode([
            'site_id' => $site['id'],
            'site_code' => $site['site_id'],
            'location' => $site['location'],
            'customer' => $site['customer'] ?? 'N/A'
        ]),
        'reference_id' => $id,
        'reference_table' => 'sites',
        'priority' => Auth::isSuperAdmin() ? 'high' : 'medium'
    ];
    
    $requestId = $requestModel->createRequest($requestData);
    
    if ($requestId) {
        $message = Auth::isSuperAdmin() 
            ? 'Site deletion request created successfully. You can review and approve it in Superadmin Actions.' 
            : 'Site deletion request submitted successfully. Awaiting superadmin approval.';
            
        echo json_encode([
            'success' => true,
            'message' => $message,
            'request_id' => $requestId
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create deletion request']);
    }
    
} catch (Exception $e) {
    error_log('Site deletion error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing the request']);
}
?>
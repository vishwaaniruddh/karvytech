<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/SuperadminRequest.php';
require_once __DIR__ . '/../../models/Site.php';

// Require superadmin authentication
Auth::requireRole('superadmin');

header('Content-Type: application/json');

$currentUser = Auth::getCurrentUser();
$requestModel = new SuperadminRequest();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$action = $input['action'] ?? '';
$id = $input['id'] ?? 0;
$remarks = $input['remarks'] ?? '';

if (!$id || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    // Get the request details first
    $request = $requestModel->find($id);
    
    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found']);
        exit;
    }
    
    if ($action === 'approve') {
        // If it's a site deletion request, perform the actual permanent deletion with backup
        if ($request['request_type'] === 'site_deletion' && $request['reference_id']) {
            $siteModel = new Site();
            
            // Permanently delete the site with backup (pass requestId and deletedBy)
            $deleteSuccess = $siteModel->permanentDelete(
                $request['reference_id'],
                $id, // request ID for backup reference
                $currentUser['id'] // deleted by (superadmin who approved)
            );
            
            if (!$deleteSuccess) {
                echo json_encode(['success' => false, 'message' => 'Failed to delete site']);
                exit;
            }
        }
        
        $success = $requestModel->approve($id, $currentUser['id'], $remarks);
        $message = 'Request approved successfully';
        
        // Add specific message for site deletion
        if ($request['request_type'] === 'site_deletion') {
            $message = 'Site deletion approved and site has been permanently removed. Backup created for potential restoration.';
        }
    } else {
        if (empty($remarks)) {
            echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
            exit;
        }
        $success = $requestModel->reject($id, $currentUser['id'], $remarks);
        $message = 'Request rejected successfully';
    }
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to process request']);
    }
    
} catch (Exception $e) {
    error_log('Superadmin request processing error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>

<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/BoqMaster.php';
require_once __DIR__ . '/../../models/BoqMasterItem.php';
require_once __DIR__ . '/../../models/AuditLog.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);
$currentUser = Auth::getCurrentUser();

// Set header before any output
header('Content-Type: application/json');

// Log the request
error_log('BOQ Delete Request - Method: ' . $_SERVER['REQUEST_METHOD'] . ', ID: ' . ($_GET['id'] ?? 'none'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log('BOQ Delete Failed - Invalid method: ' . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    error_log('BOQ Delete Failed - Missing ID');
    echo json_encode(['success' => false, 'message' => 'Missing ID']);
    exit;
}

try {
    $boqMaster = new BoqMaster();
    $boqItemLink = new BoqMasterItem();
    $auditLog = new AuditLog();
    
    error_log('BOQ Delete - Fetching BOQ with ID: ' . $id);
    
    // Fetch details before delete for logging
    $boq = $boqMaster->find($id);
    
    if (!$boq) {
        error_log('BOQ Delete Failed - BOQ not found with ID: ' . $id);
        echo json_encode(['success' => false, 'message' => 'BOQ Set not found']);
        exit;
    }
    
    error_log('BOQ Delete - Found BOQ: ' . $boq['boq_name']);
    
    // Delete links first
    error_log('BOQ Delete - Deleting linked items');
    $boqItemLink->deleteByMasterId($id);
    
    // Delete master
    error_log('BOQ Delete - Deleting master record');
    $success = $boqMaster->delete($id);
    
    if ($success) {
        error_log('BOQ Delete Success - ID: ' . $id);
        
        // Log activity
        $auditLog->log([
            'user_id' => $currentUser['id'],
            'username' => $currentUser['username'],
            'user_role' => $currentUser['role'],
            'action_type' => 'delete_boq',
            'endpoint' => $_SERVER['REQUEST_URI'],
            'request_data' => json_encode([
                'id' => $id,
                'boq_name' => $boq['boq_name'] ?? 'Unknown'
            ]),
            'status_code' => 200
        ]);
        
        echo json_encode(['success' => true, 'message' => 'BOQ Set deleted successfully']);
    } else {
        error_log('BOQ Delete Failed - Delete operation returned false');
        echo json_encode(['success' => false, 'message' => 'Failed to delete BOQ Set']);
    }
    
} catch (Exception $e) {
    error_log('BOQ Delete Exception: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

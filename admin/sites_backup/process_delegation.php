<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/SiteDelegation.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $delegationModel = new SiteDelegation();
    $action = $_POST['action'] ?? 'delegate';
    
    switch ($action) {
        case 'delegate':
            $siteId = $_POST['site_id'] ?? null;
            $vendorId = $_POST['vendor_id'] ?? null;
            $notes = $_POST['notes'] ?? '';
            
            if (!$siteId || !$vendorId) {
                throw new Exception('Site ID and Vendor ID are required');
            }
            
            $delegationId = $delegationModel->delegateSite($siteId, $vendorId, Auth::getUserId(), $notes);
            
            echo json_encode([
                'success' => true,
                'message' => 'Site delegated successfully',
                'delegation_id' => $delegationId
            ]);
            break;
            
        case 'complete':
            $delegationId = $_POST['delegation_id'] ?? null;
            
            if (!$delegationId) {
                throw new Exception('Delegation ID is required');
            }
            
            $delegationModel->completeDelegation($delegationId, Auth::getUserId());
            
            echo json_encode([
                'success' => true,
                'message' => 'Delegation marked as completed'
            ]);
            break;
            
        case 'cancel':
            $delegationId = $_POST['delegation_id'] ?? null;
            
            if (!$delegationId) {
                throw new Exception('Delegation ID is required');
            }
            
            $delegationModel->cancelDelegation($delegationId, Auth::getUserId());
            
            echo json_encode([
                'success' => true,
                'message' => 'Delegation cancelled successfully'
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
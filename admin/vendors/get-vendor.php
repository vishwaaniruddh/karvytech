<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Vendor.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $vendorModel = new Vendor();
    $vendorId = intval($_GET['id'] ?? 0);
    
    if (!$vendorId) {
        throw new Exception('Vendor ID is required');
    }
    
    $vendor = $vendorModel->find($vendorId);
    
    if (!$vendor) {
        throw new Exception('Vendor not found');
    }
    
    // If detailed view is requested, include additional information
    if (isset($_GET['detailed']) && $_GET['detailed'] === 'true') {
        // Get vendor delegations
        $delegations = $vendorModel->getVendorDelegations($vendorId);
        $vendor['delegations'] = $delegations;
        
        // Get vendor permissions if available
        try {
            require_once __DIR__ . '/../../models/VendorPermission.php';
            $permissionModel = new VendorPermission();
            $permissions = $permissionModel->getVendorPermissions($vendorId);
            $vendor['permissions'] = $permissions;
        } catch (Exception $e) {
            // Permissions model might not be available
            $vendor['permissions'] = [];
        }
    }
    
    echo json_encode([
        'success' => true,
        'vendor' => $vendor
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
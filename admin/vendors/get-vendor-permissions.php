<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Vendor.php';
require_once __DIR__ . '/../../models/VendorPermission.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

try {
    $vendorId = $_GET['vendor_id'] ?? null;
    
    if (!$vendorId) {
        throw new Exception('Vendor ID is required');
    }
    
    $vendorModel = new Vendor();
    $permissionModel = new VendorPermission();
    
    $vendor = $vendorModel->find($vendorId);
    if (!$vendor) {
        throw new Exception('Vendor not found');
    }
    
    $permissions = $permissionModel->getVendorPermissions($vendorId);
    
    echo json_encode([
        'success' => true,
        'vendor' => $vendor,
        'permissions' => $permissions
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/VendorPermission.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $vendorId = $_POST['vendor_id'] ?? null;
    $permissions = $_POST['permissions'] ?? [];
    
    if (!$vendorId) {
        throw new Exception('Vendor ID is required');
    }
    
    $permissionModel = new VendorPermission();
    $allPermissions = $permissionModel->getAllPermissions();
    $adminId = Auth::getUserId();
    
    // Update each permission
    foreach ($allPermissions as $key => $label) {
        $value = isset($permissions[$key]) && $permissions[$key] == '1';
        $permissionModel->setPermission($vendorId, $key, $value, $adminId);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Vendor permissions updated successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
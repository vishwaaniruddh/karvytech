<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Installation.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();

header('Content-Type: application/json');

try {
    $installationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$installationId) {
        throw new Exception('Installation ID is required');
    }
    
    $installationModel = new Installation();
    
    // Verify vendor access to this installation
    $installation = $installationModel->getInstallationDetails($installationId);
    if (!$installation || $installation['vendor_id'] != $vendorId) {
        throw new Exception('Access denied');
    }
    
    $progress = $installationModel->getInstallationProgress($installationId);
    
    echo json_encode([
        'success' => true,
        'progress' => $progress
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
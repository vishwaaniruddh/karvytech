<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../models/SiteDelegation.php';

// Require vendor authentication
Auth::requireVendor();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $siteId = $_POST['site_id'] ?? null;
    $delegationId = $_POST['delegation_id'] ?? null;
    $markComplete = isset($_POST['mark_delegation_complete']) && $_POST['mark_delegation_complete'] == '1';
    
    if (!$siteId || !$delegationId) {
        throw new Exception('Site ID and Delegation ID are required');
    }
    
    $vendorId = Auth::getVendorId();
    $siteModel = new Site();
    $delegationModel = new SiteDelegation();
    
    // Verify delegation belongs to current vendor
    $delegation = $delegationModel->find($delegationId);
    if (!$delegation || $delegation['vendor_id'] != $vendorId) {
        throw new Exception('Access denied');
    }
    
    // Prepare site update data
    $siteUpdateData = [];
    
    // Survey status
    if (isset($_POST['survey_status'])) {
        $siteUpdateData['survey_status'] = (int)$_POST['survey_status'];
        if ($_POST['survey_status'] == '1' && !empty($_POST['survey_submission_date'])) {
            $siteUpdateData['survey_submission_date'] = $_POST['survey_submission_date'];
        }
    }
    
    // Installation status
    if (isset($_POST['installation_status'])) {
        $siteUpdateData['installation_status'] = (int)$_POST['installation_status'];
        if ($_POST['installation_status'] == '1' && !empty($_POST['installation_date'])) {
            $siteUpdateData['installation_date'] = $_POST['installation_date'];
        }
    }
    
    // Activity status
    if (!empty($_POST['activity_status'])) {
        $siteUpdateData['activity_status'] = $_POST['activity_status'];
    }
    
    // Material request
    if (isset($_POST['is_material_request_generated'])) {
        $siteUpdateData['is_material_request_generated'] = 1;
    } else {
        $siteUpdateData['is_material_request_generated'] = 0;
    }
    
    // Remarks
    if (isset($_POST['remarks'])) {
        $siteUpdateData['remarks'] = $_POST['remarks'];
    }
    
    // Update site
    $siteModel->update($siteId, $siteUpdateData);
    
    $response = [
        'success' => true,
        'message' => 'Progress updated successfully',
        'delegation_completed' => false
    ];
    
    // Mark delegation as complete if requested
    if ($markComplete) {
        $delegationModel->completeDelegation($delegationId, Auth::getUserId());
        $response['message'] = 'Installation completed and delegation closed successfully';
        $response['delegation_completed'] = true;
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
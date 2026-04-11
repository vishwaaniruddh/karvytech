<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/SiteSurvey.php';

// Require vendor authentication
Auth::requireVendor();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $surveyId = $_POST['survey_id'] ?? null;
    if (!$surveyId) {
        throw new Exception('Survey ID is required');
    }
    
    $vendorId = Auth::getVendorId();
    $surveyModel = new SiteSurvey();
    
    // Verify ownership
    $existingSurvey = $surveyModel->find($surveyId);
    if (!$existingSurvey || $existingSurvey['vendor_id'] != $vendorId) {
        throw new Exception('Survey not found or access denied');
    }
    
    // Only allow editing if status is pending or rejected
    if (!in_array($existingSurvey['survey_status'], ['pending', 'rejected'])) {
        throw new Exception('This survey cannot be edited');
    }
    
    // Prepare update data (same as create, but update instead)
    $updateData = [
        'survey_status' => 'completed',
        'submitted_date' => date('Y-m-d H:i:s'),
        
        // Time tracking
        'checkin_datetime' => $_POST['checkin_datetime'] ?? null,
        'checkout_datetime' => $_POST['checkout_datetime'] ?? null,
        'working_hours' => $_POST['working_hours'] ?? null,
        
        // Site information
        'store_model' => $_POST['store_model'] ?? null,
        
        // Floor and ceiling
        'floor_height' => $_POST['floor_height'] ?? null,
        'ceiling_type' => $_POST['ceiling_type'] ?? null,
        'floor_height_photo_remarks' => $_POST['floor_height_photo_remarks'] ?? null,
        'ceiling_photo_remarks' => $_POST['ceiling_photo_remarks'] ?? null,
        
        // Camera assessment
        'total_cameras' => $_POST['total_cameras'] ?? null,
        'slp_cameras' => $_POST['slp_cameras'] ?? null,
        'analytic_cameras' => $_POST['analytic_cameras'] ?? null,
        'analytic_photos_remarks' => $_POST['analytic_photos_remarks'] ?? null,
        
        // POE Rack
        'existing_poe_rack' => $_POST['existing_poe_rack'] ?? null,
        'existing_poe_photos_remarks' => $_POST['existing_poe_photos_remarks'] ?? null,
        'space_new_rack' => $_POST['space_new_rack'] ?? null,
        'space_new_rack_photo_remarks' => $_POST['space_new_rack_photo_remarks'] ?? null,
        'new_poe_rack' => $_POST['new_poe_rack'] ?? null,
        'new_poe_photos_remarks' => $_POST['new_poe_photos_remarks'] ?? null,
        
        // Zones
        'zones_recommended' => $_POST['zones_recommended'] ?? null,
        
        // Materials
        'rrl_delivery_status' => $_POST['rrl_delivery_status'] ?? null,
        'rrl_photos_remarks' => $_POST['rrl_photos_remarks'] ?? null,
        'kptl_space' => $_POST['kptl_space'] ?? null,
        'kptl_photos_remarks' => $_POST['kptl_photos_remarks'] ?? null,
        
        // Technical assessment
        'site_accessibility' => $_POST['site_accessibility'] ?? null,
        'site_accessibility_others' => $_POST['site_accessibility_others'] ?? null,
        'power_availability' => $_POST['power_availability'] ?? null,
        'network_connectivity' => $_POST['network_connectivity'] ?? null,
        'nos_of_ladder' => $_POST['nos_of_ladder'] ?? null,
        'ladder_size' => $_POST['ladder_size'] ?? null,
        
        // Survey findings
        'technical_remarks' => $_POST['technical_remarks'] ?? null,
        'challenges_identified' => $_POST['challenges_identified'] ?? null,
        'recommendations' => $_POST['recommendations'] ?? null,
        'additional_equipment_needed' => $_POST['additional_equipment_needed'] ?? null,
        'estimated_completion_days' => $_POST['estimated_completion_days'] ?? null,
        'site_photos_remarks' => $_POST['site_photos_remarks'] ?? null,
        
        // Work requirements
        'electrical_work_required' => isset($_POST['electrical_work_required']) ? 1 : 0,
        'civil_work_required' => isset($_POST['civil_work_required']) ? 1 : 0,
        'network_work_required' => isset($_POST['network_work_required']) ? 1 : 0
    ];
    
    // Update the survey
    $surveyModel->update($surveyId, $updateData);
    
    echo json_encode([
        'success' => true,
        'message' => 'Survey updated successfully',
        'survey_id' => $surveyId
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

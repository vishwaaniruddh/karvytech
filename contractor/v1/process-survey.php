<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/SiteSurvey.php';
require_once __DIR__ . '/../models/SiteDelegation.php';

// Require vendor authentication
Auth::requireVendor();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $vendorId = Auth::getVendorId();
    $siteId = $_POST['site_id'] ?? null;
    $delegationId = $_POST['delegation_id'] ?? null;
    
    if (!$siteId || !$delegationId) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    // Verify delegation belongs to vendor
    $delegationModel = new SiteDelegation();
    $delegation = $delegationModel->find($delegationId);
    
    if (!$delegation || $delegation['vendor_id'] != $vendorId) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }
    
    // Handle file uploads
    $uploadedPhotos = [];
    if (isset($_FILES['site_photos']) && !empty($_FILES['site_photos']['name'][0])) {
        $uploadDir = __DIR__ . '/../assets/uploads/surveys/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        foreach ($_FILES['site_photos']['name'] as $key => $filename) {
            if ($_FILES['site_photos']['error'][$key] === UPLOAD_ERR_OK) {
                $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $newFilename = 'survey_' . $delegationId . '_' . time() . '_' . $key . '.' . $fileExtension;
                    $uploadPath = $uploadDir . $newFilename;
                    
                    if (move_uploaded_file($_FILES['site_photos']['tmp_name'][$key], $uploadPath)) {
                        $uploadedPhotos[] = 'assets/uploads/surveys/' . $newFilename;
                    }
                }
            }
        }
    }
    
    // Prepare survey data
    $surveyData = [
        'site_id' => $siteId,
        'vendor_id' => $vendorId,
        'delegation_id' => $delegationId,
        'survey_status' => 'completed',
        'survey_date' => $_POST['survey_date'] ?? null,
        'site_accessibility' => $_POST['site_accessibility'] ?? null,
        'power_availability' => $_POST['power_availability'] ?? null,
        'network_connectivity' => $_POST['network_connectivity'] ?? null,
        'space_adequacy' => $_POST['space_adequacy'] ?? null,
        'security_level' => $_POST['security_level'] ?? null,
        'electrical_work_required' => isset($_POST['electrical_work_required']) ? 1 : 0,
        'civil_work_required' => isset($_POST['civil_work_required']) ? 1 : 0,
        'network_work_required' => isset($_POST['network_work_required']) ? 1 : 0,
        'additional_equipment_needed' => $_POST['additional_equipment_needed'] ?? null,
        'technical_remarks' => $_POST['technical_remarks'] ?? null,
        'challenges_identified' => $_POST['challenges_identified'] ?? null,
        'recommendations' => $_POST['recommendations'] ?? null,
        'estimated_completion_days' => $_POST['estimated_completion_days'] ?? null,
        'site_photos' => !empty($uploadedPhotos) ? json_encode($uploadedPhotos) : null
    ];
    
    // Create survey
    $surveyModel = new SiteSurvey();
    $result = $surveyModel->create($surveyData);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Site survey submitted successfully! It will be reviewed by the admin.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit survey']);
    }
    
} catch (Exception $e) {
    error_log('Survey submission error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while submitting the survey']);
}
?>
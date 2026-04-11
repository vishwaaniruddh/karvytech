<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Installation.php';
require_once __DIR__ . '/../../models/SiteSurvey.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Debug output
error_log("Processing installation delegation request");

try {
    // Log the request for debugging
    error_log("Installation delegation request: " . print_r($_POST, true));
    
    $installationModel = new Installation();
    $surveyModel = new SiteSurvey();
    $currentUser = Auth::getCurrentUser();
    
    // Validate required fields
    $requiredFields = ['survey_id', 'vendor_id'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    $surveyId = intval($_POST['survey_id']);
    $vendorId = intval($_POST['vendor_id']);
    
    // Get survey details
    $survey = $surveyModel->findWithDetails($surveyId);
    if (!$survey) {
        throw new Exception('Survey not found');
    }
    
    // Check if survey is approved
    if ($survey['survey_status'] !== 'approved') {
        throw new Exception('Only approved surveys can be delegated for installation');
    }
    
    // Check if already delegated
    if (($survey['installation_status'] ?? 'not_delegated') !== 'not_delegated') {
        throw new Exception('This survey has already been delegated for installation');
    }
    
    // Prepare installation delegation data
    $delegationData = [
        'survey_id' => $surveyId,
        'site_id' => $survey['site_id'],
        'vendor_id' => $vendorId,
        'delegated_by' => $currentUser['id'],
        'expected_start_date' => $_POST['expected_start_date'] ?? null,
        'expected_completion_date' => $_POST['expected_completion_date'] ?? null,
        'priority' => $_POST['priority'] ?? 'medium',
        'installation_type' => $_POST['installation_type'] ?? 'standard',
        'special_instructions' => $_POST['special_instructions'] ?? null,
        'notes' => $_POST['notes'] ?? null
    ];
    
    // Create installation delegation
    $installationId = $installationModel->createInstallationDelegation($delegationData);
    
    if (!$installationId) {
        throw new Exception('Failed to create installation delegation');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Installation delegated successfully',
        'installation_id' => $installationId
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
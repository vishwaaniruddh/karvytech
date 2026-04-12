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

try {
    $installationModel = new Installation();
    $surveyModel = new SiteSurvey();
    $currentUser = Auth::getCurrentUser();
    
    // Validate required fields
    $requiredFields = ['site_id', 'survey_id', 'vendor_id'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    $siteId = intval($_POST['site_id']);
    $surveyId = intval($_POST['survey_id']);
    $vendorId = intval($_POST['vendor_id']);
    
    // Get survey details to verify it's approved
    $survey = $surveyModel->findWithDetails($surveyId);
    if (!$survey || $survey['survey_status'] !== 'approved') {
        throw new Exception('A valid approved survey is required for installation delegation');
    }
    
    // Mark existing active delegations for this site as 'cancelled' or 'replaced' before creating a new one
    $db = Database::getInstance()->getConnection();
    $updateStmt = $db->prepare("UPDATE installation_delegations SET status = 'replaced', updated_at = NOW(), updated_by = ? WHERE site_id = ? AND status = 'assigned'");
    $updateStmt->execute([$currentUser['id'], $siteId]);
    
    // Create new delegation
    $delegationData = [
        'survey_id' => $surveyId,
        'site_id' => $siteId,
        'vendor_id' => $vendorId,
        'delegated_by' => $currentUser['id'],
        'expected_start_date' => $_POST['expected_start_date'] ?: null,
        'expected_completion_date' => $_POST['expected_completion_date'] ?: null,
        'priority' => $_POST['priority'] ?? 'medium',
        'notes' => $_POST['notes'] ?? ''
    ];
    
    $installationId = $installationModel->createInstallationDelegation($delegationData);
    
    if ($installationId) {
        echo json_encode(['success' => true, 'message' => 'Installation delegated successfully']);
    } else {
        throw new Exception('Failed to create installation delegation');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

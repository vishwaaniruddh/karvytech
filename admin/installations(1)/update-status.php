<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Installation.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $installationModel = new Installation();
    $currentUser = Auth::getCurrentUser();
    
    // Validate required fields
    $requiredFields = ['installation_id', 'status'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field '$field' is required");
        }
    }
    
    $installationId = intval($_POST['installation_id']);
    $status = $_POST['status'];
    $notes = $_POST['notes'] ?? null;
    
    // Validate status
    $validStatuses = ['assigned', 'acknowledged', 'in_progress', 'on_hold', 'completed', 'cancelled'];
    if (!in_array($status, $validStatuses)) {
        throw new Exception('Invalid status provided');
    }
    
    // Update installation status
    $result = $installationModel->updateInstallationStatus($installationId, $status, $currentUser['id'], $notes);
    
    if (!$result) {
        throw new Exception('Failed to update installation status');
    }
    
    // Update survey installation status if completed
    if ($status === 'completed') {
        // Get installation details to update survey and site
        $installation = $installationModel->getInstallationDetails($installationId);
        if ($installation) {
            // Update survey installation status
            $installationModel->updateSurveyInstallationStatus($installation['survey_id'], 'completed');
            
            // Update site installation status to sync both systems
            require_once __DIR__ . '/../../models/Site.php';
            $siteModel = new Site();
            $siteModel->updateInstallationStatus($installation['site_id'], true, date('Y-m-d H:i:s'));
        }
    } else {
        // If status is changed from completed to something else, update site status to pending
        $installation = $installationModel->getInstallationDetails($installationId);
        if ($installation) {
            require_once __DIR__ . '/../../models/Site.php';
            $siteModel = new Site();
            $siteModel->updateInstallationStatus($installation['site_id'], false, null);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Installation status updated successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
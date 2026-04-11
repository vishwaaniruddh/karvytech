<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/SiteSurvey.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$surveyId = $_POST['survey_id'] ?? null;
$action = $_POST['action'] ?? null;
$remarks = $_POST['remarks'] ?? null;

if (!$surveyId || !$action) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    $surveyModel = new SiteSurvey();
    $currentUser = Auth::getCurrentUser();
    
    $result = false;
    $message = '';
    
    if ($action === 'approve') {
        $result = $surveyModel->approve($surveyId, $currentUser['id'], $remarks);
        $message = 'Survey approved successfully';
    } elseif ($action === 'reject') {
        $result = $surveyModel->reject($surveyId, $currentUser['id'], $remarks);
        $message = 'Survey rejected successfully';
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update survey status']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
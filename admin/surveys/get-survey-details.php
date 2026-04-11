<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/SiteSurvey.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $surveyModel = new SiteSurvey();
    $surveyId = intval($_GET['id'] ?? 0);
    
    if (!$surveyId) {
        throw new Exception('Survey ID is required');
    }
    
    $survey = $surveyModel->findWithDetails($surveyId);
    
    if (!$survey) {
        throw new Exception('Survey not found');
    }
    
    echo json_encode([
        'success' => true,
        'survey' => $survey
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
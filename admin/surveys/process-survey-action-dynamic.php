<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$responseId = $_POST['response_id'] ?? null;
$action = $_POST['action'] ?? null;
$remarks = $_POST['remarks'] ?? null;

if (!$responseId || !$action) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $currentUser = Auth::getCurrentUser();
    
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    $message = ($action === 'approve') ? 'Survey response approved successfully' : 'Survey response rejected successfully';
    
    $sql = "UPDATE dynamic_survey_responses SET 
            survey_status = ?,
            approved_by = ?,
            approved_date = NOW(),
            approval_remarks = ?
            WHERE id = ?";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        $status,
        $currentUser['id'],
        $remarks,
        $responseId
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update survey status']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

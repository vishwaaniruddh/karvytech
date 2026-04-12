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
    
    // Begin transaction for safety
    $db->beginTransaction();

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
        // If approved, sync with sites table
        if ($action === 'approve') {
            // Get site_id from the response
            $stmt = $db->prepare("SELECT site_id FROM dynamic_survey_responses WHERE id = ?");
            $stmt->execute([$responseId]);
            $siteData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($siteData && $siteData['site_id']) {
                $stmt = $db->prepare("UPDATE sites SET survey_status = 1 WHERE id = ?");
                $stmt->execute([$siteData['site_id']]);
            }
        }
        
        $db->commit();
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to update survey status']);
    }
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

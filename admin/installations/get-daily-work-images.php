<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Installation.php';

// Require authentication (admin or user with access)
if (!Auth::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    if (!isset($_GET['installation_id'])) {
        throw new Exception('Installation ID is required');
    }
    
    $installationId = (int)$_GET['installation_id'];
    
    // Get all attachments for this installation
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT * FROM installation_progress_attachments 
        WHERE installation_id = ? 
        AND attachment_type IN ('daily_work_site', 'daily_work_material')
        ORDER BY uploaded_at ASC
    ");
    $stmt->execute([$installationId]);
    $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group by day number
    $groupedByDay = [];
    foreach ($attachments as $attachment) {
        // Extract day number from description
        if (preg_match('/Day (\d+)/', $attachment['description'], $matches)) {
            $dayNumber = (int)$matches[1];
            if (!isset($groupedByDay[$dayNumber])) {
                $groupedByDay[$dayNumber] = [];
            }
            $groupedByDay[$dayNumber][] = $attachment;
        }
    }
    
    echo json_encode([
        'success' => true,
        'attachments' => $groupedByDay,
        'total' => count($attachments)
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

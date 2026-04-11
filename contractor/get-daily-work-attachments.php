<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Installation.php';

// Require vendor authentication
Auth::requireVendor();

$vendorId = Auth::getVendorId();

header('Content-Type: application/json');

try {
    if (!isset($_GET['installation_id'])) {
        throw new Exception('Installation ID is required');
    }
    
    $installationId = (int)$_GET['installation_id'];
    
    $installationModel = new Installation();
    
    // Verify vendor access to this installation
    $installation = $installationModel->getInstallationDetails($installationId);
    if (!$installation || $installation['vendor_id'] != $vendorId) {
        throw new Exception('Access denied');
    }
    
    // Get attachments
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT * FROM installation_progress_attachments 
        WHERE installation_id = ? 
        AND attachment_type IN ('daily_work_site', 'daily_work_material')
        ORDER BY day_number ASC, uploaded_at ASC
    ");
    $stmt->execute([$installationId]);
    $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group by day number
    $groupedAttachments = [];
    foreach ($attachments as $attachment) {
        $dayNumber = (int)$attachment['day_number'];
        
        if (!isset($groupedAttachments[$dayNumber])) {
            $groupedAttachments[$dayNumber] = [
                'site' => [],
                'material' => []
            ];
        }
        
        $fileData = [
            'id' => $attachment['id'],
            'file_name' => $attachment['file_name'],
            'original_name' => $attachment['original_name'],
            'file_path' => $attachment['file_path'],
            'file_type' => $attachment['file_type'],
            'mime_type' => $attachment['mime_type'],
            'uploaded_at' => $attachment['uploaded_at']
        ];
        
        if ($attachment['attachment_type'] === 'daily_work_site') {
            $groupedAttachments[$dayNumber]['site'][] = $fileData;
        } else {
            // Extract material ID from description
            if (preg_match('/Material ID (\d+)/', $attachment['description'], $matMatches)) {
                $materialId = (int)$matMatches[1];
                if (!isset($groupedAttachments[$dayNumber]['material'][$materialId])) {
                    $groupedAttachments[$dayNumber]['material'][$materialId] = [];
                }
                $groupedAttachments[$dayNumber]['material'][$materialId][] = $fileData;
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'attachments' => $groupedAttachments
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

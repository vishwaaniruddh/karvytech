<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/auth.php';

// Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, "r");
    
    // Skip header
    fgetcsv($handle);
    
    $stats = [
        'created' => 0,
        'updated' => 0,
        'failed' => 0,
        'rows' => []
    ];
    
    $rowNum = 1;
    while (($data = fgetcsv($handle)) !== FALSE) {
        $rowNum++;
        if (empty($data[0])) continue;
        
        $name = trim($data[0]);
        $statusLabel = trim($data[1] ?? 'Active');
        $status = (strtolower($statusLabel) === 'active') ? 1 : 0;
        
        try {
            // Check if exists
            $stmt = $db->prepare("SELECT id FROM project_category WHERE category_name = ?");
            $stmt->execute([$name]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update
                $updateStmt = $db->prepare("UPDATE project_category SET status = ?, updated_at = NOW() WHERE id = ?");
                $updateStmt->execute([$status, $existing['id']]);
                
                $stats['updated']++;
                $stats['rows'][] = [
                    'row' => $rowNum,
                    'name' => $name,
                    'status_label' => $statusLabel,
                    'action' => 'update',
                    'message' => 'System configuration synchronized'
                ];
            } else {
                // Create
                $insertStmt = $db->prepare("INSERT INTO project_category (category_name, status, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                $insertStmt->execute([$name, $status]);
                
                $stats['created']++;
                $stats['rows'][] = [
                    'row' => $rowNum,
                    'name' => $name,
                    'status_label' => $statusLabel,
                    'action' => 'create',
                    'message' => 'New system architecture provisioned'
                ];
            }
        } catch (Exception $e) {
            $stats['failed']++;
            $stats['rows'][] = [
                'row' => $rowNum,
                'name' => $name,
                'status' => 'failed',
                'action' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    fclose($handle);
    echo json_encode(['success' => true] + $stats);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

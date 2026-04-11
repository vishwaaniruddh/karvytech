<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    $db = Database::getInstance()->getConnection();
    
    switch ($action) {
        case 'all':
            // Get all active permissions
            $stmt = $db->query("
                SELECT p.*, m.name as module_name, m.display_name as module_display_name
                FROM permissions p
                JOIN modules m ON p.module_id = m.id
                WHERE p.status = 'active' AND m.status = 'active'
                ORDER BY m.display_name, p.display_name
            ");
            $permissions = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'permissions' => $permissions
            ]);
            break;
            
        case 'by_module':
            $moduleId = $_GET['module_id'] ?? 0;
            
            $stmt = $db->prepare("
                SELECT p.*, m.name as module_name, m.display_name as module_display_name
                FROM permissions p
                JOIN modules m ON p.module_id = m.id
                WHERE p.module_id = ? AND p.status = 'active'
                ORDER BY p.display_name
            ");
            $stmt->execute([$moduleId]);
            $permissions = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'permissions' => $permissions
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
    
} catch (Exception $e) {
    error_log('Permissions API error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?>

<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$field = $_GET['field'] ?? '';

if (!$field) {
    echo json_encode([
        'success' => false,
        'message' => 'Field parameter is required'
    ]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $values = [];
    
    switch ($field) {
        case 'activity_status':
            $values = [
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'in_progress', 'label' => 'In Progress'],
                ['value' => 'completed', 'label' => 'Completed'],
                ['value' => 'on_hold', 'label' => 'On Hold'],
                ['value' => 'cancelled', 'label' => 'Cancelled']
            ];
            break;
            
        case 'customer_id':
        case 'customer':
            $stmt = $db->query("SELECT id as value, name as label FROM customers ORDER BY name");
            $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'bank_id':
        case 'bank':
            $stmt = $db->query("SELECT id as value, name as label FROM banks ORDER BY name");
            $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'vendor':
            $stmt = $db->query("SELECT DISTINCT vendor as value, vendor as label FROM sites WHERE vendor IS NOT NULL AND vendor != '' ORDER BY vendor");
            $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'delegated_vendor':
            $stmt = $db->query("SELECT id as value, CONCAT(name, ' (', company_name, ')') as label FROM vendors WHERE status = 'active' ORDER BY name");
            $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid field specified'
            ]);
            exit;
    }
    
    echo json_encode([
        'success' => true,
        'values' => $values
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load field values: ' . $e->getMessage()
    ]);
}
?>
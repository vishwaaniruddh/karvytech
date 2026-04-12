<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['sites']) || !isset($input['field']) || !isset($input['value'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters: sites, field, value'
    ]);
    exit;
}

$sites = $input['sites'];
$field = $input['field'];
$value = $input['value'];

// Validate field
$allowedFields = ['activity_status', 'delegated_vendor', 'customer_id', 'bank_id', 'vendor', 'customer', 'bank'];
if (!in_array($field, $allowedFields)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid field specified'
    ]);
    exit;
}

if (empty($sites)) {
    echo json_encode([
        'success' => false,
        'message' => 'No sites selected'
    ]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $currentUser = Auth::getCurrentUser();
    
    // Start transaction
    $db->beginTransaction();
    
    // Check if sites are provided as IDs or site_ids
    $isNumericIds = is_numeric($sites[0]);
    
    if ($isNumericIds) {
        // Sites provided as database IDs
        $placeholders = str_repeat('?,', count($sites) - 1) . '?';
        $sql = "UPDATE sites SET {$field} = ?, updated_at = NOW(), updated_by = ? WHERE id IN ({$placeholders})";
        $params = [$value, $currentUser['id']];
        $params = array_merge($params, $sites);
    } else {
        // Sites provided as site_ids (strings)
        $placeholders = str_repeat('?,', count($sites) - 1) . '?';
        $sql = "UPDATE sites SET {$field} = ?, updated_at = NOW(), updated_by = ? WHERE site_id IN ({$placeholders})";
        $params = [$value, $currentUser['id']];
        $params = array_merge($params, $sites);
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    $updatedCount = $stmt->rowCount();
    
    // Get the actual site IDs for logging
    if ($isNumericIds) {
        $logSiteIds = $sites;
    } else {
        // Get database IDs for the site_ids
        $placeholders = str_repeat('?,', count($sites) - 1) . '?';
        $logStmt = $db->prepare("SELECT id FROM sites WHERE site_id IN ({$placeholders})");
        $logStmt->execute($sites);
        $logSiteIds = $logStmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully updated {$updatedCount} sites",
        'updated_count' => $updatedCount
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update sites: ' . $e->getMessage()
    ]);
}
?>
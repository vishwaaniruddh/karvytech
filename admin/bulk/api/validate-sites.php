<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['site_ids']) || !isset($input['field']) || !isset($input['value'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters: site_ids, field, value'
    ]);
    exit;
}

$siteIds = $input['site_ids'];
$field = $input['field'];
$newValue = $input['value'];

// Validate field
$allowedFields = ['activity_status', 'delegated_vendor', 'customer_id', 'bank_id', 'vendor', 'customer', 'bank'];
if (!in_array($field, $allowedFields)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid field specified'
    ]);
    exit;
}

if (empty($siteIds)) {
    echo json_encode([
        'success' => false,
        'message' => 'No site IDs provided'
    ]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Create placeholders for IN clause
    $placeholders = str_repeat('?,', count($siteIds) - 1) . '?';
    
    // Get sites that exist and their current values
    $sql = "SELECT id, site_id, location, {$field} as current_value FROM sites WHERE site_id IN ({$placeholders})";
    $stmt = $db->prepare($sql);
    $stmt->execute($siteIds);
    $foundSites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Find sites that don't exist
    $foundSiteIds = array_column($foundSites, 'site_id');
    $notFoundSites = array_diff($siteIds, $foundSiteIds);
    
    // Separate sites that already have the target value
    $sitesToUpdate = [];
    $sitesAlreadySet = [];
    
    foreach ($foundSites as $site) {
        if ($site['current_value'] === $newValue) {
            $sitesAlreadySet[] = $site;
        } else {
            $sitesToUpdate[] = $site;
        }
    }
    
    echo json_encode([
        'success' => true,
        'validation' => [
            'sites_to_update' => $sitesToUpdate,
            'sites_already_set' => $sitesAlreadySet,
            'sites_not_found' => $notFoundSites,
            'total_requested' => count($siteIds),
            'total_found' => count($foundSites),
            'total_to_update' => count($sitesToUpdate),
            'field' => $field,
            'new_value' => $newValue
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Validation failed: ' . $e->getMessage()
    ]);
}
?>
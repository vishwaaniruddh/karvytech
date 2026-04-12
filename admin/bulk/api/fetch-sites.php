<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['site_ids'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameter: site_ids'
    ]);
    exit;
}

$siteIds = $input['site_ids'];
$method = $input['method'] ?? 'text'; // 'text' or 'dropdown'

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
    
    // Get sites that exist
    $sql = "SELECT s.id, s.site_id, s.location, s.city, s.state, 
                   COALESCE(cu.name, s.customer) as customer, 
                   s.activity_status, s.vendor,
                   v.name as vendor_name, v.company_name as vendor_company,
                   b.name as bank_name,
                   sd.delegation_date
            FROM sites s
            LEFT JOIN site_delegations sd ON s.id = sd.site_id AND sd.status = 'active'
            LEFT JOIN vendors v ON sd.vendor_id = v.id
            LEFT JOIN customers cu ON s.customer_id = cu.id
            LEFT JOIN banks b ON s.bank_id = b.id
            WHERE s.site_id IN ({$placeholders})
            ORDER BY s.site_id ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($siteIds);
    $foundSites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Find sites that don't exist
    $foundSiteIds = array_column($foundSites, 'site_id');
    $notFoundSites = array_diff($siteIds, $foundSiteIds);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'found_sites' => $foundSites,
            'not_found_sites' => array_values($notFoundSites),
            'total_requested' => count($siteIds),
            'total_found' => count($foundSites),
            'method' => $method
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch sites: ' . $e->getMessage()
    ]);
}
?>
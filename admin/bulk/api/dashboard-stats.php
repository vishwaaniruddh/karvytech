<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    
    // Get total sites
    $stmt = $db->query("SELECT COUNT(*) as count FROM sites WHERE deleted_at IS NULL");
    $totalSites = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get active users
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
    $activeUsers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get pending surveys (both legacy and dynamic)
    $stmt = $db->query("
        SELECT 
            (SELECT COUNT(*) FROM site_surveys WHERE survey_status = 'pending') +
            (SELECT COUNT(*) FROM dynamic_survey_responses WHERE survey_status = 'submitted') as count
    ");
    $pendingSurveys = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get material requests
    $stmt = $db->query("SELECT COUNT(*) as count FROM material_requests WHERE status IN ('pending', 'approved')");
    $materialRequests = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_sites' => (int)$totalSites,
            'active_users' => (int)$activeUsers,
            'pending_surveys' => (int)$pendingSurveys,
            'material_requests' => (int)$materialRequests
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load dashboard stats: ' . $e->getMessage()
    ]);
}
?>
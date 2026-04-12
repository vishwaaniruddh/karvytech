<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

if (strlen($query) < 2) {
    echo json_encode([
        'success' => false,
        'message' => 'Query must be at least 2 characters'
    ]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Search sites by site_id or location
    $searchTerm = "%{$query}%";
    $sql = "SELECT s.id, s.site_id, s.location, s.city, s.state, s.customer, s.activity_status
            FROM sites s
            WHERE (s.site_id LIKE ? OR s.location LIKE ?)
            ORDER BY 
                CASE 
                    WHEN s.site_id LIKE ? THEN 1 
                    ELSE 2 
                END,
                s.site_id ASC
            LIMIT ?";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, "{$query}%", $limit]);
    $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'sites' => $sites,
        'query' => $query,
        'count' => count($sites)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Search failed: ' . $e->getMessage()
    ]);
}
?>
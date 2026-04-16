<?php
require_once __DIR__ . '/../../../../config/auth.php';
require_once __DIR__ . '/../../../../models/Inventory.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$inventoryModel = new Inventory();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$siteId = $_GET['site_id'] ?? null;
$requestId = $_GET['request_id'] ?? null;

try {
    $result = $inventoryModel->getDispatches($page, $limit, $search, $status, $siteId, $requestId);
    
    // Calculate statistics based on filtered or total data
    // For simplicity, we can fetch total stats or calculate from result
    // But better to have a dedicated method or just return what we have
    
    // We also need stats for the header
    $db = Database::getInstance()->getConnection();
    
    // Total stats (unfiltered or filtered? usually header stats are global or filtered by current view)
    $statsStmt = $db->query("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN dispatch_status IN ('dispatched', 'in_transit') THEN 1 END) as in_transit,
            COUNT(CASE WHEN dispatch_status = 'delivered' THEN 1 END) as delivered,
            COUNT(CASE WHEN dispatch_status = 'prepared' THEN 1 END) as pending
        FROM inventory_dispatches
    ");
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $result['dispatches'],
        'pagination' => [
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit'],
            'pages' => $result['pages']
        ],
        'stats' => $stats
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'all'; // 'all' or 'available'

$offset = ($page - 1) * $limit;

try {
    $db = Database::getInstance()->getConnection();
    
    // Build search condition
    $searchCondition = '';
    $searchParams = [];
    if (!empty($search)) {
        $searchCondition = " AND (s.site_id LIKE ? OR s.location LIKE ?)";
        $searchParams = ["%{$search}%", "%{$search}%"];
    }
    
    if ($type === 'available') {
        // Get available sites for delegation (not already delegated or completed)
        $countSql = "SELECT COUNT(*) FROM sites s WHERE (s.delegated_vendor IS NULL OR s.delegated_vendor = '' OR s.activity_status IN ('pending', 'in_progress'))" . $searchCondition;
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($searchParams);
        $totalCount = $countStmt->fetchColumn();
        
        $sql = "SELECT s.id, s.site_id, s.location, s.activity_status, s.customer as customer_name
                FROM sites s
                WHERE (s.delegated_vendor IS NULL OR s.delegated_vendor = '' OR s.activity_status IN ('pending', 'in_progress'))" . $searchCondition . "
                ORDER BY s.site_id ASC
                LIMIT ? OFFSET ?";
        
        $params = array_merge($searchParams, [$limit, $offset]);
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'sites' => $sites,
            'pagination' => [
                'current_page' => $page,
                'total_count' => $totalCount,
                'per_page' => $limit,
                'has_more' => ($offset + $limit) < $totalCount
            ]
        ]);
        
    } else {
        // Get all sites (for bulk updates)
        $countSql = "SELECT COUNT(*) FROM sites s" . ($searchCondition ? " WHERE 1=1" . $searchCondition : "");
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($searchParams);
        $totalCount = $countStmt->fetchColumn();
        
        $sql = "SELECT s.id, s.site_id, s.location, s.activity_status, 
                       s.customer as customer_name, s.delegated_vendor, v.name as vendor_name
                FROM sites s
                LEFT JOIN vendors v ON s.delegated_vendor = v.id" . 
                ($searchCondition ? " WHERE 1=1" . $searchCondition : "") . "
                ORDER BY s.site_id ASC
                LIMIT ? OFFSET ?";
        
        $params = array_merge($searchParams, [$limit, $offset]);
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'sites' => $sites,
            'pagination' => [
                'current_page' => $page,
                'total_count' => $totalCount,
                'per_page' => $limit,
                'has_more' => ($offset + $limit) < $totalCount
            ]
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load sites: ' . $e->getMessage()
    ]);
}
?>
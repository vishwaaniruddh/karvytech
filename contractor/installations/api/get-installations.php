<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

try {
    Auth::requireVendor();
    $vendorId = Auth::getVendorId();
    
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    
    $db = Database::getInstance()->getConnection();
    $offset = ($page - 1) * $limit;
    
    $sql = "SELECT id.*, s.site_id as site_code, s.location,
                   ct.name as city_name, st.name as state_name,
                   COALESCE(
                       (SELECT MAX(progress_percentage) 
                        FROM installation_progress ip 
                        WHERE ip.installation_id = id.id), 0
                   ) as progress_percentage
            FROM installation_delegations id
            INNER JOIN sites s ON id.site_id = s.id
            LEFT JOIN cities ct ON s.city_id = ct.id
            LEFT JOIN states st ON s.state_id = st.id
            WHERE id.vendor_id = :vendor_id";
    
    $params = [':vendor_id' => $vendorId];
    
    if ($status !== 'all') {
        $sql .= " AND id.status = :status";
        $params[':status'] = $status;
    }
    
    if (!empty($search)) {
        $sql .= " AND (s.site_id LIKE :search OR s.location LIKE :search OR ct.name LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    // Count total
    $countSql = "SELECT COUNT(*) FROM ($sql) as t";
    $countStmt = $db->prepare($countSql);
    foreach($params as $key => $val) $countStmt->bindValue($key, $val);
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
    
    // Get rows
    $sql .= " ORDER BY id.delegation_date DESC LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($sql);
    foreach($params as $key => $val) $stmt->bindValue($key, $val);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $rows,
        'pagination' => [
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

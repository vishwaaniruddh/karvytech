<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../models/Site.php';

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
    
    $conditions = ["sd.vendor_id = ?"];
    $params = [$vendorId];
    
    if ($status !== 'all') {
        $conditions[] = "sd.status = ?";
        $params[] = $status;
    }
    
    if (!empty($search)) {
        $conditions[] = "(s.site_id LIKE ? OR s.location LIKE ? OR cu.name LIKE ? OR ct.name LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    }
    
    $whereSql = "WHERE " . implode(" AND ", $conditions);
    
    // Count total
    $countSql = "SELECT COUNT(*) FROM site_delegations sd 
                 INNER JOIN sites s ON sd.site_id = s.id 
                 LEFT JOIN customers cu ON s.customer_id = cu.id
                 LEFT JOIN cities ct ON s.city_id = ct.id
                 $whereSql";
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();
    
    // Get rows
    $sql = "SELECT sd.id as delegation_id, sd.delegation_date, sd.status as delegation_status,
                   s.id as site_id, s.site_id as site_code, s.location, s.branch,
                   cu.name as customer_name,
                   ct.name as city_name, st.name as state_name,
                   COALESCE(ss.survey_status, dsr.survey_status, 'pending') as survey_status,
                   COALESCE(ss.submitted_date, dsr.submitted_date) as survey_date,
                   ins.status as installation_status,
                   ins.id as installation_id,
                   COALESCE(ss.id, dsr.id) as survey_id,
                   IF(dsr.id IS NOT NULL, 'dynamic', 'legacy') as survey_source
            FROM site_delegations sd
            INNER JOIN sites s ON sd.site_id = s.id
            LEFT JOIN customers cu ON s.customer_id = cu.id
            LEFT JOIN cities ct ON s.city_id = ct.id
            LEFT JOIN states st ON s.state_id = st.id
            LEFT JOIN site_surveys ss ON sd.id = ss.delegation_id
            LEFT JOIN (
                SELECT id, delegation_id, site_id, survey_status, submitted_date 
                FROM dynamic_survey_responses 
                WHERE id IN (SELECT MAX(id) FROM dynamic_survey_responses GROUP BY site_id)
            ) dsr ON (sd.id = dsr.delegation_id OR s.id = dsr.site_id)
            LEFT JOIN installation_delegations ins ON s.id = ins.site_id AND ins.vendor_id = sd.vendor_id
            $whereSql
            ORDER BY sd.delegation_date DESC
            LIMIT $limit OFFSET $offset";
            
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
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

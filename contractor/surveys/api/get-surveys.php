<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json');

try {
    Auth::requireVendor();
    $vendorId = Auth::getVendorId();
    $userId = $_SESSION['user_id'];
    
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    
    $db = Database::getInstance()->getConnection();
    $offset = ($page - 1) * $limit;
    
    // We combine dynamic and legacy surveys
    // Part 1: Dynamic Surveys (Unified Site & Survey Info)
    $dynamicSql = "
        SELECT dsr.id, dsr.site_id as site_db_id, dsr.survey_status, 
               dsr.submitted_date, s.site_id as site_code, s.location,
               ds.title as survey_title, 'dynamic' as source
        FROM dynamic_survey_responses dsr
        INNER JOIN sites s ON dsr.site_id = s.id
        LEFT JOIN dynamic_surveys ds ON dsr.survey_form_id = ds.id
        WHERE dsr.surveyor_id = :user_id
    ";
    
    // Part 2: Legacy Surveys
    $legacySql = "
        SELECT ss.id, ss.site_id as site_db_id, ss.survey_status,
               ss.created_at as submitted_date, s.site_id as site_code, s.location,
               'Legacy Survey' as survey_title, 'legacy' as source
        FROM site_surveys ss
        INNER JOIN sites s ON ss.site_id = s.id
        WHERE ss.vendor_id = :vendor_id
    ";
    
    // Combine with UNION
    $combinedSql = "($dynamicSql) UNION ALL ($legacySql)";
    
    // Wrap for filtering and pagination
    $finalSql = "SELECT * FROM ($combinedSql) AS all_surveys";
    $conditions = [];
    $params = [':user_id' => $userId, ':vendor_id' => $vendorId];
    
    if ($status !== 'all') {
        $conditions[] = "survey_status = :status";
        $params[':status'] = $status;
    }
    
    if (!empty($search)) {
        $conditions[] = "(site_code LIKE :search OR location LIKE :search OR survey_title LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($conditions)) {
        $finalSql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    // Count total
    $countSql = "SELECT COUNT(*) FROM ($finalSql) as t";
    $countStmt = $db->prepare($countSql);
    foreach($params as $key => $val) $countStmt->bindValue($key, $val);
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
    
    // Get rows
    $finalSql .= " ORDER BY submitted_date DESC LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($finalSql);
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
        ],
        'stats' => [
            'total' => (int)$total,
            'pending' => 0, // Could add more complex stats here if needed
            'approved' => 0
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../models/Site.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$search = $_GET['search'] ?? '';
$filters = [
    'city' => $_GET['city'] ?? '',
    'state' => $_GET['state'] ?? '',
    'activity_status' => $_GET['activity_status'] ?? '',
    'survey_status' => $_GET['survey_status'] ?? ''
];

try {
    $siteModel = new Site();
    $db = Database::getInstance()->getConnection();
    
    // Use the existing model for base pagination and filtering
    $result = $siteModel->getAllWithPagination($page, $limit, $search, $filters);
    
    // Enhance sites with extra data for the advanced dashboard
    $sites = $result['sites'];
    
    foreach ($sites as &$site) {
        // 1. Get Installation Vendor específicamente
        $instStmt = $db->prepare("
            SELECT v.name as vendor_name 
            FROM installation_delegations id 
            JOIN vendors v ON id.vendor_id = v.id 
            WHERE id.site_id = ? 
            LIMIT 1
        ");
        $instStmt->execute([$site['id']]);
        $instData = $instStmt->fetch(PDO::FETCH_ASSOC);
        $site['installation_vendor_name'] = $instData ? $instData['vendor_name'] : null;
        
        // 2. Get Material Request & Dispatch Info
        $matStmt = $db->prepare("
            SELECT mr.status as request_status, mr.id as request_id, 
                   md.id as dispatch_id, md.acknowledgment_status as delivery_status,
                   md.dispatch_date, md.tracking_number
            FROM material_requests mr
            LEFT JOIN material_dispatches md ON mr.id = md.material_request_id
            WHERE mr.site_id = ? 
            ORDER BY mr.id DESC, md.id DESC LIMIT 1
        ");
        $matStmt->execute([$site['id']]);
        $matData = $matStmt->fetch(PDO::FETCH_ASSOC);
        
        $site['material_request_id'] = $matData ? $matData['request_id'] : null;
        $site['material_request_number'] = $matData ? 'REQ-' . str_pad($matData['request_id'], 6, '0', STR_PAD_LEFT) : '-';
        $site['material_status'] = $matData ? $matData['request_status'] : 'Pending';
        $site['dispatch_status'] = $matData && $matData['dispatch_id'] ? 'Dispatched' : 'Pending';
        $site['delivery_status'] = $matData && $matData['dispatch_id'] ? (ucfirst($matData['delivery_status'] ?? 'In-Transit')) : '-';
        
        // 3. Get Installation Person/Time & Status
        $instLogStmt = $db->prepare("
            SELECT u.username, id.updated_at, id.status as inst_status, id.id as delegation_id
            FROM installation_delegations id 
            LEFT JOIN users u ON id.updated_by = u.id 
            WHERE id.site_id = ? 
            ORDER BY id.id DESC LIMIT 1
        ");
        $instLogStmt->execute([$site['id']]);
        $instLog = $instLogStmt->fetch(PDO::FETCH_ASSOC);
        $site['installer_name'] = $instLog ? $instLog['username'] : '-';
        $site['installation_completed_time'] = ($instLog && $instLog['inst_status'] === 'completed') ? $instLog['updated_at'] : null;
        $site['installation_status_label'] = $instLog ? ucfirst($instLog['inst_status']) : 'Pending';
    }
    
    echo json_encode([
        'success' => true,
        'sites' => $sites,
        'pagination' => [
            'total_records' => $result['total'],
            'total_pages' => $result['pages'],
            'current_page' => $result['page'],
            'limit' => $result['limit']
        ],
        'stats' => $siteModel->getOverallStatistics($search, $filters)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch sites: ' . $e->getMessage()
    ]);
}

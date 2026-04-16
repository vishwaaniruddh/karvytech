<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../models/MaterialRequest.php';

Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$requestId = $_GET['request_id'] ?? null;
$siteId = $_GET['site_id'] ?? null;

if (!$requestId && !$siteId) {
    echo json_encode(['success' => false, 'message' => 'Request ID or Site ID is required']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    if ($requestId) {
        $stmt = $db->prepare("SELECT items, id, site_id FROM material_requests WHERE id = ?");
        $stmt->execute([$requestId]);
    } else {
        $stmt = $db->prepare("SELECT items, id, site_id FROM material_requests WHERE site_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$siteId]);
    }
    
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Material request not found']);
        exit;
    }
    
    $items = json_decode($request['items'], true);
    
    // If item names are missing, fetch them from boq_items table
    if (!empty($items) && is_array($items)) {
        $boqItemIds = array_filter(array_column($items, 'boq_item_id'));
        if (!empty($boqItemIds)) {
            $placeholders = implode(',', array_fill(0, count($boqItemIds), '?'));
            $stmt = $db->prepare("SELECT id, item_name FROM boq_items WHERE id IN ($placeholders)");
            $stmt->execute(array_values($boqItemIds));
            $itemNames = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            foreach ($items as &$item) {
                if (empty($item['material_name']) && empty($item['item_name'])) {
                    $item['item_name'] = $itemNames[$item['boq_item_id']] ?? 'Unknown Item';
                }
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'request_id' => $request['id'],
        'items' => $items ?: []
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

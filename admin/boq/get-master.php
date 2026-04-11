<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/BoqMaster.php';
require_once __DIR__ . '/../../models/BoqMasterItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing ID']);
    exit;
}

try {
    $boqMaster = new BoqMaster();
    $boqItemLink = new BoqMasterItem();
    
    $boq = $boqMaster->find($id);
    
    if ($boq) {
        $items = $boqItemLink->getItemsByMasterId($id);
        $itemIds = array_map(function($item) {
            return (int)$item['boq_item_id'];
        }, $items);
        
        echo json_encode([
            'success' => true, 
            'boq' => $boq,
            'item_ids' => $itemIds,
            'item_details' => $items
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'BOQ Set not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

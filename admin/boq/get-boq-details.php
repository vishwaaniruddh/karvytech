<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/BoqMaster.php';
require_once __DIR__ . '/../../models/BoqMasterItem.php';

// Require admin/vendor authentication (vendors might need this too if they create requests)
Auth::requirePermission('materials', 'create');

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing BOQ ID']);
    exit;
}

try {
    $boqMaster = new BoqMaster();
    $boqItemLink = new BoqMasterItem();
    
    $boq = $boqMaster->find($id);
    
    if ($boq) {
        $items = $boqItemLink->getItemsByMasterId($id);
        
        echo json_encode([
            'success' => true, 
            'boq' => [
                'id' => $boq['id'],
                'name' => $boq['boq_name']
            ],
            'items' => $items
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'BOQ Set not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

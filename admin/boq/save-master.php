<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/BoqMaster.php';
require_once __DIR__ . '/../../models/BoqMasterItem.php';
require_once __DIR__ . '/../../models/AuditLog.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);
$currentUser = Auth::getCurrentUser();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $boqMaster = new BoqMaster();
    $boqItemLink = new BoqMasterItem();
    $auditLog = new AuditLog();
    
    $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $boq_name = trim($_POST['boq_name'] ?? '');
    $customer_id = !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;
    $status = $_POST['status'] ?? 'active';
    $materials = $_POST['materials'] ?? []; // Array of material IDs
    
    if (empty($boq_name) || empty($customer_id)) {
        echo json_encode(['success' => false, 'message' => 'BOQ Name and Customer are required']);
        exit;
    }
    
    $data = [
        'boq_name' => $boq_name,
        'customer_id' => $customer_id,
        'status' => $status
    ];
    
    if ($id) {
        // Update
        $boqMaster->update($id, $data);
        $masterId = $id;
        
        // Sync items: delete existing and re-add
        $boqItemLink->deleteByMasterId($masterId);
        
        $actionType = 'update_boq';
    } else {
        // Create
        $masterId = $boqMaster->create($data);
        $actionType = 'create_boq';
    }
    
    if ($masterId) {
        // Add materials
        if (!empty($materials)) {
            $boqItemLink->addItems($masterId, $materials);
        }
        
        // Log activity
        $auditLog->log([
            'user_id' => $currentUser['id'],
            'username' => $currentUser['username'],
            'user_role' => $currentUser['role'],
            'action_type' => $actionType,
            'endpoint' => $_SERVER['REQUEST_URI'],
            'request_data' => json_encode([
                'id' => $masterId,
                'boq_name' => $boq_name,
                'item_count' => count($materials)
            ]),
            'status_code' => 200
        ]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'BOQ Set ' . ($id ? 'updated' : 'created') . ' successfully',
            'id' => $masterId
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save BOQ Set']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

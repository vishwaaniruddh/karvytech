<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Inventory.php';

// Secure endpoint
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$inventoryModel = new Inventory();

try {
    switch ($action) {
        case 'get_stats':
            $stats = $inventoryModel->getInventoryStats();
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        case 'get_overview':
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $search = $_GET['search'] ?? '';
            $category = $_GET['category'] ?? '';
            $lowStock = isset($_GET['low_stock']) && $_GET['low_stock'] === '1';
            $warehouseId = $_GET['warehouse_id'] ?? '';
            
            $data = $inventoryModel->getStockOverview($search, $category, $lowStock, $warehouseId, $page, $limit);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'get_stock_entries':
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $search = $_GET['search'] ?? '';
            $boqItemId = $_GET['boq_item_id'] ?? null;
            $location = $_GET['location'] ?? '';
            
            $data = $inventoryModel->getIndividualStockEntries($boqItemId, $search, $location, $page, $limit);
            echo json_encode(['success' => true, 'data' => $data]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

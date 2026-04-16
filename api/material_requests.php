<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/MaterialRequest.php';
require_once __DIR__ . '/../models/BoqItem.php';
require_once __DIR__ . '/../models/Inventory.php';
require_once __DIR__ . '/../models/Courier.php';

// Secure endpoint
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$materialRequestModel = new MaterialRequest();
$boqModel = new BoqItem();
$inventoryModel = new Inventory();
$courierModel = new Courier();

try {
    switch ($action) {
        case 'get_dispatch_data':
            $requestId = $_GET['request_id'] ?? null;
            if (!$requestId) {
                throw new Exception('Missing material request ID');
            }
            
            // 1. Get request details
            $request = $materialRequestModel->findWithDetails($requestId);
            if (!$request) {
                throw new Exception('Material request not found');
            }
            
            // 2. Parse items and fetch details + stock
            $requestedItems = json_decode($request['items'], true) ?: [];
            $stockAvailability = $inventoryModel->checkStockAvailabilityForItems($requestedItems);
            
            $processedItems = [];
            foreach ($requestedItems as $index => $item) {
                // Normalize ID
                $boqItemId = $item['boq_item_id'] ?? $item['material_id'] ?? null;
                
                // Fetch BOQ details
                $boqItem = null;
                if ($boqItemId) {
                    $boqItem = $boqModel->find($boqItemId);
                }
                
                // Fetch stock info
                $stockInfo = $boqItemId ? ($stockAvailability[$boqItemId] ?? null) : null;
                
                $processedItems[] = [
                    'original' => $item,
                    'boq_item_id' => $boqItemId,
                    'item_name' => $boqItem['item_name'] ?? $item['material_name'] ?? 'Custom Material',
                    'item_code' => $boqItem['item_code'] ?? $item['item_code'] ?? 'N/A',
                    'unit' => $boqItem['unit'] ?? $item['unit'] ?? 'units',
                    'icon_class' => $boqItem['icon_class'] ?? 'fas fa-box',
                    'need_serial_number' => (isset($boqItem['need_serial_number']) && $boqItem['need_serial_number'] == 1),
                    'stock' => $stockInfo,
                    'is_out_of_stock' => $stockInfo && !$stockInfo['is_sufficient']
                ];
            }
            
            // 3. Get active couriers
            $couriers = $courierModel->getActiveCouriers();
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'request' => $request,
                    'items' => $processedItems,
                    'couriers' => $couriers,
                    'has_stock_issues' => array_any($processedItems, fn($i) => $i['is_out_of_stock'])
                ]
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * polyfill for array_any if PHP < 8.4
 */
if (!function_exists('array_any')) {
    function array_any($array, $callback) {
        foreach ($array as $element) {
            if ($callback($element)) {
                return true;
            }
        }
        return false;
    }
}

<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Warehouse.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$warehouseModel = new Warehouse();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get single warehouse
                $warehouse = $warehouseModel->getById($_GET['id']);
                if ($warehouse) {
                    echo json_encode([
                        'success' => true,
                        'data' => $warehouse
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Warehouse not found'
                    ]);
                }
            } else {
                // Get all warehouses
                $search = $_GET['search'] ?? '';
                $status = $_GET['status'] ?? '';
                $warehouses = $warehouseModel->getAll($search, $status);
                echo json_encode([
                    'success' => true,
                    'data' => $warehouses
                ]);
            }
            break;
            
        case 'POST':
            $data = $_POST;
            
            // Validate required fields
            $required = ['warehouse_code', 'name', 'address', 'city', 'state', 'pincode', 'contact_person', 'contact_phone'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
                    ]);
                    exit;
                }
            }
            
            if (!empty($data['id'])) {
                // Update existing warehouse
                $result = $warehouseModel->update($data['id'], $data);
            } else {
                // Create new warehouse
                $result = $warehouseModel->create($data);
            }
            
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            break;
            
        case 'DELETE':
            if (isset($_GET['id'])) {
                $result = $warehouseModel->delete($_GET['id']);
                if ($result['success']) {
                    echo json_encode($result);
                } else {
                    http_response_code(400);
                    echo json_encode($result);
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Warehouse ID is required'
                ]);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

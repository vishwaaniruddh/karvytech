<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/BoqItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';
$boqModel = new BoqItem();

try {
    switch ($action) {
        case 'list':
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 10);
            $search = $_GET['search'] ?? '';
            $category = $_GET['category'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $result = $boqModel->getAllWithPagination($page, $limit, $search, $category, $status);
            $stats = $boqModel->getStats();
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'items' => $result['items'],
                    'pagination' => [
                        'current_page' => $result['page'],
                        'total_pages' => $result['pages'],
                        'total_records' => $result['total'],
                        'limit' => $result['limit']
                    ],
                    'stats' => $stats
                ]
            ]);
            break;

        case 'view':
            $id = (int)$_GET['id'];
            $item = $boqModel->find($id);
            if ($item) {
                echo json_encode(['success' => true, 'item' => $item]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Item not found']);
            }
            break;

        case 'create':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $data = $_POST;
            $data['need_serial_number'] = isset($_POST['need_serial_number']);
            
            $errors = $boqModel->validateItemData($data);
            if (!empty($errors)) {
                echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
                break;
            }
            
            $id = $boqModel->create($data);
            if ($id) {
                echo json_encode(['success' => true, 'message' => 'Item created successfully', 'id' => $id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create item']);
            }
            break;

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $id = (int)$_GET['id'];
            $data = $_POST;
            $data['need_serial_number'] = isset($_POST['need_serial_number']);
            
            $errors = $boqModel->validateItemData($data, true, $id);
            if (!empty($errors)) {
                echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
                break;
            }
            
            if ($boqModel->update($id, $data)) {
                echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update item']);
            }
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $id = (int)$_GET['id'];
            if ($boqModel->delete($id)) {
                echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete item']);
            }
            break;

        case 'toggle-status':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $id = (int)$_GET['id'];
            $item = $boqModel->find($id);
            if (!$item) {
                throw new Exception('Item not found');
            }
            
            $newStatus = ($item['status'] === 'active') ? 'inactive' : 'active';
            if ($boqModel->updateStatus($id, $newStatus)) {
                echo json_encode(['success' => true, 'message' => "Item status updated to $newStatus"]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update status']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

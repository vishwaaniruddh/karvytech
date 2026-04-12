<?php
require_once __DIR__ . '/../controllers/UsersController.php';

$controller = new UsersController();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

header('Content-Type: application/json');

try {
    switch ($method) {
        case 'GET':
            if ($action === 'view' && $id) {
                $controller->show($id);
            } elseif ($action === 'edit' && $id) {
                $controller->edit($id);
            } else {
                // Return users list and stats
                $data = $controller->index();
                echo json_encode([
                    'success' => true,
                    'data' => $data
                ]);
            }
            break;

        case 'POST':
            if ($action === 'create') {
                $controller->store();
            } elseif ($action === 'update' && $id) {
                $controller->update($id);
            } elseif ($action === 'toggle-status' && $id) {
                $controller->toggleStatus($id);
            } elseif ($action === 'delete' && $id) {
                $controller->delete($id);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()]);
}
?>

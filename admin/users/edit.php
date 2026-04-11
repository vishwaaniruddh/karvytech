<?php
require_once __DIR__ . '/../../controllers/UsersController.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

header('Content-Type: application/json');

$controller = new UsersController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->update($id);
    echo json_encode($result);
} else {
    $result = $controller->edit($id);
    echo json_encode($result);
}
?>
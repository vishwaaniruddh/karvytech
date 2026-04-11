<?php
require_once __DIR__ . '/../../../controllers/CountriesController.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid country ID']);
    exit;
}

$controller = new CountriesController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $result = $controller->update($id);
    echo json_encode($result);
} else {
    header('Content-Type: application/json');
    $result = $controller->edit($id);
    
    if (isset($result['error'])) {
        echo json_encode(['success' => false, 'message' => $result['error']]);
    } else {
        echo json_encode(['success' => true, 'record' => $result['record']]);
    }
}
?>
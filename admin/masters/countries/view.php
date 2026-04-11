<?php
require_once __DIR__ . '/../../../controllers/CountriesController.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid country ID']);
    exit;
}

header('Content-Type: application/json');

$controller = new CountriesController();
$result = $controller->show($id);

echo json_encode($result);
?>
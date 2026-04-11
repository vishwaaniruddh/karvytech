<?php
require_once __DIR__ . '/../../../controllers/CouriersController.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Courier ID is required']);
    exit;
}

$controller = new CouriersController();
$result = $controller->update($id);

echo json_encode($result);
?>

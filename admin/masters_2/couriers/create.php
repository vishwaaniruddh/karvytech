<?php
require_once __DIR__ . '/../../../controllers/CouriersController.php';

header('Content-Type: application/json');

$controller = new CouriersController();
$result = $controller->create();

echo json_encode($result);
?>

<?php
require_once __DIR__ . '/../../controllers/UsersController.php';

header('Content-Type: application/json');

$controller = new UsersController();
$result = $controller->create();

echo json_encode($result);
?>
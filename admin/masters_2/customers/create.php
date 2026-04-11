<?php
require_once __DIR__ . '/../../../controllers/CustomersController.php';

header('Content-Type: application/json');

$controller = new CustomersController();
$result = $controller->create();

echo json_encode($result);
?>
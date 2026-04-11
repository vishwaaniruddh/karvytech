<?php
require_once __DIR__ . '/../../../controllers/BanksController.php';

header('Content-Type: application/json');

$controller = new BanksController();
$result = $controller->create();

echo json_encode($result);
?>
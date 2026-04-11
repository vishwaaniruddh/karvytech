<?php
require_once __DIR__ . '/../../controllers/SitesController.php';

header('Content-Type: application/json');

$controller = new SitesController();
$result = $controller->create();

echo json_encode($result);
?>
<?php
require_once __DIR__ . '/../../../controllers/CountriesController.php';

header('Content-Type: application/json');

$controller = new CountriesController();
$result = $controller->create();

echo json_encode($result);
?>
<?php
require_once __DIR__ . '/../../../controllers/CitiesController.php';

$controller = new CitiesController();
$id = $_GET['id'] ?? 0;
$controller->edit($id);
?>
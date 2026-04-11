<?php
require_once __DIR__ . '/../../../controllers/StatesController.php';

$controller = new StatesController();
$id = $_GET['id'] ?? 0;
$controller->view($id);
?>
<?php
require_once __DIR__ . '/../../../controllers/ZonesController.php';

$controller = new ZonesController();
$id = $_GET['id'] ?? 0;
$controller->view($id);
?>
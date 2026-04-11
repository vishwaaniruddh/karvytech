<?php
require_once __DIR__ . '/../../../controllers/BoqMasterController.php';

$controller = new BoqMasterController();
$id = $_GET['id'] ?? 0;
$controller->edit($id);
?>
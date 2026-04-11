<?php
require_once __DIR__ . '/../controllers/ApiController.php';

$controller = new ApiController();

// Get the action from the URL
$action = $_GET['action'] ?? '';
$stateId = $_GET['state_id'] ?? 0;

switch ($action) {
    case 'getByState':
        $controller->getCitiesByState($stateId);
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?>
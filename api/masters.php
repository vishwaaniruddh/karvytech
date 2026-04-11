<?php
require_once __DIR__ . '/../controllers/ApiController.php';

$controller = new ApiController();

// Get the request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['path'] ?? '';
$pathParts = explode('/', trim($path, '/'));

// Extract master type and ID
$type = $pathParts[0] ?? '';
$id = $pathParts[1] ?? null;
$action = $pathParts[2] ?? '';

// Validate master type
$validTypes = ['zones', 'countries', 'states', 'cities', 'banks', 'customers', 'boq', 'vendors', 'survey', 'installation', 'couriers'];
if (!in_array($type, $validTypes)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid master type']);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            if ($id && $action === 'toggle-status') {
                // Toggle status endpoint
                $controller->toggleStatus($type, $id);
            } elseif ($id) {
                // Show single record
                $controller->show($type, $id);
            } else {
                // List records with pagination
                $controller->index($type);
            }
            break;
            
        case 'POST':
            if ($id === 'bulk-upload') {
                // Bulk upload endpoint
                $controller->bulkStore($type);
            } elseif ($id && $action === 'toggle-status') {
                // Toggle status endpoint
                $controller->toggleStatus($type, $id);
            } elseif ($id) {
                // Update record
                $controller->update($type, $id);
            } else {
                // Create new record
                $controller->store($type);
            }
            break;
            
        case 'PUT':
            if ($id) {
                // Update record
                $controller->update($type, $id);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'ID required for update']);
            }
            break;
            
        case 'DELETE':
            if ($id) {
                // Delete record
                $controller->destroy($type, $id);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'ID required for delete']);
            }
            break;
            
        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    error_log("API error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../includes/error_handler.php';
require_once __DIR__ . '/../includes/logger.php';

class BaseMasterController extends BaseController {
    protected $model;
    protected $modelName;
    protected $tableName;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        
        $result = $this->model->getAllWithPagination($page, 20, $search, $status);
        
        return [
            'records' => $result['records'],
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['pages'],
                'total_records' => $result['total'],
                'limit' => $result['limit']
            ],
            'search' => $search,
            'status_filter' => $status
        ];
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->store();
        }
        
        return ['record' => null];
    }
    
    public function store() {
        try {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'status' => $_POST['status'] ?? 'active'
            ];
            
            // Add additional fields for specific models
            $data = $this->addModelSpecificFields($data);
            
            // Validate data
            $errors = $this->validateData($data);
            
            if (!empty($errors)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], 400);
            }
            
            // Create record
            $recordId = $this->model->create($data);
            
            if ($recordId) {
                // Log the action
                ErrorHandler::logUserAction("CREATE_{$this->tableName}", $this->tableName, $recordId, null, $data);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => "{$this->modelName} created successfully",
                    'record_id' => $recordId
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => "Failed to create {$this->modelName}"
                ], 500);
            }
            
        } catch (Exception $e) {
            Logger::error("{$this->modelName} creation failed", ['error' => $e->getMessage()]);
            return $this->jsonResponse([
                'success' => false,
                'message' => "An error occurred while creating the {$this->modelName}"
            ], 500);
        }
    }
    
    public function show($id) {
        $record = $this->model->find($id);
        
        if (!$record) {
            return $this->jsonResponse([
                'success' => false,
                'message' => "{$this->modelName} not found"
            ], 404);
        }
        
        return $this->jsonResponse([
            'success' => true,
            'record' => $record
        ]);
    }
    
    public function edit($id) {
        $record = $this->model->find($id);
        
        if (!$record) {
            return ['error' => "{$this->modelName} not found"];
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->update($id);
        }
        
        return ['record' => $record];
    }
    
    public function update($id) {
        try {
            $record = $this->model->find($id);
            
            if (!$record) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => "{$this->modelName} not found"
                ], 404);
            }
            
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'status' => $_POST['status'] ?? 'active'
            ];
            
            // Add additional fields for specific models
            $data = $this->addModelSpecificFields($data);
            
            // Validate data
            $errors = $this->validateData($data, true, $id);
            
            if (!empty($errors)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], 400);
            }
            
            // Update record
            $success = $this->model->update($id, $data);
            
            if ($success) {
                // Log the action
                ErrorHandler::logUserAction("UPDATE_{$this->tableName}", $this->tableName, $id, $record, $data);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => "{$this->modelName} updated successfully"
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => "Failed to update {$this->modelName}"
                ], 500);
            }
            
        } catch (Exception $e) {
            Logger::error("{$this->modelName} update failed", ['error' => $e->getMessage(), 'record_id' => $id]);
            return $this->jsonResponse([
                'success' => false,
                'message' => "An error occurred while updating the {$this->modelName}"
            ], 500);
        }
    }
    
    public function delete($id) {
        try {
            $record = $this->model->find($id);
            
            if (!$record) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => "{$this->modelName} not found"
                ], 404);
            }
            
            // Check for dependencies before deletion
            $canDelete = $this->checkDependencies($id);
            if (!$canDelete['allowed']) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => $canDelete['message']
                ], 400);
            }
            
            // Delete record
            $success = $this->model->delete($id);
            
            if ($success) {
                // Log the action
                ErrorHandler::logUserAction("DELETE_{$this->tableName}", $this->tableName, $id, $record, null);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => "{$this->modelName} deleted successfully"
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => "Failed to delete {$this->modelName}"
                ], 500);
            }
            
        } catch (Exception $e) {
            Logger::error("{$this->modelName} deletion failed", ['error' => $e->getMessage(), 'record_id' => $id]);
            return $this->jsonResponse([
                'success' => false,
                'message' => "An error occurred while deleting the {$this->modelName}"
            ], 500);
        }
    }
    
    public function toggleStatus($id) {
        try {
            $record = $this->model->find($id);
            
            if (!$record) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => "{$this->modelName} not found"
                ], 404);
            }
            
            $success = $this->model->toggleStatus($id);
            
            if ($success) {
                $newStatus = $record['status'] === 'active' ? 'inactive' : 'active';
                
                // Log the action
                ErrorHandler::logUserAction("TOGGLE_{$this->tableName}_STATUS", $this->tableName, $id, 
                    ['status' => $record['status']], 
                    ['status' => $newStatus]
                );
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => "{$this->modelName} status updated successfully",
                    'new_status' => $newStatus
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => "Failed to update {$this->modelName} status"
                ], 500);
            }
            
        } catch (Exception $e) {
            Logger::error("{$this->modelName} status toggle failed", ['error' => $e->getMessage(), 'record_id' => $id]);
            return $this->jsonResponse([
                'success' => false,
                'message' => "An error occurred while updating {$this->modelName} status"
            ], 500);
        }
    }
    
    // Override these methods in child controllers for model-specific behavior
    protected function addModelSpecificFields($data) {
        return $data;
    }
    
    protected function validateData($data, $isUpdate = false, $recordId = null) {
        return $this->model->validateMasterData($data, $isUpdate, $recordId);
    }
    
    protected function checkDependencies($id) {
        return ['allowed' => true, 'message' => ''];
    }
}
?>
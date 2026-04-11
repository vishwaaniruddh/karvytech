<?php
require_once __DIR__ . '/../models/BoqMaster.php';
require_once __DIR__ . '/BaseController.php';

class BoqMasterController extends BaseController {
    private $boqModel;
    
    public function __construct() {
        parent::__construct();
        $this->boqModel = new BoqMaster();
    }
    
    public function index() {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 20);
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $data = $this->boqModel->getAllWithPagination($page, $limit, $search, $status);
            
            // Format data for the view
            $result = [
                'records' => $data['records'],
                'pagination' => [
                    'current_page' => $data['page'],
                    'total_pages' => $data['pages'],
                    'total_records' => $data['total'],
                    'limit' => $data['limit']
                ],
                'search' => $search,
                'status_filter' => $status
            ];
            
            return $result;
        } catch (Exception $e) {
            error_log("BOQ index error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'boq_name' => trim($_POST['boq_name'] ?? ''),
                    'is_serial_number_required' => isset($_POST['is_serial_number_required']) ? 1 : 0,
                    'status' => $_POST['status'] ?? 'active'
                ];
                
                // Validation
                $errors = $this->boqModel->validateBoqData($data);
                if (!empty($errors)) {
                    throw new Exception(implode(', ', $errors));
                }
                
                $result = $this->boqModel->create($data);
                if ($result) {
                    $this->redirect('/admin/masters/boq?success=' . urlencode('BOQ created successfully'));
                } else {
                    throw new Exception('Failed to create BOQ');
                }
            } catch (Exception $e) {
                $this->render('admin/masters/boq/create', [
                    'error' => $e->getMessage(),
                    'data' => $data ?? [],
                    'title' => 'Create BOQ'
                ]);
            }
        } else {
            $this->render('admin/masters/boq/create', [
                'title' => 'Create BOQ'
            ]);
        }
    }
    
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'boq_name' => trim($_POST['boq_name'] ?? ''),
                    'is_serial_number_required' => isset($_POST['is_serial_number_required']) ? 1 : 0,
                    'status' => $_POST['status'] ?? 'active'
                ];
                
                // Validation
                $errors = $this->boqModel->validateBoqData($data, true, $id);
                if (!empty($errors)) {
                    throw new Exception(implode(', ', $errors));
                }
                
                $result = $this->boqModel->update($id, $data);
                if ($result) {
                    $this->redirect('/admin/masters/boq?success=' . urlencode('BOQ updated successfully'));
                } else {
                    throw new Exception('Failed to update BOQ');
                }
            } catch (Exception $e) {
                $boq = $this->boqModel->find($id);
                $this->render('admin/masters/boq/edit', [
                    'boq' => $boq,
                    'error' => $e->getMessage(),
                    'title' => 'Edit BOQ'
                ]);
            }
        } else {
            $boq = $this->boqModel->find($id);
            if (!$boq) {
                $this->redirect('/admin/masters/boq?error=' . urlencode('BOQ not found'));
                return;
            }
            
            $this->render('admin/masters/boq/edit', [
                'boq' => $boq,
                'title' => 'Edit BOQ'
            ]);
        }
    }
    
    public function view($id) {
        $boq = $this->boqModel->find($id);
        if (!$boq) {
            $this->redirect('/admin/masters/boq?error=' . urlencode('BOQ not found'));
            return;
        }
        
        $this->render('admin/masters/boq/view', [
            'boq' => $boq,
            'title' => 'View BOQ'
        ]);
    }
    
    public function delete($id) {
        try {
            $result = $this->boqModel->delete($id);
            if ($result) {
                $this->redirect('/admin/masters/boq?success=' . urlencode('BOQ deleted successfully'));
            } else {
                throw new Exception('Failed to delete BOQ');
            }
        } catch (Exception $e) {
            $this->redirect('/admin/masters/boq?error=' . urlencode($e->getMessage()));
        }
    }
    
    public function toggleStatus($id) {
        try {
            $result = $this->boqModel->toggleStatus($id);
            if ($result) {
                $this->redirect('/admin/masters/boq?success=' . urlencode('BOQ status updated successfully'));
            } else {
                throw new Exception('Failed to update BOQ status');
            }
        } catch (Exception $e) {
            $this->redirect('/admin/masters/boq?error=' . urlencode($e->getMessage()));
        }
    }
}
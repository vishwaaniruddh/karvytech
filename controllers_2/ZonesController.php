<?php
require_once __DIR__ . '/../models/Zone.php';
require_once __DIR__ . '/BaseController.php';

class ZonesController extends BaseController {
    private $zoneModel;
    
    public function __construct() {
        parent::__construct();
        $this->zoneModel = new Zone();
    }
    
    public function index() {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 20);
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $data = $this->zoneModel->getAllWithPagination($page, $limit, $search, $status);
            
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
            error_log("Zones index error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'status' => (int)($_POST['status'] ?? 1)
                ];
                
                // Validation
                if (empty($data['name'])) {
                    throw new Exception('Zone name is required');
                }
                
                $result = $this->zoneModel->create($data);
                if ($result) {
                    $this->redirect('/admin/masters/zones?success=' . urlencode('Zone created successfully'));
                } else {
                    throw new Exception('Failed to create zone');
                }
            } catch (Exception $e) {
                $this->render('admin/masters/zones/create', [
                    'error' => $e->getMessage(),
                    'data' => $data ?? [],
                    'title' => 'Create Zone'
                ]);
            }
        } else {
            $this->render('admin/masters/zones/create', [
                'title' => 'Create Zone'
            ]);
        }
    }
    
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'status' => (int)($_POST['status'] ?? 1)
                ];
                
                // Validation
                if (empty($data['name'])) {
                    throw new Exception('Zone name is required');
                }
                
                $result = $this->zoneModel->update($id, $data);
                if ($result) {
                    $this->redirect('/admin/masters/zones?success=' . urlencode('Zone updated successfully'));
                } else {
                    throw new Exception('Failed to update zone');
                }
            } catch (Exception $e) {
                $zone = $this->zoneModel->getById($id);
                $this->render('admin/masters/zones/edit', [
                    'zone' => $zone,
                    'error' => $e->getMessage(),
                    'title' => 'Edit Zone'
                ]);
            }
        } else {
            $zone = $this->zoneModel->getById($id);
            if (!$zone) {
                $this->redirect('/admin/masters/zones?error=' . urlencode('Zone not found'));
                return;
            }
            
            $this->render('admin/masters/zones/edit', [
                'zone' => $zone,
                'title' => 'Edit Zone'
            ]);
        }
    }
    
    public function view($id) {
        $zone = $this->zoneModel->getById($id);
        if (!$zone) {
            $this->redirect('/admin/masters/zones?error=' . urlencode('Zone not found'));
            return;
        }
        
        $this->render('admin/masters/zones/view', [
            'zone' => $zone,
            'title' => 'View Zone'
        ]);
    }
    
    public function delete($id) {
        try {
            $result = $this->zoneModel->delete($id);
            if ($result) {
                $this->redirect('/admin/masters/zones?success=' . urlencode('Zone deleted successfully'));
            } else {
                throw new Exception('Failed to delete zone');
            }
        } catch (Exception $e) {
            $this->redirect('/admin/masters/zones?error=' . urlencode($e->getMessage()));
        }
    }
    
    public function toggleStatus($id) {
        try {
            $result = $this->zoneModel->toggleStatus($id);
            if ($result) {
                $this->redirect('/admin/masters/zones?success=' . urlencode('Zone status updated successfully'));
            } else {
                throw new Exception('Failed to update zone status');
            }
        } catch (Exception $e) {
            $this->redirect('/admin/masters/zones?error=' . urlencode($e->getMessage()));
        }
    }
}
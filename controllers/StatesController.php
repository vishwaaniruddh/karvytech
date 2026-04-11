<?php
require_once __DIR__ . '/../models/State.php';
require_once __DIR__ . '/BaseController.php';

class StatesController extends BaseController {
    private $stateModel;
    
    public function __construct() {
        parent::__construct();
        $this->stateModel = new State();
    }
    
    public function index() {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 20);
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $data = $this->stateModel->getAllWithRelations($page, $limit, $search, $status);
            
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
            error_log("States index error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'country_id' => (int)($_POST['country_id'] ?? 0),
                    'status' => (int)($_POST['status'] ?? 1)
                ];
                
                // Validation
                if (empty($data['name'])) {
                    throw new Exception('State name is required');
                }
                if ($data['country_id'] <= 0) {
                    throw new Exception('Please select a country');
                }
                
                $result = $this->stateModel->create($data);
                if ($result) {
                    $this->redirect('/admin/masters/states?success=' . urlencode('State created successfully'));
                } else {
                    throw new Exception('Failed to create state');
                }
            } catch (Exception $e) {
                $countries = $this->stateModel->getCountries();
                $this->render('admin/masters/states/create', [
                    'countries' => $countries,
                    'error' => $e->getMessage(),
                    'data' => $data ?? [],
                    'title' => 'Create State'
                ]);
            }
        } else {
            $countries = $this->stateModel->getCountries();
            $this->render('admin/masters/states/create', [
                'countries' => $countries,
                'title' => 'Create State'
            ]);
        }
    }
    
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'country_id' => (int)($_POST['country_id'] ?? 0),
                    'status' => (int)($_POST['status'] ?? 1)
                ];
                
                // Validation
                if (empty($data['name'])) {
                    throw new Exception('State name is required');
                }
                if ($data['country_id'] <= 0) {
                    throw new Exception('Please select a country');
                }
                
                $result = $this->stateModel->update($id, $data);
                if ($result) {
                    $this->redirect('/admin/masters/states?success=' . urlencode('State updated successfully'));
                } else {
                    throw new Exception('Failed to update state');
                }
            } catch (Exception $e) {
                $state = $this->stateModel->getById($id);
                $countries = $this->stateModel->getCountries();
                $this->render('admin/masters/states/edit', [
                    'state' => $state,
                    'countries' => $countries,
                    'error' => $e->getMessage(),
                    'title' => 'Edit State'
                ]);
            }
        } else {
            $state = $this->stateModel->getById($id);
            if (!$state) {
                $this->redirect('/admin/masters/states?error=' . urlencode('State not found'));
                return;
            }
            
            $countries = $this->stateModel->getCountries();
            $this->render('admin/masters/states/edit', [
                'state' => $state,
                'countries' => $countries,
                'title' => 'Edit State'
            ]);
        }
    }
    
    public function view($id) {
        $state = $this->stateModel->getById($id);
        if (!$state) {
            $this->redirect('/admin/masters/states?error=' . urlencode('State not found'));
            return;
        }
        
        $this->render('admin/masters/states/view', [
            'state' => $state,
            'title' => 'View State'
        ]);
    }
    
    public function delete($id) {
        try {
            $result = $this->stateModel->delete($id);
            if ($result) {
                $this->redirect('/admin/masters/states?success=' . urlencode('State deleted successfully'));
            } else {
                throw new Exception('Failed to delete state');
            }
        } catch (Exception $e) {
            $this->redirect('/admin/masters/states?error=' . urlencode($e->getMessage()));
        }
    }
    
    public function toggleStatus($id) {
        try {
            $result = $this->stateModel->toggleStatus($id);
            if ($result) {
                $this->redirect('/admin/masters/states?success=' . urlencode('State status updated successfully'));
            } else {
                throw new Exception('Failed to update state status');
            }
        } catch (Exception $e) {
            $this->redirect('/admin/masters/states?error=' . urlencode($e->getMessage()));
        }
    }
    
    // API endpoint for getting states by country
    public function getByCountry($countryId) {
        header('Content-Type: application/json');
        try {
            $states = $this->stateModel->getByCountry($countryId);
            echo json_encode(['success' => true, 'data' => $states]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
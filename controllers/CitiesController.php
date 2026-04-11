<?php
require_once __DIR__ . '/../models/City.php';
require_once __DIR__ . '/BaseController.php';

class CitiesController extends BaseController {
    private $cityModel;
    
    public function __construct() {
        parent::__construct();
        $this->cityModel = new City();
    }
    
    public function index() {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 20);
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $data = $this->cityModel->getAllWithRelations($page, $limit, $search, $status);
            
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
            error_log("Cities index error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'state_id' => (int)($_POST['state_id'] ?? 0),
                    'status' => (int)($_POST['status'] ?? 1)
                ];
                
                // Validation
                if (empty($data['name'])) {
                    throw new Exception('City name is required');
                }
                if ($data['state_id'] <= 0) {
                    throw new Exception('Please select a state');
                }
                
                // Get country_id from state
                $state = $this->cityModel->getStateById($data['state_id']);
                if (!$state) {
                    throw new Exception('Invalid state selected');
                }
                $data['country_id'] = $state['country_id'];
                
                $result = $this->cityModel->create($data);
                if ($result) {
                    $this->redirect('/admin/masters/cities?success=' . urlencode('City created successfully'));
                } else {
                    throw new Exception('Failed to create city');
                }
            } catch (Exception $e) {
                $countries = $this->cityModel->getCountries();
                $states = $this->cityModel->getStates();
                $this->render('admin/masters/cities/create', [
                    'countries' => $countries,
                    'states' => $states,
                    'error' => $e->getMessage(),
                    'data' => $data ?? [],
                    'title' => 'Create City'
                ]);
            }
        } else {
            $countries = $this->cityModel->getCountries();
            $states = $this->cityModel->getStates();
            $this->render('admin/masters/cities/create', [
                'countries' => $countries,
                'states' => $states,
                'title' => 'Create City'
            ]);
        }
    }
    
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'state_id' => (int)($_POST['state_id'] ?? 0),
                    'status' => (int)($_POST['status'] ?? 1)
                ];
                
                // Validation
                if (empty($data['name'])) {
                    throw new Exception('City name is required');
                }
                if ($data['state_id'] <= 0) {
                    throw new Exception('Please select a state');
                }
                
                // Get country_id from state
                $state = $this->cityModel->getStateById($data['state_id']);
                if (!$state) {
                    throw new Exception('Invalid state selected');
                }
                $data['country_id'] = $state['country_id'];
                
                $result = $this->cityModel->update($id, $data);
                if ($result) {
                    $this->redirect('/admin/masters/cities?success=' . urlencode('City updated successfully'));
                } else {
                    throw new Exception('Failed to update city');
                }
            } catch (Exception $e) {
                $city = $this->cityModel->getById($id);
                $countries = $this->cityModel->getCountries();
                $states = $this->cityModel->getStates();
                $this->render('admin/masters/cities/edit', [
                    'city' => $city,
                    'countries' => $countries,
                    'states' => $states,
                    'error' => $e->getMessage(),
                    'title' => 'Edit City'
                ]);
            }
        } else {
            $city = $this->cityModel->getById($id);
            if (!$city) {
                $this->redirect('/admin/masters/cities?error=' . urlencode('City not found'));
                return;
            }
            
            $countries = $this->cityModel->getCountries();
            $states = $this->cityModel->getStates();
            $this->render('admin/masters/cities/edit', [
                'city' => $city,
                'countries' => $countries,
                'states' => $states,
                'title' => 'Edit City'
            ]);
        }
    }
    
    public function view($id) {
        $city = $this->cityModel->getById($id);
        if (!$city) {
            $this->redirect('/admin/masters/cities?error=' . urlencode('City not found'));
            return;
        }
        
        $this->render('admin/masters/cities/view', [
            'city' => $city,
            'title' => 'View City'
        ]);
    }
    
    public function delete($id) {
        try {
            $result = $this->cityModel->delete($id);
            if ($result) {
                $this->redirect('/admin/masters/cities?success=' . urlencode('City deleted successfully'));
            } else {
                throw new Exception('Failed to delete city');
            }
        } catch (Exception $e) {
            $this->redirect('/admin/masters/cities?error=' . urlencode($e->getMessage()));
        }
    }
    
    public function toggleStatus($id) {
        try {
            $result = $this->cityModel->toggleStatus($id);
            if ($result) {
                $this->redirect('/admin/masters/cities?success=' . urlencode('City status updated successfully'));
            } else {
                throw new Exception('Failed to update city status');
            }
        } catch (Exception $e) {
            $this->redirect('/admin/masters/cities?error=' . urlencode($e->getMessage()));
        }
    }
    
    // API endpoint for getting cities by state
    public function getByState($stateId) {
        header('Content-Type: application/json');
        try {
            $cities = $this->cityModel->getByState($stateId);
            echo json_encode(['success' => true, 'data' => $cities]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Zone.php';
require_once __DIR__ . '/../models/Country.php';
require_once __DIR__ . '/../models/State.php';
require_once __DIR__ . '/../models/City.php';
require_once __DIR__ . '/../models/Bank.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/BoqMaster.php';
require_once __DIR__ . '/../models/Vendor.php';
require_once __DIR__ . '/../models/SurveyType.php';
require_once __DIR__ . '/../models/InstallationType.php';
require_once __DIR__ . '/../models/Courier.php';

class ApiController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        header('Content-Type: application/json');
    }
    
    private function sendResponse($success, $data = null, $message = '', $errors = []) {
        $response = [
            'success' => $success,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        $json = json_encode($response);
        if ($json === false) {
            // Handle JSON encoding error (likely due to non-UTF8 characters)
            error_log("API JSON encode error: " . json_last_error_msg());
            $response['data'] = null;
            $response['success'] = false;
            $response['message'] = "JSON encoding failed: " . json_last_error_msg();
            echo json_encode($response);
        } else {
            echo $json;
        }
        exit;
    }
    
    private function getModelByType($type) {
        switch ($type) {
            case 'zones':
                return new Zone();
            case 'countries':
                return new Country();
            case 'states':
                return new State();
            case 'cities':
                return new City();
            case 'banks':
                return new Bank();
            case 'customers':
                return new Customer();
            case 'boq':
                return new BoqMaster();
            case 'vendors':
                return new Vendor();
            case 'survey':
                return new SurveyType();
            case 'installation':
                return new InstallationType();
            case 'couriers':
                return new Courier();
            default:
                return null;
        }
    }
    
    public function index($type) {
        try {
            $model = $this->getModelByType($type);
            if (!$model) {
                $this->sendResponse(false, null, 'Invalid master type');
            }
            
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 20);
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            
            // Handle filtering for states by country
            if ($type === 'states' && !empty($_GET['country_id'])) {
                $countryId = (int)$_GET['country_id'];
                $filters = ['country_id' => $countryId];
                $result = $model->getAllWithPagination($page, $limit, $search, $status, $filters);
            } else {
                $result = $model->getAllWithPagination($page, $limit, $search, $status);
            }
            
            $this->sendResponse(true, [
                'records' => $result['records'],
                'pagination' => [
                    'current_page' => $result['page'],
                    'total_pages' => $result['pages'],
                    'total_records' => $result['total'],
                    'limit' => $result['limit']
                ],
                'search' => $search,
                'status_filter' => $status
            ]);
            
        } catch (Exception $e) {
            error_log("API index error: " . $e->getMessage());
            $this->sendResponse(false, null, 'Failed to fetch data');
        }
    }
    
    public function show($type, $id) {
        try {
            $model = $this->getModelByType($type);
            if (!$model) {
                $this->sendResponse(false, null, 'Invalid master type');
            }
            
            $record = $model->find($id);
            if (!$record) {
                $this->sendResponse(false, null, 'Record not found');
            }
            
            $this->sendResponse(true, ['record' => $record]);
            
        } catch (Exception $e) {
            error_log("API show error: " . $e->getMessage());
            $this->sendResponse(false, null, 'Failed to fetch record');
        }
    }
    
    public function store($type) {
        try {
            $model = $this->getModelByType($type);
            if (!$model) {
                $this->sendResponse(false, null, 'Invalid master type');
            }
            
            $data = $this->getPostData($type);
            
            // Validate data
            $errors = $this->validateData($type, $data);
            if (!empty($errors)) {
                $this->sendResponse(false, null, 'Validation failed', $errors);
            }
            
            $id = $model->create($data);
            if ($id) {
                $record = $model->find($id);
                $this->sendResponse(true, ['record' => $record], 'Record created successfully');
            } else {
                $this->sendResponse(false, null, 'Failed to create record');
            }
            
        } catch (Exception $e) {
            error_log("API store error: " . $e->getMessage());
            $this->sendResponse(false, null, 'Failed to create record');
        }
    }

    public function bulkStore($type) {
        if ($type !== 'cities') {
            $this->sendResponse(false, null, 'Bulk upload only supported for cities currently');
        }

        if (!isset($_FILES['bulkFile']) || $_FILES['bulkFile']['error'] !== UPLOAD_ERR_OK) {
            $this->sendResponse(false, null, 'No file uploaded or upload error');
        }

        $file = $_FILES['bulkFile']['tmp_name'];
        
        // Detect file encoding
        $content = file_get_contents($file);
        $encoding = mb_detect_encoding($content, 'UTF-8, ISO-8859-1, Windows-1252', true);
        
        $handle = fopen($file, 'r');
        if (!$handle) {
            $this->sendResponse(false, null, 'Failed to open file');
        }

        // Read header
        $header = fgetcsv($handle);
        
        $cityModel = new City();
        $stateModel = new State();
        $countryModel = new Country();

        $successCount = 0;
        $failCount = 0;
        $errors = [];
        $rows = []; // For detailed results table
        $rowNum = 1;

        while (($data = fgetcsv($handle)) !== FALSE) {
            // Convert encoding if not UTF-8
            if ($encoding && $encoding !== 'UTF-8') {
                $data = array_map(function($item) use ($encoding) {
                    return $item !== null ? mb_convert_encoding($item, 'UTF-8', $encoding) : null;
                }, $data);
            }

            $rowNum++;
            $cityName = isset($data[0]) ? trim($data[0]) : '';
            $countryName = isset($data[1]) ? trim($data[1]) : '';
            $stateName = isset($data[2]) ? trim($data[2]) : '';
            $status = isset($data[3]) ? trim(strtolower($data[3])) : 'active';
            
            $rowResult = [
                'row' => $rowNum,
                'name' => $cityName,
                'country' => $countryName,
                'state' => $stateName,
                'status' => 'pending',
                'message' => ''
            ];

            // Basic validation
            if (empty($cityName) || empty($countryName) || empty($stateName)) {
                $failCount++;
                $rowResult['status'] = 'failed';
                $rowResult['message'] = "Missing required fields (City, Country, or State)";
                $rows[] = $rowResult;
                continue;
            }

            if (!in_array($status, ['active', 'inactive'])) $status = 'active';

            // 1. Find Country
            $country = $countryModel->findByName($countryName);
            if (!$country) {
                $failCount++;
                $rowResult['status'] = 'failed';
                $rowResult['message'] = "Country '$countryName' not found";
                $rows[] = $rowResult;
                continue;
            }

            // 2. Find State in that Country
            $states = $stateModel->findAll(['name' => $stateName, 'country_id' => $country['id']]);
            $state = $states[0] ?? null;
            if (!$state) {
                $failCount++;
                $rowResult['status'] = 'failed';
                $rowResult['message'] = "State '$stateName' not found in '$countryName'";
                $rows[] = $rowResult;
                continue;
            }

            // 3. Check if City already exists in that State
            $existingCities = $cityModel->findAll(['name' => $cityName, 'state_id' => $state['id']]);
            if (!empty($existingCities)) {
                $failCount++;
                $rowResult['status'] = 'failed';
                $rowResult['message'] = "City already exists in '$stateName'";
                $rows[] = $rowResult;
                continue;
            }

            // 4. Create City
            try {
                $insertData = [
                    'name' => $cityName,
                    'state_id' => $state['id'],
                    'country_id' => $country['id'],
                    'zone_id' => $state['zone_id'] ?? null,
                    'status' => $status,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if ($cityModel->create($insertData)) {
                    $successCount++;
                    $rowResult['status'] = 'success';
                    $rowResult['message'] = "Created successfully";
                } else {
                    $failCount++;
                    $rowResult['status'] = 'failed';
                    $rowResult['message'] = "Database insertion failed";
                    error_log("City bulk upload error row $rowNum: Insert failed for " . json_encode($insertData));
                }
            } catch (Exception $e) {
                $failCount++;
                $rowResult['status'] = 'failed';
                $rowResult['message'] = "Error: " . $e->getMessage();
                error_log("City bulk upload exception row $rowNum: " . $e->getMessage());
            }
            $rows[] = $rowResult;
        }

        fclose($handle);

        $message = "Bulk upload completed.";
        if ($successCount > 0) {
            $message .= " $successCount cities added.";
        }
        if ($failCount > 0) {
            $message .= " $failCount rows failed.";
        }

        $this->sendResponse(true, [
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'rows' => $rows
        ], $message);
    }
    
    public function update($type, $id) {
        try {
            $model = $this->getModelByType($type);
            if (!$model) {
                $this->sendResponse(false, null, 'Invalid master type');
            }
            
            $record = $model->find($id);
            if (!$record) {
                $this->sendResponse(false, null, 'Record not found');
            }
            
            $data = $this->getPostData($type);
            
            // Validate data
            $errors = $this->validateData($type, $data, true, $id);
            if (!empty($errors)) {
                $this->sendResponse(false, null, 'Validation failed', $errors);
            }
            
            $success = $model->update($id, $data);
            if ($success) {
                $record = $model->find($id);
                $this->sendResponse(true, ['record' => $record], 'Record updated successfully');
            } else {
                $this->sendResponse(false, null, 'Failed to update record');
            }
            
        } catch (Exception $e) {
            error_log("API update error: " . $e->getMessage());
            $this->sendResponse(false, null, 'Failed to update record');
        }
    }
    
    public function destroy($type, $id) {
        try {
            $model = $this->getModelByType($type);
            if (!$model) {
                $this->sendResponse(false, null, 'Invalid master type');
            }
            
            $record = $model->find($id);
            if (!$record) {
                $this->sendResponse(false, null, 'Record not found');
            }
            
            $success = $model->delete($id);
            if ($success) {
                $this->sendResponse(true, null, 'Record deleted successfully');
            } else {
                $this->sendResponse(false, null, 'Failed to delete record');
            }
            
        } catch (Exception $e) {
            error_log("API destroy error: " . $e->getMessage());
            $this->sendResponse(false, null, 'Failed to delete record');
        }
    }
    
    public function toggleStatus($type, $id) {
        try {
            $model = $this->getModelByType($type);
            if (!$model) {
                $this->sendResponse(false, null, 'Invalid master type');
            }
            
            $record = $model->find($id);
            if (!$record) {
                $this->sendResponse(false, null, 'Record not found');
            }
            
            $success = $model->toggleStatus($id);
            if ($success) {
                $record = $model->find($id);
                $this->sendResponse(true, ['record' => $record], 'Status updated successfully');
            } else {
                $this->sendResponse(false, null, 'Failed to update status');
            }
            
        } catch (Exception $e) {
            error_log("API toggleStatus error: " . $e->getMessage());
            $this->sendResponse(false, null, 'Failed to update status');
        }
    }
    
    // Location-specific endpoints
    public function getStatesByCountry($countryId) {
        try {
            $stateModel = new State();
            $states = $stateModel->getByCountry($countryId);
            $this->sendResponse(true, $states);
        } catch (Exception $e) {
            error_log("API getStatesByCountry error: " . $e->getMessage());
            $this->sendResponse(false, null, 'Failed to fetch states');
        }
    }
    
    public function getCitiesByState($stateId) {
        try {
            $cityModel = new City();
            $cities = $cityModel->getByState($stateId);
            $this->sendResponse(true, $cities);
        } catch (Exception $e) {
            error_log("API getCitiesByState error: " . $e->getMessage());
            $this->sendResponse(false, null, 'Failed to fetch cities');
        }
    }
    
    private function getPostData($type) {
        $data = [];
        
        switch ($type) {
            case 'zones':
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'status' => $_POST['status'] ?? 'active'
                ];
                break;
                
            case 'countries':
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'status' => $_POST['status'] ?? 'active'
                ];
                break;
                
            case 'states':
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'country_id' => (int)($_POST['country_id'] ?? 0),
                    'zone_id' => !empty($_POST['zone_id']) ? (int)$_POST['zone_id'] : null,
                    'status' => $_POST['status'] ?? 'active'
                ];
                break;
                
            case 'cities':
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'state_id' => (int)($_POST['state_id'] ?? 0),
                    'status' => $_POST['status'] ?? 'active'
                ];
                
                // Get country_id from state
                if ($data['state_id'] > 0) {
                    $cityModel = new City();
                    $state = $cityModel->getStateById($data['state_id']);
                    if ($state) {
                        $data['country_id'] = $state['country_id'];
                    }
                }
                break;
                
            case 'banks':
            case 'customers':
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'status' => $_POST['status'] ?? 'active'
                ];
                break;
                
            case 'boq':
                $data = [
                    'boq_name' => trim($_POST['boq_name'] ?? ''),
                    'is_serial_number_required' => isset($_POST['is_serial_number_required']) ? 1 : 0,
                    'status' => $_POST['status'] ?? 'active'
                ];
                break;
                
            case 'vendors':
                $data = [
                    'name' => trim($_POST['name'] ?? ''),
                    'status' => $_POST['status'] ?? 'active'
                ];
                break;
                
            case 'couriers':
                $data = [
                    'courier_name' => trim($_POST['name'] ?? $_POST['courier_name'] ?? ''),
                    'status' => $_POST['status'] ?? 'active'
                ];
                break;
        }
        
        return $data;
    }
    
    private function validateData($type, $data, $isUpdate = false, $recordId = null) {
        $errors = [];
        
        // Common validation
        if ($type === 'boq') {
            if (empty($data['boq_name'])) {
                $errors['boq_name'] = 'BOQ name is required';
            }
        } elseif ($type === 'couriers') {
            if (empty($data['courier_name'])) {
                $errors['courier_name'] = 'Courier name is required';
            }
        } else {
            if (empty($data['name'])) {
                $errors['name'] = 'Name is required';
            }
        }
        
        // Type-specific validation
        switch ($type) {
            case 'states':
                if (empty($data['country_id']) || $data['country_id'] <= 0) {
                    $errors['country_id'] = 'Please select a country';
                }
                break;
                
            case 'cities':
                if (empty($data['state_id']) || $data['state_id'] <= 0) {
                    $errors['state_id'] = 'Please select a state';
                }
                break;
        }
        
        return $errors;
    }
}
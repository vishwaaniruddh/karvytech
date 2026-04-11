<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/error_handler.php';
require_once __DIR__ . '/../includes/logger.php';
require_once __DIR__ . '/../includes/validation_helper.php';

class SitesController extends BaseController {
    private $siteModel;
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->siteModel = new Site();
        $this->userModel = new User();
    }
    
    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        $filters = [
            'city' => isset($_GET['city']) ? trim($_GET['city']) : '',
            'state' => isset($_GET['state']) ? trim($_GET['state']) : '',
            'activity_status' => isset($_GET['activity_status']) ? trim($_GET['activity_status']) : '',
            'vendor' => isset($_GET['vendor']) ? trim($_GET['vendor']) : '',
            'survey_status' => isset($_GET['survey_status']) ? trim($_GET['survey_status']) : ''
        ];
        
        $result = $this->siteModel->getAllWithPagination($page, 20, $search, $filters);
        
        // Get filter options
        $cities = $this->siteModel->getUniqueValues('city');
        $states = $this->siteModel->getUniqueValues('state');
        $activityStatuses = $this->siteModel->getUniqueValues('activity_status');
        $vendors = $this->siteModel->getUniqueValues('vendor');
        
        return [
            'sites' => $result['sites'],
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['pages'],
                'total_records' => $result['total'],
                'limit' => $result['limit']
            ],
            'search' => $search,
            'filters' => $filters,
            'filter_options' => [
                'cities' => $cities,
                'states' => $states,
                'activity_statuses' => $activityStatuses,
                'vendors' => $vendors
            ]
        ];
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->store();
        }
        
        return ['site' => null];
    }
    
    public function store() {
        try {
            $currentUser = Auth::getCurrentUser();
            $siteTicketId = $this->siteModel->generateSiteTicketId();
            
            $data = [
                'site_id' => trim($_POST['site_id'] ?? ''),
                'store_id' => trim($_POST['store_id'] ?? ''),
                'location' => trim($_POST['location'] ?? ''),
                'country_id' => !empty($_POST['country_id']) ? (int)$_POST['country_id'] : null,
                'state_id' => !empty($_POST['state_id']) ? (int)$_POST['state_id'] : null,
                'city_id' => !empty($_POST['city_id']) ? (int)$_POST['city_id'] : null,
                'branch' => trim($_POST['branch'] ?? ''),
                'remarks' => trim($_POST['remarks'] ?? ''),
                'po_number' => trim($_POST['po_number'] ?? ''),
                'po_date' => !empty($_POST['po_date']) ? $_POST['po_date'] : null,
                'customer_id' => !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null,
                'bank_id' => !empty($_POST['bank_id']) ? (int)$_POST['bank_id'] : null,
                'vendor' => trim($_POST['vendor'] ?? ''),
                'activity_status' => trim($_POST['activity_status'] ?? ''),
                'is_delegate' => isset($_POST['is_delegate']) ? 1 : 0,
                'delegated_vendor' => trim($_POST['delegated_vendor'] ?? ''),
                'survey_status' => isset($_POST['survey_status']) ? 1 : 0,
                'installation_status' => isset($_POST['installation_status']) ? 1 : 0,
                'is_material_request_generated' => isset($_POST['is_material_request_generated']) ? 1 : 0,
                'survey_submission_date' => !empty($_POST['survey_submission_date']) ? $_POST['survey_submission_date'] : null,
                'installation_date' => !empty($_POST['installation_date']) ? $_POST['installation_date'] : null,
                'contact_person_name' => trim($_POST['contact_person_name'] ?? ''),
                'contact_person_number' => trim($_POST['contact_person_number'] ?? ''),
                'contact_person_email' => trim($_POST['contact_person_email'] ?? ''),
                'created_by' => $currentUser['username'],
                'site_ticket_id' => $siteTicketId
            ];
            
            // Validate contact person fields using validation helper
            $validationErrors = [];
            
            // Validate phone number if provided
            if (!empty($data['contact_person_number'])) {
                $phoneValidation = validatePhoneNumber($data['contact_person_number'], false);
                if (!$phoneValidation['valid']) {
                    $validationErrors[] = 'Contact Person Number: ' . $phoneValidation['message'];
                } else {
                    // Use formatted phone number
                    $data['contact_person_number'] = $phoneValidation['formatted'];
                }
            }
            
            // Validate email if provided
            if (!empty($data['contact_person_email'])) {
                $emailValidation = validateEmail($data['contact_person_email'], false);
                if (!$emailValidation['valid']) {
                    $validationErrors[] = 'Contact Person Email: ' . $emailValidation['message'];
                }
            }
            
            // Return validation errors if any
            if (!empty($validationErrors)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validationErrors
                ], 400);
            }
            
            // Validate data
            $errors = $this->siteModel->validateSiteData($data);
            
            if (!empty($errors)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], 400);
            }
            
            // Create site
            $siteId = $this->siteModel->create($data);
            
            if ($siteId) {
                // Log the action
                ErrorHandler::logUserAction('CREATE_SITE', 'sites', $siteId, null, $data);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Site created successfully',
                    'site_id' => $siteId
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to create site'
                ], 500);
            }
            
        } catch (Exception $e) {
            Logger::error('Site creation failed', ['error' => $e->getMessage()]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'An error occurred while creating the site'
            ], 500);
        }
    }
    
    public function show($id) {
        $site = $this->siteModel->findWithRelations($id);
        
        if (!$site) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Site not found'
            ], 404);
        }
        
        return $this->jsonResponse([
            'success' => true,
            'site' => $site
        ]);
    }
    
    public function edit($id) {
        $site = $this->siteModel->findWithRelations($id);
        
        if (!$site) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Site not found'
            ], 404);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->update($id);
        }
        
        return $this->jsonResponse([
            'success' => true,
            'site' => $site
        ]);
    }
    
    public function update($id) {
        try {
            $site = $this->siteModel->find($id);
            
            if (!$site) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Site not found'
                ], 404);
            }
            
            $currentUser = Auth::getCurrentUser();
            
            $data = [
                'site_id' => trim($_POST['site_id'] ?? ''),
                'store_id' => trim($_POST['store_id'] ?? ''),
                'location' => trim($_POST['location'] ?? ''),
                'country_id' => !empty($_POST['country_id']) ? (int)$_POST['country_id'] : null,
                'state_id' => !empty($_POST['state_id']) ? (int)$_POST['state_id'] : null,
                'city_id' => !empty($_POST['city_id']) ? (int)$_POST['city_id'] : null,
                'branch' => trim($_POST['branch'] ?? ''),
                'remarks' => trim($_POST['remarks'] ?? ''),
                'po_number' => trim($_POST['po_number'] ?? ''),
                'po_date' => !empty($_POST['po_date']) ? $_POST['po_date'] : null,
                'customer_id' => !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null,
                'bank_id' => !empty($_POST['bank_id']) ? (int)$_POST['bank_id'] : null,
                'vendor' => trim($_POST['vendor'] ?? ''),
                'activity_status' => trim($_POST['activity_status'] ?? ''),
                'is_delegate' => isset($_POST['is_delegate']) ? 1 : 0,
                'delegated_vendor' => trim($_POST['delegated_vendor'] ?? ''),
                'survey_status' => isset($_POST['survey_status']) ? 1 : 0,
                'installation_status' => isset($_POST['installation_status']) ? 1 : 0,
                'is_material_request_generated' => isset($_POST['is_material_request_generated']) ? 1 : 0,
                'survey_submission_date' => !empty($_POST['survey_submission_date']) ? $_POST['survey_submission_date'] : null,
                'installation_date' => !empty($_POST['installation_date']) ? $_POST['installation_date'] : null,
                'contact_person_name' => trim($_POST['contact_person_name'] ?? ''),
                'contact_person_number' => trim($_POST['contact_person_number'] ?? ''),
                'contact_person_email' => trim($_POST['contact_person_email'] ?? ''),
                'updated_by' => $currentUser['username']
            ];
            
            // Validate contact person fields using validation helper
            $validationErrors = [];
            
            // Validate phone number if provided
            if (!empty($data['contact_person_number'])) {
                $phoneValidation = validatePhoneNumber($data['contact_person_number'], false);
                if (!$phoneValidation['valid']) {
                    $validationErrors[] = 'Contact Person Number: ' . $phoneValidation['message'];
                } else {
                    // Use formatted phone number
                    $data['contact_person_number'] = $phoneValidation['formatted'];
                }
            }
            
            // Validate email if provided
            if (!empty($data['contact_person_email'])) {
                $emailValidation = validateEmail($data['contact_person_email'], false);
                if (!$emailValidation['valid']) {
                    $validationErrors[] = 'Contact Person Email: ' . $emailValidation['message'];
                }
            }
            
            // Return validation errors if any
            if (!empty($validationErrors)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validationErrors
                ], 400);
            }
            
            // Validate data
            $errors = $this->siteModel->validateSiteData($data, true, $id);
            
            if (!empty($errors)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], 400);
            }
            
            // Update site
            $success = $this->siteModel->update($id, $data);
            
            if ($success) {
                // Log the action
                ErrorHandler::logUserAction('UPDATE_SITE', 'sites', $id, $site, $data);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Site updated successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to update site'
                ], 500);
            }
            
        } catch (Exception $e) {
            Logger::error('Site update failed', ['error' => $e->getMessage(), 'site_id' => $id]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'An error occurred while updating the site'
            ], 500);
        }
    }
    
    public function delete($id) {
        try {
            $site = $this->siteModel->find($id);
            
            if (!$site) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Site not found'
                ], 404);
            }
            
            // Delete site
            $success = $this->siteModel->delete($id);
            
            if ($success) {
                // Log the action
                ErrorHandler::logUserAction('DELETE_SITE', 'sites', $id, $site, null);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Site deleted successfully'
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to delete site'
                ], 500);
            }
            
        } catch (Exception $e) {
            Logger::error('Site deletion failed', ['error' => $e->getMessage(), 'site_id' => $id]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'An error occurred while deleting the site'
            ], 500);
        }
    }
    
    public function updateSurveyStatus($id) {
        try {
            $site = $this->siteModel->find($id);
            
            if (!$site) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Site not found'
                ], 404);
            }
            
            $status = isset($_POST['survey_status']) ? (bool)$_POST['survey_status'] : false;
            $submissionDate = $status ? date('Y-m-d H:i:s') : null;
            
            $success = $this->siteModel->updateSurveyStatus($id, $status, $submissionDate);
            
            if ($success) {
                ErrorHandler::logUserAction('UPDATE_SURVEY_STATUS', 'sites', $id, 
                    ['survey_status' => $site['survey_status']], 
                    ['survey_status' => $status, 'survey_submission_date' => $submissionDate]
                );
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Survey status updated successfully',
                    'new_status' => $status
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to update survey status'
                ], 500);
            }
            
        } catch (Exception $e) {
            Logger::error('Survey status update failed', ['error' => $e->getMessage(), 'site_id' => $id]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'An error occurred while updating survey status'
            ], 500);
        }
    }
    
    public function updateInstallationStatus($id) {
        try {
            $site = $this->siteModel->find($id);
            
            if (!$site) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Site not found'
                ], 404);
            }
            
            $status = isset($_POST['installation_status']) ? (bool)$_POST['installation_status'] : false;
            $installationDate = $status ? date('Y-m-d H:i:s') : null;
            
            $success = $this->siteModel->updateInstallationStatus($id, $status, $installationDate);
            
            if ($success) {
                ErrorHandler::logUserAction('UPDATE_INSTALLATION_STATUS', 'sites', $id, 
                    ['installation_status' => $site['installation_status']], 
                    ['installation_status' => $status, 'installation_date' => $installationDate]
                );
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Installation status updated successfully',
                    'new_status' => $status
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to update installation status'
                ], 500);
            }
            
        } catch (Exception $e) {
            Logger::error('Installation status update failed', ['error' => $e->getMessage(), 'site_id' => $id]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'An error occurred while updating installation status'
            ], 500);
        }
    }
}
?>
<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Vendor.php';
require_once __DIR__ . '/../includes/error_handler.php';
require_once __DIR__ . '/../includes/logger.php';

class VendorsController extends BaseController {
    private $vendorModel;
    
    public function __construct() {
        parent::__construct();
        $this->vendorModel = new Vendor();
    }
    
    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
        $limit = 10;
        
        $result = $this->vendorModel->getAllWithPagination($page, $limit, $search, $statusFilter);
        
        // Fetch dashboard stats
        $stats = $this->vendorModel->getVendorStats();
        
        // Add total and disabled counts for stats cards like Users page
        $stats['total'] = $this->db->query("SELECT COUNT(*) FROM vendors")->fetchColumn();
        $stats['disabled'] = $this->db->query("SELECT COUNT(*) FROM vendors WHERE status != 'active'")->fetchColumn();
        
        return [
            'vendors' => $result['records'],
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['pages'],
                'total_records' => $result['total'],
                'limit' => $result['limit']
            ],
            'stats' => $stats,
            'search' => $search,
            'status_filter' => $statusFilter
        ];
    }
    
    public function show($id) {
        $vendor = $this->vendorModel->findById($id);
        
        if (!$vendor) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Vendor not found'
            ], 404);
        }
        
        return $this->jsonResponse([
            'success' => true,
            'vendor' => $vendor
        ]);
    }
    
    public function store() {
        try {
            $data = $this->mapVendorData($_POST);
            
            // Basic validation
            if (empty($data['name'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Vendor name is required'
                ], 400);
            }
            
            // Check if vendor code already exists
            if (!empty($data['vendor_code'])) {
                $existing = $this->vendorModel->findByVendorCode($data['vendor_code']);
                if ($existing) {
                    return $this->jsonResponse([
                        'success' => false,
                        'message' => 'Vendor code already exists'
                    ], 400);
                }
            }
            
            // Create vendor first to get ID for file uploads
            $vendorId = $this->vendorModel->create($data);
            
            if ($vendorId) {
                // Handle file uploads
                $filePaths = [];
                if (isset($_FILES['experience_letter']) && $_FILES['experience_letter']['error'] === UPLOAD_ERR_OK) {
                    try {
                        $filePaths['experience_letter_path'] = $this->vendorModel->uploadFile($_FILES['experience_letter'], 'experience_letter', $vendorId);
                    } catch (Exception $e) {
                        Logger::error('Experience letter upload failed', ['error' => $e->getMessage()]);
                    }
                }
                
                if (isset($_FILES['photograph']) && $_FILES['photograph']['error'] === UPLOAD_ERR_OK) {
                    try {
                        $filePaths['photograph_path'] = $this->vendorModel->uploadFile($_FILES['photograph'], 'photograph', $vendorId);
                    } catch (Exception $e) {
                        Logger::error('Photograph upload failed', ['error' => $e->getMessage()]);
                    }
                }
                
                if (!empty($filePaths)) {
                    $this->vendorModel->update($vendorId, $filePaths);
                }

                // Log action
                ErrorHandler::logUserAction('CREATE_VENDOR', 'vendors', $vendorId, null, $data);
                
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Vendor created successfully',
                    'vendor_id' => $vendorId
                ]);
            } else {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Failed to create vendor'
                ], 500);
            }
        } catch (Exception $e) {
            Logger::error('Vendor creation failed', ['error' => $e->getMessage()]);
            return $this->jsonResponse([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update($id) {
        try {
            $vendor = $this->vendorModel->findById($id);
            if (!$vendor) {
                return $this->jsonResponse(['success' => false, 'message' => 'Vendor not found'], 404);
            }
            
            $data = $this->mapVendorData($_POST);

            // Check if vendor code already exists (excluding current vendor)
            if (!empty($data['vendor_code']) && $data['vendor_code'] !== $vendor['vendor_code']) {
                $existing = $this->vendorModel->findByVendorCode($data['vendor_code']);
                if ($existing) {
                    return $this->jsonResponse(['success' => false, 'message' => 'Vendor code already exists'], 400);
                }
            }

            // Only include password if provided
            if (empty($data['mobility_password'])) {
                unset($data['mobility_password']);
            }

            // Handle file uploads
            if (isset($_FILES['experience_letter']) && $_FILES['experience_letter']['error'] === UPLOAD_ERR_OK) {
                try {
                    $data['experience_letter_path'] = $this->vendorModel->uploadFile($_FILES['experience_letter'], 'experience_letter', $id);
                } catch (Exception $e) {
                    Logger::error('Experience letter upload failed', ['error' => $e->getMessage()]);
                }
            }
            
            if (isset($_FILES['photograph']) && $_FILES['photograph']['error'] === UPLOAD_ERR_OK) {
                try {
                    $data['photograph_path'] = $this->vendorModel->uploadFile($_FILES['photograph'], 'photograph', $id);
                } catch (Exception $e) {
                    Logger::error('Photograph upload failed', ['error' => $e->getMessage()]);
                }
            }

            $success = $this->vendorModel->update($id, $data);
            
            if ($success) {
                ErrorHandler::logUserAction('UPDATE_VENDOR', 'vendors', $id, $vendor, $data);
                return $this->jsonResponse(['success' => true, 'message' => 'Vendor updated successfully']);
            } else {
                return $this->jsonResponse(['success' => false, 'message' => 'Failed to update vendor'], 500);
            }
        } catch (Exception $e) {
            Logger::error('Vendor update failed', ['error' => $e->getMessage(), 'vendor_id' => $id]);
            return $this->jsonResponse(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    
    public function delete($id) {
        try {
            $vendor = $this->vendorModel->findById($id);
            if (!$vendor) {
                return $this->jsonResponse(['success' => false, 'message' => 'Vendor not found'], 404);
            }
            
            // Check for dependencies (sites, etc)
            $delegations = $this->vendorModel->getVendorDelegations($id, 'active');
            if (!empty($delegations)) {
                return $this->jsonResponse(['success' => false, 'message' => 'Cannot delete vendor with active site delegations'], 400);
            }
            
            // Soft delete by setting status to inactive
            $success = $this->vendorModel->update($id, ['status' => 'inactive']);
            if ($success) {
                ErrorHandler::logUserAction('DELETE_VENDOR', 'vendors', $id, $vendor, null);
                return $this->jsonResponse(['success' => true, 'message' => 'Vendor deleted successfully']);
            } else {
                return $this->jsonResponse(['success' => false, 'message' => 'Failed to delete vendor'], 500);
            }
        } catch (Exception $e) {
            Logger::error('Vendor deletion failed', ['error' => $e->getMessage(), 'vendor_id' => $id]);
            return $this->jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
        }
    }
    
    public function toggleStatus($id) {
        try {
            $vendor = $this->vendorModel->findById($id);
            if (!$vendor) {
                return $this->jsonResponse(['success' => false, 'message' => 'Vendor not found'], 404);
            }
            
            $newStatus = ($vendor['status'] === 'active') ? 'inactive' : 'active';
            $success = $this->vendorModel->update($id, ['status' => $newStatus]);
            
            if ($success) {
                ErrorHandler::logUserAction('TOGGLE_VENDOR_STATUS', 'vendors', $id, ['status' => $vendor['status']], ['status' => $newStatus]);
                return $this->jsonResponse(['success' => true, 'message' => "Vendor status changed to {$newStatus}", 'new_status' => $newStatus]);
            }
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to toggle status'], 500);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Maps incoming form field names to database/model field names
     */
    private function mapVendorData($data) {
        return [
            'name' => $data['vendorName'] ?? ($data['name'] ?? null),
            'phone' => $data['contact_number'] ?? ($data['phone'] ?? null),
            'vendor_code' => $data['vendor_code'] ?? null,
            'mobility_id' => $data['mobility_id'] ?? null,
            'mobility_password' => $data['mobility_password'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'address' => $data['address'] ?? null,
            'email' => $data['email'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'account_number' => $data['account_number'] ?? null,
            'ifsc_code' => $data['ifsc_code'] ?? null,
            'gst_number' => $data['gst_number'] ?? null,
            'pan_card_number' => $data['pan_card_number'] ?? null,
            'aadhaar_number' => $data['aadhaar_number'] ?? null,
            'msme_number' => $data['msme_number'] ?? null,
            'esic_number' => $data['esic_number'] ?? null,
            'pf_number' => $data['pf_number'] ?? null,
            'pvc_status' => $data['pvc_status'] ?? null,
            'status' => $data['status'] ?? 'active'
        ];
    }
}

<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Vendor.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $vendorModel = new Vendor();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            // Validate required fields
            $requiredFields = ['vendorName'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }
            
            // Check if vendor code already exists
            if (!empty($_POST['vendor_code'])) {
                $existing = $vendorModel->findByVendorCode($_POST['vendor_code']);
                if ($existing) {
                    throw new Exception('Vendor code already exists');
                }
            }
            
            // Prepare vendor data
            $vendorData = [
                'vendor_code' => $_POST['vendor_code'] ?? null,
                'name' => $_POST['vendorName'],
                'company_name' => $_POST['company_name'] ?? null,
                'email' => $_POST['email'] ?? null,
                'phone' => $_POST['contact_number'] ?? null,
                'address' => $_POST['address'] ?? null,
                'mobility_id' => $_POST['mobility_id'] ?? null,
                'mobility_password' => $_POST['mobility_password'] ?? null,
                'contact_person' => $_POST['vendorName'], // Use name as contact person for now
                'bank_name' => $_POST['bank_name'] ?? null,
                'account_number' => $_POST['account_number'] ?? null,
                'ifsc_code' => $_POST['ifsc_code'] ?? null,
                'gst_number' => $_POST['gst_number'] ?? null,
                'pan_card_number' => $_POST['pan_card_number'] ?? null,
                'aadhaar_number' => $_POST['aadhaar_number'] ?? null,
                'msme_number' => $_POST['msme_number'] ?? null,
                'esic_number' => $_POST['esic_number'] ?? null,
                'pf_number' => $_POST['pf_number'] ?? null,
                'pvc_status' => $_POST['pvc_status'] ?? 'No'
            ];
            
            // Create vendor first to get ID for file uploads
            $vendorId = $vendorModel->create($vendorData);
            
            if (!$vendorId) {
                throw new Exception('Failed to create vendor');
            }
            
            // Handle file uploads
            $filePaths = [];
            
            if (isset($_FILES['experience_letter']) && $_FILES['experience_letter']['error'] === UPLOAD_ERR_OK) {
                try {
                    $filePaths['experience_letter_path'] = $vendorModel->uploadFile($_FILES['experience_letter'], 'experience_letter', $vendorId);
                } catch (Exception $e) {
                    // Log error but don't fail the vendor creation
                    error_log('Experience letter upload failed: ' . $e->getMessage());
                }
            }
            
            if (isset($_FILES['photograph']) && $_FILES['photograph']['error'] === UPLOAD_ERR_OK) {
                try {
                    $filePaths['photograph_path'] = $vendorModel->uploadFile($_FILES['photograph'], 'photograph', $vendorId);
                } catch (Exception $e) {
                    // Log error but don't fail the vendor creation
                    error_log('Photograph upload failed: ' . $e->getMessage());
                }
            }
            
            // Update vendor with file paths if any were uploaded
            if (!empty($filePaths)) {
                $vendorModel->update($vendorId, $filePaths);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Vendor created successfully',
                'vendor_id' => $vendorId
            ]);
            break;
            
        case 'update':
            $vendorId = intval($_POST['vendor_id']);
            
            if (!$vendorId) {
                throw new Exception('Vendor ID is required');
            }
            
            // Check if vendor exists
            $existingVendor = $vendorModel->find($vendorId);
            if (!$existingVendor) {
                throw new Exception('Vendor not found');
            }
            
            // Check if vendor code already exists (excluding current vendor)
            if (!empty($_POST['vendor_code']) && $_POST['vendor_code'] !== $existingVendor['vendor_code']) {
                $existing = $vendorModel->findByVendorCode($_POST['vendor_code']);
                if ($existing) {
                    throw new Exception('Vendor code already exists');
                }
            }
            
            // Prepare vendor data
            $vendorData = [
                'vendor_code' => $_POST['vendor_code'] ?? null,
                'name' => $_POST['vendorName'],
                'company_name' => $_POST['company_name'] ?? null,
                'email' => $_POST['email'] ?? null,
                'phone' => $_POST['contact_number'] ?? null,
                'address' => $_POST['address'] ?? null,
                'mobility_id' => $_POST['mobility_id'] ?? null,
                'contact_person' => $_POST['vendorName'], // Use name as contact person for now
                'bank_name' => $_POST['bank_name'] ?? null,
                'account_number' => $_POST['account_number'] ?? null,
                'ifsc_code' => $_POST['ifsc_code'] ?? null,
                'gst_number' => $_POST['gst_number'] ?? null,
                'pan_card_number' => $_POST['pan_card_number'] ?? null,
                'aadhaar_number' => $_POST['aadhaar_number'] ?? null,
                'msme_number' => $_POST['msme_number'] ?? null,
                'esic_number' => $_POST['esic_number'] ?? null,
                'pf_number' => $_POST['pf_number'] ?? null,
                'pvc_status' => $_POST['pvc_status'] ?? 'No'
            ];
            
            // Only include password if provided
            if (!empty($_POST['mobility_password'])) {
                $vendorData['mobility_password'] = $_POST['mobility_password'];
            }
            
            // Handle file uploads
            if (isset($_FILES['experience_letter']) && $_FILES['experience_letter']['error'] === UPLOAD_ERR_OK) {
                try {
                    $vendorData['experience_letter_path'] = $vendorModel->uploadFile($_FILES['experience_letter'], 'experience_letter', $vendorId);
                } catch (Exception $e) {
                    // Log error but don't fail the update
                    error_log('Experience letter upload failed: ' . $e->getMessage());
                }
            }
            
            if (isset($_FILES['photograph']) && $_FILES['photograph']['error'] === UPLOAD_ERR_OK) {
                try {
                    $vendorData['photograph_path'] = $vendorModel->uploadFile($_FILES['photograph'], 'photograph', $vendorId);
                } catch (Exception $e) {
                    // Log error but don't fail the update
                    error_log('Photograph upload failed: ' . $e->getMessage());
                }
            }
            
            $result = $vendorModel->update($vendorId, $vendorData);
            
            if (!$result) {
                throw new Exception('Failed to update vendor');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Vendor updated successfully'
            ]);
            break;
            
        case 'delete':
            $vendorId = intval($_POST['vendor_id']);
            
            if (!$vendorId) {
                throw new Exception('Vendor ID is required');
            }
            
            // Check if vendor exists
            $existingVendor = $vendorModel->find($vendorId);
            if (!$existingVendor) {
                throw new Exception('Vendor not found');
            }
            
            // Check if vendor has active delegations
            $delegations = $vendorModel->getVendorDelegations($vendorId, 'active');
            if (!empty($delegations)) {
                throw new Exception('Cannot delete vendor with active site delegations');
            }
            
            // Soft delete by setting status to inactive
            $result = $vendorModel->update($vendorId, ['status' => 'inactive']);
            
            if (!$result) {
                throw new Exception('Failed to delete vendor');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Vendor deleted successfully'
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
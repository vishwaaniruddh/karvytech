<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/SiteDelegation.php';
require_once __DIR__ . '/../../models/DelegationLayout.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    $delegationModel = new SiteDelegation();
    $action = $_POST['action'] ?? 'delegate';
    
    switch ($action) {
        case 'delegate':
            $siteId = $_POST['site_id'] ?? null;
            $vendorId = $_POST['vendor_id'] ?? null;
            $notes = $_POST['notes'] ?? '';
            
            if (!$siteId || !$vendorId) {
                throw new Exception('Site ID and Vendor ID are required');
            }
            
            $delegationId = $delegationModel->delegateSite($siteId, $vendorId, Auth::getUserId(), $notes);
            
            // Handle multiple file uploads if present
            $layoutsUploaded = 0;
            $uploadedPaths = [];
            
            if (isset($_FILES['layout_files']) && is_array($_FILES['layout_files']['name'])) {
                $layoutModel = new DelegationLayout();
                $remarks = $_POST['layout_remarks'] ?? '';
                
                $fileCount = count($_FILES['layout_files']['name']);
                
                for ($i = 0; $i < $fileCount; $i++) {
                    // Check if file was uploaded
                    if ($_FILES['layout_files']['error'][$i] === UPLOAD_ERR_OK) {
                        try {
                            // Create individual file array for each upload
                            $file = [
                                'name' => $_FILES['layout_files']['name'][$i],
                                'type' => $_FILES['layout_files']['type'][$i],
                                'tmp_name' => $_FILES['layout_files']['tmp_name'][$i],
                                'error' => $_FILES['layout_files']['error'][$i],
                                'size' => $_FILES['layout_files']['size'][$i]
                            ];
                            
                            $layoutId = $layoutModel->uploadLayout($delegationId, $file, $remarks, Auth::getUserId());
                            $layoutsUploaded++;
                            
                            // Get the uploaded file path
                            $layoutInfo = $layoutModel->find($layoutId);
                            if ($layoutInfo) {
                                $uploadedPaths[] = $layoutInfo['file_path'];
                            }
                        } catch (Exception $e) {
                            // Log the error but continue with other files
                            error_log("Layout upload error for delegation {$delegationId}, file {$i}: " . $e->getMessage());
                        }
                    }
                }
            }
            
            $message = 'Site delegated successfully';
            if ($layoutsUploaded > 0) {
                $message .= " with {$layoutsUploaded} layout file(s)";
            }
            
            echo json_encode([
                'success' => true,
                'message' => $message,
                'delegation_id' => $delegationId,
                'layouts_uploaded' => $layoutsUploaded,
                'file_paths' => implode(',', $uploadedPaths)
            ]);
            break;
            
        case 'complete':
            $delegationId = $_POST['delegation_id'] ?? null;
            
            if (!$delegationId) {
                throw new Exception('Delegation ID is required');
            }
            
            $delegationModel->completeDelegation($delegationId, Auth::getUserId());
            
            echo json_encode([
                'success' => true,
                'message' => 'Delegation marked as completed'
            ]);
            break;
            
        case 'cancel':
            $delegationId = $_POST['delegation_id'] ?? null;
            
            if (!$delegationId) {
                throw new Exception('Delegation ID is required');
            }
            
            $delegationModel->cancelDelegation($delegationId, Auth::getUserId());
            
            echo json_encode([
                'success' => true,
                'message' => 'Delegation cancelled successfully'
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
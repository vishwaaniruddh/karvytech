<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

if (!isset($_FILES['import_file'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No file uploaded'
    ]);
    exit;
}

$file = $_FILES['import_file'];

// Validate file
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'message' => 'File upload error'
    ]);
    exit;
}

$allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid file type. Please upload CSV or Excel file.'
    ]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $currentUser = Auth::getCurrentUser();
    
    // Handle CSV files
    if ($file['type'] === 'text/csv') {
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            throw new Exception('Could not open CSV file');
        }
        
        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            throw new Exception('Invalid CSV format - no headers found');
        }
        
        // Validate required columns
        $requiredColumns = ['site_id', 'location', 'customer_id', 'city_id', 'state_id'];
        $missingColumns = array_diff($requiredColumns, $headers);
        if (!empty($missingColumns)) {
            throw new Exception('Missing required columns: ' . implode(', ', $missingColumns));
        }
        
        // Map column positions
        $columnMap = array_flip($headers);
        
        $db->beginTransaction();
        $importedCount = 0;
        $errors = [];
        
        while (($row = fgetcsv($handle)) !== false) {
            try {
                // Extract data using column mapping
                $siteId = $row[$columnMap['site_id']] ?? '';
                $location = $row[$columnMap['location']] ?? '';
                $customerId = $row[$columnMap['customer_id']] ?? '';
                $cityId = $row[$columnMap['city_id']] ?? '';
                $stateId = $row[$columnMap['state_id']] ?? '';
                
                // Validate required fields
                if (empty($siteId) || empty($location) || empty($customerId)) {
                    $errors[] = "Row skipped: Missing required data for site_id: {$siteId}";
                    continue;
                }
                
                // Check if site already exists
                $checkStmt = $db->prepare("SELECT id FROM sites WHERE site_id = ?");
                $checkStmt->execute([$siteId]);
                if ($checkStmt->fetch()) {
                    $errors[] = "Site {$siteId} already exists - skipped";
                    continue;
                }
                
                // Insert site
                $insertStmt = $db->prepare("
                    INSERT INTO sites (site_id, location, customer_id, city_id, state_id, status, created_by, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW(), NOW())
                ");
                
                $insertStmt->execute([
                    $siteId,
                    $location,
                    $customerId,
                    $cityId ?: null,
                    $stateId ?: null,
                    $currentUser['id']
                ]);
                
                $newSiteId = $db->lastInsertId();
                
                // Log the import
                $logStmt = $db->prepare("
                    INSERT INTO audit_logs (user_id, action, table_name, record_id, details, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                
                $details = json_encode([
                    'action' => 'bulk_import',
                    'source' => 'csv_upload',
                    'site_id' => $siteId,
                    'bulk_operation' => true
                ]);
                
                $logStmt->execute([
                    $currentUser['id'],
                    'create',
                    'sites',
                    $newSiteId,
                    $details
                ]);
                
                $importedCount++;
                
            } catch (Exception $e) {
                $errors[] = "Error importing site {$siteId}: " . $e->getMessage();
            }
        }
        
        fclose($handle);
        $db->commit();
        
        $response = [
            'success' => true,
            'message' => "Successfully imported {$importedCount} sites",
            'imported_count' => $importedCount
        ];
        
        if (!empty($errors)) {
            $response['warnings'] = $errors;
        }
        
        echo json_encode($response);
        
    } else {
        // For Excel files, you would need a library like PhpSpreadsheet
        echo json_encode([
            'success' => false,
            'message' => 'Excel file import not yet implemented. Please use CSV format.'
        ]);
    }
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Import failed: ' . $e->getMessage()
    ]);
}
?>
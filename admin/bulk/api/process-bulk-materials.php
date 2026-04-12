<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../models/BoqItem.php';

// Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

if (!isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];
$itemModel = new BoqItem();
$results = ['success' => true, 'created' => 0, 'updated' => 0, 'failed' => 0, 'rows' => []];

try {
    if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ","); // Skip headers
        
        $rowNum = 1;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $rowNum++;
            $name = trim($data[0] ?? '');
            $code = trim($data[1] ?? '');
            $unit = trim($data[2] ?? '');
            $desc = trim($data[3] ?? '');
            $cat  = trim($data[4] ?? '');
            $serial = strtolower(trim($data[5] ?? 'no'));

            if (empty($name) && empty($code)) continue;

            $rowResult = [
                'row' => $rowNum, 
                'name' => $name, 
                'code' => $code, 
                'status' => 'success', 
                'action' => 'create', 
                'message' => ''
            ];

            try {
                if (empty($name) || empty($code) || empty($unit)) {
                    throw new Exception("Missing required fields (Name, Code, or Unit)");
                }

                $existing = $itemModel->findByItemCode($code);
                
                $itemData = [
                    'item_name' => $name,
                    'item_code' => $code,
                    'unit' => $unit,
                    'description' => $desc,
                    'category' => $cat,
                    'need_serial_number' => ($serial === 'yes' || $serial === '1' || $serial === 'y') ? 1 : 0,
                    'status' => 'active'
                ];

                if ($existing) {
                    $rowResult['action'] = 'update';
                    $itemModel->update($existing['id'], $itemData);
                    $results['updated']++;
                    $rowResult['message'] = "Item updated successfully";
                } else {
                    $itemModel->create($itemData);
                    $results['created']++;
                    $rowResult['message'] = "New item added to master";
                }
            } catch (Exception $e) {
                $results['failed']++;
                $rowResult['status'] = 'failed';
                $rowResult['message'] = $e->getMessage();
            }
            $results['rows'][] = $rowResult;
        }
        fclose($handle);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

echo json_encode($results);

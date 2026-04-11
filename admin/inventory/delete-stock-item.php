
<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/Inventory.php';

Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$itemId = $_POST['item_id'] ?? null;
$remark = trim($_POST['remark'] ?? '');

if (empty($itemId)) {
    echo json_encode(['success' => false, 'message' => 'Item ID required']);
    exit;
}

if ($remark === '') {
    echo json_encode(['success' => false, 'message' => 'Delete remark is required']);
    exit;
}

try {
    $inventory = new Inventory();

    // ✅ updated call
    $result = $inventory->deleteIndividualStock(
        (int)$itemId,
        $remark
    );

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Delete failed']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}









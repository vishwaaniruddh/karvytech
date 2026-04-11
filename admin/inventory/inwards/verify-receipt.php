<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['receipt_id'])) {
        throw new Exception('Receipt ID is required');
    }
    
    $receiptId = intval($input['receipt_id']);
    $currentUser = Auth::getCurrentUser();
    
    $db = Database::getInstance()->getConnection();
    
    // Update receipt status to verified
    $sql = "UPDATE inventory_inwards 
            SET status = 'verified', verified_by = ?, updated_at = NOW()
            WHERE id = ? AND status = 'pending'";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([$currentUser['id'], $receiptId]);
    
    if (!$result || $stmt->rowCount() === 0) {
        throw new Exception('Receipt not found or already verified');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Receipt verified successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
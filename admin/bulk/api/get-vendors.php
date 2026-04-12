<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    
    // Get all active vendors
    $stmt = $db->query("
        SELECT id, name, company_name, email, phone, status
        FROM vendors 
        WHERE status = 'active'
        ORDER BY name ASC
    ");
    $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'vendors' => $vendors
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load vendors: ' . $e->getMessage()
    ]);
}
?>
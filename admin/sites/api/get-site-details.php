<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';
Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

try {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        throw new Exception('Site ID is required');
    }

    $db = Database::getInstance()->getConnection();
    
    // Fetch site details with related master names
    $stmt = $db->prepare("
        SELECT s.*, 
               c.name as customer_name,
               st.name as state_name,
               ci.name as city_name,
               co.name as country_name,
               b.name as bank_name
        FROM sites s
        LEFT JOIN customers c ON s.customer_id = c.id
        LEFT JOIN states st ON s.state_id = st.id
        LEFT JOIN cities ci ON s.city_id = ci.id
        LEFT JOIN countries co ON s.country_id = co.id
        LEFT JOIN banks b ON s.bank_id = b.id
        WHERE s.id = ?
    ");
    $stmt->execute([$id]);
    $site = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$site) {
        throw new Exception('Site not found');
    }

    echo json_encode([
        'success' => true,
        'data' => $site
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

<?php
require_once __DIR__ . '/../config/database.php';
try {
    $db = Database::getInstance()->getConnection();
    $boqItemId = 3; // 12U Rack
    
    $sql = "SELECT 
                COALESCE(SUM(CASE WHEN item_status = 'available' AND quality_status = 'good' THEN 1 ELSE 0 END), 0) as available_qty,
                bi.item_name as db_item_name
            FROM inventory_stock ist
            RIGHT JOIN boq_items bi ON ist.boq_item_id = bi.id
            WHERE bi.id = ?
            GROUP BY bi.id";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([$boqItemId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "--- Query Result for ID $boqItemId ---\n";
    print_r($result);
    
} catch (Exception $e) {
    echo $e->getMessage();
}
?>

<?php
require_once __DIR__ . '/../config/database.php';
try {
    $db = Database::getInstance()->getConnection();
    
    echo "--- 1. Bulk Item Example (Cable Ties ID 16) ---\n";
    $stmt = $db->query("SELECT id, boq_item_id, serial_number, current_stock, available_stock FROM inventory_stock WHERE boq_item_id = 16 LIMIT 5");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    echo "\n--- 2. View Definition (inventory_summary) ---\n";
    try {
        $stmt = $db->query("SHOW CREATE VIEW inventory_summary");
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        echo $res['Create View'] . "\n";
    } catch (Exception $e) { echo "No view found or no access: " . $e->getMessage() . "\n"; }

    echo "\n--- 3. Total Items for Request 90 (Costs) ---\n";
    // Get all boq_item_ids from a sample request if needed
    
} catch (Exception $e) {
    echo $e->getMessage();
}
?>

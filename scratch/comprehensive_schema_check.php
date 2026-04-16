<?php
require_once __DIR__ . '/../config/database.php';
try {
    $db = Database::getInstance()->getConnection();
    $tables = ['inventory_stock', 'inventory_dispatches', 'inventory_dispatch_items'];
    foreach ($tables as $table) {
        echo "\n--- DESCRIBE $table ---\n";
        try {
            $stmt = $db->query("DESCRIBE $table");
            if ($stmt) {
                print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
            } else {
                echo "Table $table does not exist or DESCRIBE failed.\n";
            }
        } catch (Exception $e) {
            echo "Error describing $table: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n--- Sample inventory_summary row ---\n";
    try {
        $stmt = $db->query("SELECT * FROM inventory_summary LIMIT 1");
        print_r($stmt->fetch(PDO::FETCH_ASSOC));
    } catch (Exception $e) { echo "No view found: " . $e->getMessage() . "\n"; }

} catch (Exception $e) {
    echo $e->getMessage();
}
?>

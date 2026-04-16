<?php
require_once __DIR__ . '/../config/database.php';
try {
    $db = Database::getInstance()->getConnection();
    $tables = ['inventory_dispatches', 'inventory_dispatch_items'];
    foreach ($tables as $table) {
        echo "\n--- $table ---\n";
        $stmt = $db->query("DESCRIBE $table");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>

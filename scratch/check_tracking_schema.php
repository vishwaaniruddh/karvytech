<?php
require_once __DIR__ . '/../config/database.php';
try {
    $db = Database::getInstance()->getConnection();
    echo "--- inventory_tracking schema ---\n";
    $stmt = $db->query("DESCRIBE inventory_tracking");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) { echo $e->getMessage(); }
?>

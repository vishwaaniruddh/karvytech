<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

try {
    $stmt = $db->query("DESCRIBE inventory_dispatches");
    echo "--- inventory_dispatches ---\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

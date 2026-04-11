<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $output = "--- TABLES ---\n";
    $stmt = $db->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $output .= $row[0] . "\n";
    }
    
    file_put_contents(__DIR__ . '/table_list.txt', $output);
    echo "Table list created in table_list.txt\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

echo "Material-related tables:\n";
echo "========================\n";

$stmt = $db->query("SHOW TABLES LIKE '%material%'");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo $row[0] . "\n";
}

echo "\nRequest-related tables:\n";
echo "=======================\n";

$stmt = $db->query("SHOW TABLES LIKE '%request%'");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo $row[0] . "\n";
}

echo "\nAll tables:\n";
echo "===========\n";

$stmt = $db->query("SHOW TABLES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    if (strpos($row[0], 'material') !== false || strpos($row[0], 'request') !== false) {
        echo "*** " . $row[0] . " ***\n";
    } else {
        echo $row[0] . "\n";
    }
}
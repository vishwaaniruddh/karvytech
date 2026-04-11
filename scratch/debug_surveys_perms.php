<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT p.id, p.name, p.display_name, m.name as module_name FROM permissions p JOIN modules m ON p.module_id = m.id WHERE m.name = 'surveys'");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . PHP_EOL;
}

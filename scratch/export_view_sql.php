<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

$stmt = $db->query("SHOW CREATE VIEW inventory_summary");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
file_put_contents('scratch/view_sql.txt', $row['Create View']);
echo "View SQL saved to scratch/view_sql.txt\n";

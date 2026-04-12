<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

echo "User Status Distribution:\n";
$stmt = $db->query("SELECT status, COUNT(*) as count FROM users GROUP BY status");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['status']}: {$row['count']}\n";
}

echo "\nSample Inactive Users:\n";
$stmt = $db->query("SELECT id, username, status FROM users WHERE status != 'active' LIMIT 5");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?> contraband 403面向对象

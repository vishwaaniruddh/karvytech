<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id, parent_id, title, url, sort_order FROM menu_items WHERE title = 'Survey' OR parent_id = (SELECT id FROM menu_items WHERE title = 'Survey' AND parent_id IS NULL) ORDER BY parent_id, sort_order");

echo "Survey Menu Structure:\n";
echo "======================\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("ID: %d, Parent: %s, Title: %s, URL: %s\n", 
        $row['id'], 
        $row['parent_id'] ?: 'NULL', 
        $row['title'], 
        $row['url'] ?: 'NULL'
    );
}
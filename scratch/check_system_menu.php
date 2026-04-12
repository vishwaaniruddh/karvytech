<?php
require_once __DIR__ . '/../config/database.php';
$conn = Database::getInstance()->getConnection();
print_r($conn->query("SELECT * FROM menu_items WHERE title LIKE '%System%' OR url LIKE '%system%'")->fetchAll(PDO::FETCH_ASSOC));
?>

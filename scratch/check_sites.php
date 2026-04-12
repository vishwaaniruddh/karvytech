<?php
require_once __DIR__ . '/../config/database.php';
$conn = Database::getInstance()->getConnection();
print_r($conn->query("DESCRIBE sites")->fetchAll(PDO::FETCH_ASSOC));
?>

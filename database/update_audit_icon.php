<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('UPDATE menu_items SET icon = ? WHERE title = ? AND url LIKE ?');
    $stmt->execute(['audit', 'Quantity Audits', '%quantity-audits.php']);
    echo 'Updated Quantity Audits menu icon to audit icon.';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    $sql = "ALTER TABLE roles MODIFY COLUMN status ENUM('active', 'inactive', 'pending_deletion') DEFAULT 'active'";
    $db->query($sql);
    echo "Successfully updated roles status enum to include 'pending_deletion'\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

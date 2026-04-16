<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Add role_category column
    echo "Adding role_category column to roles table...\n";
    $db->exec("ALTER TABLE roles ADD COLUMN role_category ENUM('internal', 'external') NOT NULL DEFAULT 'internal' AFTER display_name");
    
    // 2. Update existing roles
    echo "Updating existing roles...\n";
    
    // Internal roles
    $internalRoles = ['superadmin', 'admin', 'manager', 'accountmanager', 'Inventory'];
    $placeholders = implode(',', array_fill(0, count($internalRoles), '?'));
    $stmt = $db->prepare("UPDATE roles SET role_category = 'internal' WHERE name IN ($placeholders)");
    $stmt->execute($internalRoles);
    
    // External roles
    $externalRoles = ['contractor'];
    $placeholders = implode(',', array_fill(0, count($externalRoles), '?'));
    $stmt = $db->prepare("UPDATE roles SET role_category = 'external' WHERE name IN ($placeholders)");
    $stmt->execute($externalRoles);
    
    echo "Migration completed successfully.\n";
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}

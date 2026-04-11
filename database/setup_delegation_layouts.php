<?php
/**
 * Setup script for delegation_layouts table
 * Run this script to create the delegation_layouts table
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/create_delegation_layouts_table.sql');
    
    $db->exec($sql);
    
    echo "✓ delegation_layouts table created successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Error creating delegation_layouts table: " . $e->getMessage() . "\n";
    exit(1);
}
?>

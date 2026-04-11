<?php
/**
 * Update installation_progress_attachments table to support daily work attachments
 * This script adds new attachment types for daily work progress tracking
 */

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getInstance()->getConnection();
    
    echo "Updating installation_progress_attachments table...\n";
    
    // Update the ENUM to include new attachment types
    $sql = "ALTER TABLE installation_progress_attachments 
            MODIFY COLUMN attachment_type ENUM(
                'final_report', 
                'site_snap', 
                'excel_sheet', 
                'drawing_attachment', 
                'daily_work_site',
                'daily_work_material',
                'other'
            ) NOT NULL";
    
    $pdo->exec($sql);
    echo "✓ Updated attachment_type ENUM to include daily work types\n";
    
    // Add index for better query performance
    $sql = "CREATE INDEX IF NOT EXISTS idx_attachment_type_installation 
            ON installation_progress_attachments(attachment_type, installation_id)";
    
    $pdo->exec($sql);
    echo "✓ Added index for better query performance\n";
    
    // Verify the changes
    $stmt = $pdo->query("SHOW COLUMNS FROM installation_progress_attachments LIKE 'attachment_type'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\nCurrent attachment_type definition:\n";
    echo "Type: " . $column['Type'] . "\n";
    
    if (strpos($column['Type'], 'daily_work_site') !== false && 
        strpos($column['Type'], 'daily_work_material') !== false) {
        echo "\n✓ SUCCESS: Daily work attachment types are now available!\n";
        echo "\nYou can now upload:\n";
        echo "  - daily_work_site: Overall site progress photos/videos\n";
        echo "  - daily_work_material: Individual material usage photos/videos\n";
    } else {
        echo "\n✗ WARNING: Daily work types may not have been added correctly\n";
    }
    
} catch (PDOException $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>

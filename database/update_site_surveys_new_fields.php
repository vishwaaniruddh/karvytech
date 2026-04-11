<?php
/**
 * Migration script to add new fields to site_surveys table
 * Run this script to update the database schema for the enhanced survey form
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Starting site_surveys table migration...\n\n";
    
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/update_site_surveys_new_fields.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $db->exec($statement);
            $successCount++;
            
            // Extract column name from ALTER TABLE statement for better feedback
            if (preg_match('/ADD COLUMN.*?(\w+)\s+/i', $statement, $matches)) {
                echo "✓ Added column: {$matches[1]}\n";
            } elseif (preg_match('/MODIFY COLUMN\s+(\w+)/i', $statement, $matches)) {
                echo "✓ Modified column: {$matches[1]}\n";
            } elseif (preg_match('/CREATE INDEX.*?(\w+)/i', $statement, $matches)) {
                echo "✓ Created index: {$matches[1]}\n";
            } else {
                echo "✓ Executed statement successfully\n";
            }
        } catch (PDOException $e) {
            // Check if error is because column already exists
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                if (preg_match('/ADD COLUMN.*?(\w+)\s+/i', $statement, $matches)) {
                    echo "⊙ Column already exists: {$matches[1]}\n";
                }
            } elseif (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                if (preg_match('/CREATE INDEX.*?(\w+)/i', $statement, $matches)) {
                    echo "⊙ Index already exists: {$matches[1]}\n";
                }
            } else {
                echo "✗ Error: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Migration completed!\n";
    echo "Successful operations: $successCount\n";
    if ($errorCount > 0) {
        echo "Errors encountered: $errorCount\n";
    }
    echo str_repeat("=", 50) . "\n\n";
    
    // Verify the new columns exist
    echo "Verifying new columns...\n";
    $stmt = $db->query("DESCRIBE site_surveys");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $newColumns = [
        'checkin_datetime', 'checkout_datetime', 'working_hours', 'store_model',
        'floor_height', 'ceiling_type', 'floor_height_photos', 'floor_height_photo_remarks',
        'ceiling_photos', 'ceiling_photo_remarks', 'total_cameras', 'slp_cameras',
        'analytic_cameras', 'analytic_photos', 'analytic_photos_remarks',
        'existing_poe_rack', 'existing_poe_photos', 'existing_poe_photos_remarks',
        'space_new_rack', 'space_new_rack_photos', 'space_new_rack_photo_remarks',
        'new_poe_rack', 'new_poe_photos', 'new_poe_photos_remarks',
        'zones_recommended', 'rrl_delivery_status', 'rrl_photos', 'rrl_photos_remarks',
        'kptl_space', 'kptl_photos', 'kptl_photos_remarks',
        'site_accessibility_others', 'nos_of_ladder', 'ladder_size', 'site_photos_remarks'
    ];
    
    $missingColumns = [];
    foreach ($newColumns as $col) {
        if (in_array($col, $columns)) {
            echo "✓ Column exists: $col\n";
        } else {
            echo "✗ Column missing: $col\n";
            $missingColumns[] = $col;
        }
    }
    
    if (empty($missingColumns)) {
        echo "\n✓ All new columns have been added successfully!\n";
        echo "✓ The site_surveys table is now ready for the enhanced survey form.\n";
    } else {
        echo "\n⚠ Warning: Some columns are missing. Please check the errors above.\n";
    }
    
} catch (Exception $e) {
    echo "✗ Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

<?php
// Setup script to update vendors table with enhanced fields
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Setting up enhanced vendors table...\n";
    
    // Check if the new columns already exist
    $stmt = $db->query("DESCRIBE vendors");
    $existingColumns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existingColumns[] = $row['Field'];
    }
    
    // List of new columns to add
    $newColumns = [
        'vendor_code' => 'VARCHAR(50) UNIQUE AFTER id',
        'mobility_id' => 'VARCHAR(100) AFTER vendor_code',
        'mobility_password' => 'VARCHAR(255) AFTER mobility_id',
        'company_name' => 'VARCHAR(255) AFTER name',
        'bank_name' => 'VARCHAR(255) AFTER address',
        'account_number' => 'VARCHAR(50) AFTER bank_name',
        'ifsc_code' => 'VARCHAR(20) AFTER account_number',
        'gst_number' => 'VARCHAR(50) AFTER ifsc_code',
        'pan_card_number' => 'VARCHAR(20) AFTER gst_number',
        'aadhaar_number' => 'VARCHAR(20) AFTER pan_card_number',
        'msme_number' => 'VARCHAR(50) AFTER aadhaar_number',
        'esic_number' => 'VARCHAR(50) AFTER msme_number',
        'pf_number' => 'VARCHAR(50) AFTER esic_number',
        'pvc_status' => 'ENUM(\'Yes\', \'No\') DEFAULT \'No\' AFTER pf_number',
        'experience_letter_path' => 'VARCHAR(500) AFTER pvc_status',
        'photograph_path' => 'VARCHAR(500) AFTER experience_letter_path'
    ];
    
    $columnsAdded = 0;
    
    foreach ($newColumns as $columnName => $columnDefinition) {
        if (!in_array($columnName, $existingColumns)) {
            try {
                $sql = "ALTER TABLE vendors ADD COLUMN $columnName $columnDefinition";
                $db->exec($sql);
                echo "✓ Added column: $columnName\n";
                $columnsAdded++;
            } catch (PDOException $e) {
                echo "❌ Failed to add column $columnName: " . $e->getMessage() . "\n";
            }
        } else {
            echo "- Column $columnName already exists\n";
        }
    }
    
    // Add indexes if columns were added
    if ($columnsAdded > 0) {
        try {
            // Check if indexes exist before adding
            $indexQueries = [
                "ALTER TABLE vendors ADD INDEX idx_vendor_code (vendor_code)",
                "ALTER TABLE vendors ADD INDEX idx_gst_number (gst_number)",
                "ALTER TABLE vendors ADD INDEX idx_pan_card (pan_card_number)"
            ];
            
            foreach ($indexQueries as $indexQuery) {
                try {
                    $db->exec($indexQuery);
                    echo "✓ Added index\n";
                } catch (PDOException $e) {
                    // Index might already exist, ignore
                    if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                        echo "❌ Index error: " . $e->getMessage() . "\n";
                    }
                }
            }
        } catch (Exception $e) {
            echo "Warning: Could not add indexes: " . $e->getMessage() . "\n";
        }
        
        // Update existing vendors with vendor codes
        try {
            $stmt = $db->query("SELECT id FROM vendors WHERE vendor_code IS NULL OR vendor_code = ''");
            $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($vendors as $vendor) {
                $vendorCode = 'VND' . str_pad($vendor['id'], 4, '0', STR_PAD_LEFT);
                $updateStmt = $db->prepare("UPDATE vendors SET vendor_code = ? WHERE id = ?");
                $updateStmt->execute([$vendorCode, $vendor['id']]);
            }
            
            if (count($vendors) > 0) {
                echo "✓ Updated " . count($vendors) . " vendors with vendor codes\n";
            }
        } catch (Exception $e) {
            echo "Warning: Could not update vendor codes: " . $e->getMessage() . "\n";
        }
    }
    
    // Create uploads directory
    $uploadsDir = __DIR__ . '/../uploads/vendors';
    if (!is_dir($uploadsDir)) {
        if (mkdir($uploadsDir, 0755, true)) {
            echo "✓ Created uploads directory: $uploadsDir\n";
        } else {
            echo "❌ Failed to create uploads directory: $uploadsDir\n";
        }
    } else {
        echo "- Uploads directory already exists\n";
    }
    
    echo "\n=== Setup Complete ===\n";
    if ($columnsAdded > 0) {
        echo "✅ Enhanced vendors table with $columnsAdded new columns\n";
        echo "✅ Added indexes for better performance\n";
        echo "✅ Updated existing vendors with vendor codes\n";
    } else {
        echo "✅ Vendors table already has all required columns\n";
    }
    echo "✅ Uploads directory is ready\n";
    echo "\nYou can now use the enhanced vendor management system!\n";
    
} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    echo "Please check your database connection and try again.\n";
}
?>
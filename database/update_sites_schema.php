<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Updating sites table schema...\n";
    
    // Disable foreign key checks temporarily
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Drop the old sites table if it exists
    $db->exec("DROP TABLE IF EXISTS sites");
    echo "✓ Dropped old sites table\n";
    
    // Re-enable foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Create new sites table with updated schema
    $createTableSQL = "
    CREATE TABLE sites (
        id INT AUTO_INCREMENT PRIMARY KEY,
        site_id VARCHAR(255) NOT NULL,
        store_id VARCHAR(255),
        location TEXT,
        city VARCHAR(60),
        state VARCHAR(60),
        country VARCHAR(60),
        branch VARCHAR(60),
        remarks TEXT,
        po_number VARCHAR(255),
        po_date DATE,
        customer VARCHAR(255),
        bank VARCHAR(255),
        vendor VARCHAR(255),
        activity_status VARCHAR(100),
        is_delegate BOOLEAN DEFAULT FALSE,
        delegated_vendor VARCHAR(255),
        survey_status BOOLEAN DEFAULT FALSE,
        installation_status BOOLEAN DEFAULT FALSE,
        is_material_request_generated BOOLEAN DEFAULT FALSE,
        survey_submission_date DATETIME,
        installation_date DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_by VARCHAR(255),
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by VARCHAR(255),
        INDEX idx_site_id (site_id),
        INDEX idx_store_id (store_id),
        INDEX idx_city (city),
        INDEX idx_state (state),
        INDEX idx_vendor (vendor),
        INDEX idx_activity_status (activity_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $db->exec($createTableSQL);
    echo "✓ Created new sites table with updated schema\n";
    
    // Insert sample data
    $sampleData = [
        [
            'site_id' => 'SITE001',
            'store_id' => 'STORE001',
            'location' => 'Shop No. 15, Ground Floor, ABC Mall, Main Road',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'country' => 'India',
            'branch' => 'Andheri West',
            'po_number' => 'PO2024001',
            'po_date' => '2024-01-15',
            'customer' => 'ABC Corporation',
            'bank' => 'HDFC Bank',
            'vendor' => 'Tech Solutions Pvt Ltd',
            'activity_status' => 'In Progress',
            'created_by' => 'admin'
        ],
        [
            'site_id' => 'SITE002',
            'store_id' => 'STORE002',
            'location' => 'Unit 25, Second Floor, XYZ Complex, Commercial Street',
            'city' => 'Bangalore',
            'state' => 'Karnataka',
            'country' => 'India',
            'branch' => 'Koramangala',
            'po_number' => 'PO2024002',
            'po_date' => '2024-01-20',
            'customer' => 'XYZ Enterprises',
            'bank' => 'ICICI Bank',
            'vendor' => 'Network Systems Ltd',
            'activity_status' => 'Pending',
            'survey_status' => 1,
            'created_by' => 'admin'
        ],
        [
            'site_id' => 'SITE003',
            'location' => 'Ground Floor, DEF Building, Park Street',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'country' => 'India',
            'customer' => 'DEF Limited',
            'bank' => 'SBI',
            'vendor' => 'Installation Pro',
            'activity_status' => 'Completed',
            'survey_status' => 1,
            'installation_status' => 1,
            'installation_date' => '2024-01-25 14:30:00',
            'created_by' => 'admin'
        ]
    ];
    
    $insertSQL = "INSERT INTO sites (site_id, store_id, location, city, state, country, branch, po_number, po_date, customer, bank, vendor, activity_status, survey_status, installation_status, installation_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($insertSQL);
    
    foreach ($sampleData as $site) {
        $stmt->execute([
            $site['site_id'],
            $site['store_id'] ?? null,
            $site['location'],
            $site['city'],
            $site['state'],
            $site['country'],
            $site['branch'] ?? null,
            $site['po_number'] ?? null,
            $site['po_date'] ?? null,
            $site['customer'] ?? null,
            $site['bank'] ?? null,
            $site['vendor'] ?? null,
            $site['activity_status'] ?? null,
            $site['survey_status'] ?? 0,
            $site['installation_status'] ?? 0,
            $site['installation_date'] ?? null,
            $site['created_by']
        ]);
    }
    
    echo "✓ Inserted sample site data\n";
    echo "\nSites table schema updated successfully!\n";
    
} catch (Exception $e) {
    echo "Error updating sites schema: " . $e->getMessage() . "\n";
}
?>
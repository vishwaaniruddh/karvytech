<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Creating BOQ Master table...\n";
    
    // Create BOQ Master table
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS boq_master (
        boq_id INT PRIMARY KEY AUTO_INCREMENT,
        boq_name VARCHAR(200) NOT NULL,
        is_serial_number_required BOOLEAN DEFAULT FALSE,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_by INT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        updated_by INT NULL,
        INDEX idx_boq_name (boq_name),
        INDEX idx_status (status),
        INDEX idx_created_by (created_by),
        INDEX idx_updated_by (updated_by),
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
    )";
    
    $db->exec($createTableSQL);
    echo "BOQ Master table created successfully.\n";
    
    // Insert sample data
    $sampleData = [
        ['Network Equipment', true, 'active'],
        ['Installation Materials', false, 'active'],
        ['Testing Equipment', true, 'active'],
        ['Cables & Accessories', false, 'active'],
        ['Power Equipment', true, 'active']
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO boq_master (boq_name, is_serial_number_required, status, created_by) VALUES (?, ?, ?, 1)");
    
    foreach ($sampleData as $data) {
        $stmt->execute([$data[0], $data[1], $data[2]]);
    }
    
    echo "Sample BOQ data inserted successfully.\n";
    echo "BOQ Master setup completed!\n";
    
} catch (Exception $e) {
    echo "Error setting up BOQ Master: " . $e->getMessage() . "\n";
    exit(1);
}
?>
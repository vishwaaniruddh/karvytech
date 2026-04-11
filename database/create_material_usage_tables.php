<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Creating material usage tracking tables...\n";
    
    // Create installation_materials table (tracks materials assigned to installation)
    $sql1 = "CREATE TABLE IF NOT EXISTS installation_materials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        installation_id INT NOT NULL,
        material_name VARCHAR(255) NOT NULL,
        material_unit VARCHAR(50) NOT NULL DEFAULT 'Nos',
        total_quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
        used_quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
        remaining_quantity DECIMAL(10,2) GENERATED ALWAYS AS (total_quantity - used_quantity) STORED,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        FOREIGN KEY (installation_id) REFERENCES installation_delegations(id) ON DELETE CASCADE,
        INDEX idx_installation_id (installation_id)
    )";
    
    $db->exec($sql1);
    echo "✓ installation_materials table created\n";
    
    // Create daily_material_usage table (tracks daily material consumption)
    $sql2 = "CREATE TABLE IF NOT EXISTS daily_material_usage (
        id INT AUTO_INCREMENT PRIMARY KEY,
        installation_id INT NOT NULL,
        material_id INT NOT NULL,
        day_number INT NOT NULL,
        work_date DATE NOT NULL,
        engineer_name VARCHAR(255),
        quantity_used DECIMAL(10,2) NOT NULL DEFAULT 0,
        remarks TEXT,
        work_report TEXT,
        is_checked_out BOOLEAN DEFAULT FALSE,
        checked_out_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        FOREIGN KEY (installation_id) REFERENCES installation_delegations(id) ON DELETE CASCADE,
        FOREIGN KEY (material_id) REFERENCES installation_materials(id) ON DELETE CASCADE,
        INDEX idx_installation_day (installation_id, day_number),
        INDEX idx_work_date (work_date),
        UNIQUE KEY unique_material_day (installation_id, material_id, day_number)
    )";
    
    $db->exec($sql2);
    echo "✓ daily_material_usage table created\n";
    
    // Create daily_work_photos table (tracks photos/videos uploaded)
    $sql3 = "CREATE TABLE IF NOT EXISTS daily_work_photos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        installation_id INT NOT NULL,
        day_number INT NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_type ENUM('image', 'video') NOT NULL,
        file_size INT,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        FOREIGN KEY (installation_id) REFERENCES installation_delegations(id) ON DELETE CASCADE,
        INDEX idx_installation_day (installation_id, day_number)
    )";
    
    $db->exec($sql3);
    echo "✓ daily_work_photos table created\n";
    
    echo "\nAll material usage tracking tables created successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
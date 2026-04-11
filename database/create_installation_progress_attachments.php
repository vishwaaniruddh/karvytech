<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Creating installation progress attachments table...\n";
    
    // Create installation progress attachments table
    $sql1 = "CREATE TABLE IF NOT EXISTS installation_progress_attachments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        installation_id INT NOT NULL,
        progress_id INT NULL,
        attachment_type ENUM('final_report', 'site_snaps', 'excel_sheet', 'drawing_attachment', 'other') NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        original_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_type VARCHAR(100) NOT NULL,
        file_size INT,
        mime_type VARCHAR(100),
        uploaded_by INT,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        description TEXT,
        INDEX idx_installation_id (installation_id),
        INDEX idx_progress_id (progress_id),
        INDEX idx_attachment_type (attachment_type)
    )";
    
    $db->exec($sql1);
    echo "✓ installation_progress_attachments table created\n";
    
    // Try to add columns to installation_progress table (will fail silently if columns exist)
    try {
        $sql2 = "ALTER TABLE installation_progress 
                 ADD COLUMN has_final_report BOOLEAN DEFAULT FALSE,
                 ADD COLUMN has_site_snaps BOOLEAN DEFAULT FALSE,
                 ADD COLUMN has_excel_sheet BOOLEAN DEFAULT FALSE,
                 ADD COLUMN has_drawing_attachment BOOLEAN DEFAULT FALSE,
                 ADD COLUMN total_attachments INT DEFAULT 0";
        
        $db->exec($sql2);
        echo "✓ Added attachment columns to installation_progress table\n";
    } catch (Exception $e) {
        echo "ℹ Installation_progress table columns may already exist or table doesn't exist\n";
    }
    
    echo "\nInstallation progress attachments setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
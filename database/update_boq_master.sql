-- Create BOQ Master table
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
);

-- Insert some sample BOQ data
INSERT IGNORE INTO boq_master (boq_name, is_serial_number_required, status, created_by) VALUES 
('Network Equipment', TRUE, 'active', 1),
('Installation Materials', FALSE, 'active', 1),
('Testing Equipment', TRUE, 'active', 1),
('Cables & Accessories', FALSE, 'active', 1),
('Power Equipment', TRUE, 'active', 1);
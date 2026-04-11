-- Create material_requests table
CREATE TABLE IF NOT EXISTS material_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    vendor_id INT NOT NULL,
    survey_id INT NULL,
    request_date DATE NOT NULL,
    required_date DATE NULL,
    request_notes TEXT NULL,
    items JSON NOT NULL,
    status ENUM('draft', 'pending', 'approved', 'dispatched', 'completed', 'rejected') DEFAULT 'pending',
    processed_by INT NULL,
    processed_date DATETIME NULL,
    dispatch_details JSON NULL,
    created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_date DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
    FOREIGN KEY (survey_id) REFERENCES site_surveys(id) ON DELETE SET NULL,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_site_id (site_id),
    INDEX idx_vendor_id (vendor_id),
    INDEX idx_status (status),
    INDEX idx_request_date (request_date),
    INDEX idx_created_date (created_date)
);

-- Add some sample statuses for better tracking
ALTER TABLE material_requests 
MODIFY COLUMN status ENUM('draft', 'pending', 'approved', 'dispatched', 'partially_dispatched', 'completed', 'rejected', 'cancelled') DEFAULT 'pending';
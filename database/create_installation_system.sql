-- Create installation delegations table
CREATE TABLE IF NOT EXISTS installation_delegations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    site_id INT NOT NULL,
    vendor_id INT NOT NULL,
    delegated_by INT NOT NULL,
    delegation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expected_start_date DATE NULL,
    expected_completion_date DATE NULL,
    actual_start_date TIMESTAMP NULL,
    actual_completion_date TIMESTAMP NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    installation_type ENUM('standard', 'complex', 'maintenance', 'upgrade') DEFAULT 'standard',
    status ENUM('assigned', 'acknowledged', 'in_progress', 'on_hold', 'completed', 'cancelled') DEFAULT 'assigned',
    special_instructions TEXT NULL,
    notes TEXT NULL,
    hold_reason TEXT NULL,
    updated_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (survey_id) REFERENCES site_surveys(id) ON DELETE CASCADE,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
    FOREIGN KEY (delegated_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_survey_id (survey_id),
    INDEX idx_site_id (site_id),
    INDEX idx_vendor_id (vendor_id),
    INDEX idx_status (status),
    INDEX idx_delegation_date (delegation_date)
);

-- Create installation progress tracking table
CREATE TABLE IF NOT EXISTS installation_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    installation_id INT NOT NULL,
    progress_date DATE NOT NULL,
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    work_description TEXT NULL,
    photos TEXT NULL, -- JSON array of photo paths
    issues_faced TEXT NULL,
    next_steps TEXT NULL,
    updated_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (installation_id) REFERENCES installation_delegations(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_installation_id (installation_id),
    INDEX idx_progress_date (progress_date)
);

-- Add installation tracking columns to site_surveys table
ALTER TABLE site_surveys 
ADD COLUMN installation_status ENUM('not_delegated', 'delegated', 'in_progress', 'completed') DEFAULT 'not_delegated' AFTER survey_status,
ADD COLUMN installation_id INT NULL AFTER installation_status,
ADD INDEX idx_installation_status (installation_status);

-- Create installation notifications table
CREATE TABLE IF NOT EXISTS installation_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    installation_id INT NOT NULL,
    notification_type ENUM('assignment', 'status_update', 'overdue', 'completion') NOT NULL,
    recipient_type ENUM('vendor', 'admin', 'all') NOT NULL,
    recipient_id INT NULL, -- NULL for 'all' type
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (installation_id) REFERENCES installation_delegations(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_installation_id (installation_id),
    INDEX idx_recipient (recipient_type, recipient_id),
    INDEX idx_is_read (is_read)
);

-- Insert sample installation data (optional)
-- This will be populated when surveys are approved and delegated for installation
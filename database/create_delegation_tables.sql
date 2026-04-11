-- Create vendors table
CREATE TABLE IF NOT EXISTS vendors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    contact_person VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create site_delegations table
CREATE TABLE IF NOT EXISTS site_delegations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    vendor_id INT NOT NULL,
    delegated_by INT NOT NULL,
    delegation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
    FOREIGN KEY (delegated_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_site_id (site_id),
    INDEX idx_vendor_id (vendor_id),
    INDEX idx_status (status)
);

-- Insert sample vendors
INSERT INTO vendors (name, email, phone, contact_person, status) VALUES
('TechInstall Solutions', 'contact@techinstall.com', '+91-9876543210', 'Rajesh Kumar', 'active'),
('QuickFix Services', 'info@quickfix.com', '+91-9876543211', 'Priya Sharma', 'active'),
('ProInstall Corp', 'support@proinstall.com', '+91-9876543212', 'Amit Singh', 'active'),
('FastTrack Installation', 'hello@fasttrack.com', '+91-9876543213', 'Neha Gupta', 'active'),
('Elite Services', 'contact@eliteservices.com', '+91-9876543214', 'Vikram Patel', 'active');
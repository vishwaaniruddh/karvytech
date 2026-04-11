-- Create vendor permissions table
CREATE TABLE IF NOT EXISTS vendor_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    permission_key VARCHAR(100) NOT NULL,
    permission_value TINYINT(1) DEFAULT 0,
    granted_by INT NOT NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vendor_permission (vendor_id, permission_key),
    INDEX idx_vendor_id (vendor_id),
    INDEX idx_permission_key (permission_key)
);

-- Default permissions for all vendors
INSERT INTO vendor_permissions (vendor_id, permission_key, permission_value, granted_by) 
SELECT 
    v.id as vendor_id,
    'view_sites' as permission_key,
    1 as permission_value,
    1 as granted_by
FROM vendors v 
WHERE v.status = 'active'
ON DUPLICATE KEY UPDATE permission_value = 1;

INSERT INTO vendor_permissions (vendor_id, permission_key, permission_value, granted_by) 
SELECT 
    v.id as vendor_id,
    'update_progress' as permission_key,
    1 as permission_value,
    1 as granted_by
FROM vendors v 
WHERE v.status = 'active'
ON DUPLICATE KEY UPDATE permission_value = 1;

-- Optional permissions (disabled by default)
INSERT INTO vendor_permissions (vendor_id, permission_key, permission_value, granted_by) 
SELECT 
    v.id as vendor_id,
    'view_masters' as permission_key,
    0 as permission_value,
    1 as granted_by
FROM vendors v 
WHERE v.status = 'active'
ON DUPLICATE KEY UPDATE permission_value = 0;

INSERT INTO vendor_permissions (vendor_id, permission_key, permission_value, granted_by) 
SELECT 
    v.id as vendor_id,
    'view_reports' as permission_key,
    0 as permission_value,
    1 as granted_by
FROM vendors v 
WHERE v.status = 'active'
ON DUPLICATE KEY UPDATE permission_value = 0;
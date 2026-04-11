-- Add vendor_id column to users table
ALTER TABLE users ADD COLUMN vendor_id INT NULL AFTER role;
ALTER TABLE users ADD FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE SET NULL;

-- Add vendor role
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'vendor') DEFAULT 'user';

-- Create vendor users for existing vendors
INSERT INTO users (username, email, password_hash, plain_password, role, vendor_id, status) 
SELECT 
    LOWER(REPLACE(name, ' ', '_')) as username,
    email,
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' as password_hash, -- password: password
    'password' as plain_password,
    'vendor' as role,
    id as vendor_id,
    'active' as status
FROM vendors 
WHERE status = 'active' AND email IS NOT NULL;
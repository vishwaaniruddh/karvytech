-- Create roles table
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create modules table
CREATE TABLE IF NOT EXISTS modules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    display_name VARCHAR(150) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create permissions table
CREATE TABLE IF NOT EXISTS permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    module_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    display_name VARCHAR(150) NOT NULL,
    description TEXT,
    action VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    UNIQUE KEY unique_module_permission (module_id, name)
);

-- Create role_permissions table (junction table)
CREATE TABLE IF NOT EXISTS role_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_permission (role_id, permission_id)
);

-- Update users table to include role_id
ALTER TABLE users ADD COLUMN role_id INT AFTER role;
ALTER TABLE users ADD FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL;

-- Insert default roles
INSERT INTO roles (name, display_name, description) VALUES
('superadmin', 'Super Administrator', 'Full system access'),
('admin', 'Administrator', 'Administrative access'),
('manager', 'Manager', 'Manager level access'),
('contractor', 'Contractor', 'Contractor/Vendor access')
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert modules
INSERT INTO modules (name, display_name, description, icon) VALUES
('users', 'User Management', 'Manage system users', 'users'),
('sites', 'Site Management', 'Manage sites and delegations', 'map-pin'),
('surveys', 'Site Surveys', 'Manage site surveys', 'clipboard-list'),
('installations', 'Installations', 'Manage installations', 'wrench'),
('inventory', 'Inventory Management', 'Manage inventory and stock', 'box'),
('materials', 'Material Requests', 'Manage material requests', 'package'),
('reports', 'Reports', 'View and generate reports', 'bar-chart-2'),
('masters', 'Master Data', 'Manage master data', 'database'),
('settings', 'Settings', 'System settings', 'settings'),
('logs', 'Logs', 'View system logs', 'file-text')
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Users module
INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'view', 'View Users', 'View user list', 'view' FROM modules WHERE name='users'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'create', 'Create User', 'Create new user', 'create' FROM modules WHERE name='users'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'edit', 'Edit User', 'Edit user details', 'edit' FROM modules WHERE name='users'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'delete', 'Delete User', 'Delete user', 'delete' FROM modules WHERE name='users'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'manage_roles', 'Manage Roles', 'Assign roles to users', 'manage' FROM modules WHERE name='users'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Sites module
INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'view', 'View Sites', 'View site list', 'view' FROM modules WHERE name='sites'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'create', 'Create Site', 'Create new site', 'create' FROM modules WHERE name='sites'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'edit', 'Edit Site', 'Edit site details', 'edit' FROM modules WHERE name='sites'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'delete', 'Delete Site', 'Delete site', 'delete' FROM modules WHERE name='sites'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'delegate', 'Delegate Site', 'Delegate site to contractor', 'manage' FROM modules WHERE name='sites'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Surveys module
INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'view', 'View Surveys', 'View survey list', 'view' FROM modules WHERE name='surveys'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'create', 'Create Survey', 'Create new survey', 'create' FROM modules WHERE name='surveys'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'edit', 'Edit Survey', 'Edit survey details', 'edit' FROM modules WHERE name='surveys'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'approve', 'Approve Survey', 'Approve survey submission', 'manage' FROM modules WHERE name='surveys'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Installations module
INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'view', 'View Installations', 'View installation list', 'view' FROM modules WHERE name='installations'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'create', 'Create Installation', 'Create new installation', 'create' FROM modules WHERE name='installations'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'edit', 'Edit Installation', 'Edit installation details', 'edit' FROM modules WHERE name='installations'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'manage', 'Manage Installation', 'Manage installation progress', 'manage' FROM modules WHERE name='installations'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Inventory module
INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'view', 'View Inventory', 'View inventory list', 'view' FROM modules WHERE name='inventory'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'create', 'Add Stock', 'Add stock items', 'create' FROM modules WHERE name='inventory'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'edit', 'Edit Stock', 'Edit stock items', 'edit' FROM modules WHERE name='inventory'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'delete', 'Delete Stock', 'Delete stock items', 'delete' FROM modules WHERE name='inventory'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Materials module
INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'view', 'View Requests', 'View material requests', 'view' FROM modules WHERE name='materials'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'create', 'Create Request', 'Create material request', 'create' FROM modules WHERE name='materials'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'approve', 'Approve Request', 'Approve material request', 'manage' FROM modules WHERE name='materials'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'dispatch', 'Dispatch Materials', 'Dispatch materials', 'manage' FROM modules WHERE name='materials'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Reports module
INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'view', 'View Reports', 'View reports', 'view' FROM modules WHERE name='reports'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'export', 'Export Reports', 'Export report data', 'manage' FROM modules WHERE name='reports'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Masters module
INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'view', 'View Masters', 'View master data', 'view' FROM modules WHERE name='masters'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'manage', 'Manage Masters', 'Manage master data', 'manage' FROM modules WHERE name='masters'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Settings module
INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'view', 'View Settings', 'View settings', 'view' FROM modules WHERE name='settings'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'manage', 'Manage Settings', 'Manage system settings', 'manage' FROM modules WHERE name='settings'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Insert permissions for Logs module
INSERT INTO permissions (module_id, name, display_name, description, action) 
SELECT id, 'view', 'View Logs', 'View system logs', 'view' FROM modules WHERE name='logs'
ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);

-- Assign all permissions to Superadmin
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.name = 'superadmin'
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

-- Assign permissions to Admin (all except settings and logs)
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p, modules m
WHERE r.name = 'admin' AND p.module_id = m.id AND m.name NOT IN ('settings', 'logs')
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

-- Assign permissions to Manager (limited access)
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p, modules m
WHERE r.name = 'manager' AND p.module_id = m.id AND m.name IN ('sites', 'surveys', 'installations', 'materials', 'reports')
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

-- Assign permissions to Contractor (view and create only)
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r, permissions p, modules m
WHERE r.name = 'contractor' AND p.module_id = m.id AND m.name IN ('sites', 'surveys', 'installations', 'materials') 
AND p.action IN ('view', 'create', 'manage')
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);

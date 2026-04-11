-- Create menu system tables
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NULL,
    title VARCHAR(100) NOT NULL,
    icon VARCHAR(50) NULL,
    url VARCHAR(255) NULL,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS role_menu_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(50) NOT NULL,
    menu_item_id INT NOT NULL,
    can_access BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_menu (role, menu_item_id)
);

CREATE TABLE IF NOT EXISTS user_menu_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    can_access BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_menu (user_id, menu_item_id)
);

-- Insert main menu items for admin
INSERT INTO menu_items (id, parent_id, title, icon, url, sort_order) VALUES
(1, NULL, 'Dashboard', 'dashboard', '/admin/dashboard.php', 1),
(2, NULL, 'Sites', 'location', '/admin/sites/', 2),
(3, NULL, 'Vendors', 'vendor', '/admin/vendors/', 3),
(4, NULL, 'BOQ Management', 'boq', '/admin/boq/', 4),
(5, NULL, 'Inventory', 'inventory', NULL, 5),
(6, NULL, 'Surveys', 'reports', '/admin/surveys/', 7),
(7, NULL, 'Masters', 'settings', '/admin/masters/', 8);

-- Insert inventory submenu items
INSERT INTO menu_items (id, parent_id, title, icon, url, sort_order) VALUES
(11, 5, 'All Stocks', 'inventory', '/admin/inventory/', 1),
(12, 5, 'Material Requests', 'requests', '/admin/requests/', 2),
(13, 5, 'Material Received', 'location-sub', '/admin/inventory/inwards/', 3),
(14, 5, 'Material Dispatches', 'business', '/admin/inventory/dispatches/', 4);

-- Set permissions for admin role
INSERT INTO role_menu_permissions (role, menu_item_id, can_access) VALUES
('admin', 1, TRUE),
('admin', 2, TRUE),
('admin', 3, TRUE),
('admin', 4, TRUE),
('admin', 5, TRUE),
('admin', 6, TRUE),
('admin', 7, TRUE),
('admin', 11, TRUE),
('admin', 12, TRUE),
('admin', 13, TRUE),
('admin', 14, TRUE);

-- Set permissions for vendor role (limited access)
INSERT INTO role_menu_permissions (role, menu_item_id, can_access) VALUES
('vendor', 1, TRUE),
('vendor', 2, TRUE),
('vendor', 5, TRUE),
('vendor', 6, TRUE),
('vendor', 11, TRUE),
('vendor', 12, TRUE),
('vendor', 13, TRUE),
('vendor', 14, TRUE);
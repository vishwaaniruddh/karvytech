-- Site Installation Management System Database Schema

CREATE DATABASE IF NOT EXISTS site_installation_management;
USE site_installation_management;

-- Users table for authentication and role management
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    plain_password VARCHAR(255) NOT NULL COMMENT 'For testing purposes only',
    jwt_token TEXT NULL,
    role ENUM('admin', 'vendor') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_role (role)
);

-- Master Tables for Location and Business Data

-- Countries master table
CREATE TABLE countries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_status (status)
);

-- Zones master table
CREATE TABLE zones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_status (status)
);

-- States master table
CREATE TABLE states (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    country_id INT NOT NULL,
    zone_id INT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE RESTRICT,
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL,
    INDEX idx_name (name),
    INDEX idx_country (country_id),
    INDEX idx_zone (zone_id),
    INDEX idx_status (status),
    UNIQUE KEY unique_state_country (name, country_id)
);

-- Cities master table
CREATE TABLE cities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    state_id INT NOT NULL,
    country_id INT NOT NULL,
    zone_id INT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE RESTRICT,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE RESTRICT,
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL,
    INDEX idx_name (name),
    INDEX idx_state (state_id),
    INDEX idx_country (country_id),
    INDEX idx_zone (zone_id),
    INDEX idx_status (status),
    UNIQUE KEY unique_city_state (name, state_id)
);

-- Banks master table
CREATE TABLE banks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL UNIQUE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_status (status)
);

-- Customers master table
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL UNIQUE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_status (status)
);

-- Menu system tables
CREATE TABLE menu_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    parent_id INT NULL,
    title VARCHAR(100) NOT NULL,
    icon VARCHAR(100) NULL,
    url VARCHAR(255) NULL,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    INDEX idx_parent (parent_id),
    INDEX idx_sort_order (sort_order),
    INDEX idx_status (status)
);

-- Role-based menu permissions
CREATE TABLE user_menu_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    can_access BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_menu (user_id, menu_item_id),
    INDEX idx_user (user_id),
    INDEX idx_menu (menu_item_id)
);

-- Role-based menu permissions (for roles)
CREATE TABLE role_menu_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role ENUM('admin', 'vendor') NOT NULL,
    menu_item_id INT NOT NULL,
    can_access BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_menu (role, menu_item_id),
    INDEX idx_role (role),
    INDEX idx_menu (menu_item_id)
);

-- Sites table for installation locations
CREATE TABLE `sites` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `site_id` VARCHAR(255) NOT NULL,
    `store_id` VARCHAR(255),
    `location` TEXT,
    `city` VARCHAR(60),
    `state` VARCHAR(60),
    `country` VARCHAR(60),
    `branch` VARCHAR(60),
    `remarks` TEXT,
    `po_number` VARCHAR(255),
    `po_date` DATE,
    `customer` VARCHAR(255),
    `bank` VARCHAR(255),
    `vendor` VARCHAR(255),
    `activity_status` VARCHAR(100),
    `is_delegate` BOOLEAN DEFAULT FALSE,
    `delegated_vendor` VARCHAR(255),
    `survey_status` BOOLEAN DEFAULT FALSE,
    `installation_status` BOOLEAN DEFAULT FALSE,
    `is_material_request_generated` BOOLEAN DEFAULT FALSE,
    `survey_submission_date` DATETIME,
    `installation_date` DATETIME,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `created_by` VARCHAR(255),
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_by` VARCHAR(255),
    INDEX idx_site_id (site_id),
    INDEX idx_store_id (store_id),
    INDEX idx_city (city),
    INDEX idx_state (state),
    INDEX idx_vendor (vendor),
    INDEX idx_activity_status (activity_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- BOQ (Bill of Quantities) items master table
CREATE TABLE boq_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    item_name VARCHAR(200) NOT NULL,
    item_code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    unit VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_item_name (item_name),
    INDEX idx_item_code (item_code)
);

-- Inventory table for stock management
CREATE TABLE inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    boq_item_id INT NOT NULL,
    current_stock DECIMAL(10,2) DEFAULT 0,
    reserved_stock DECIMAL(10,2) DEFAULT 0,
    unit_cost DECIMAL(10,2) DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (boq_item_id) REFERENCES boq_items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_inventory_item (boq_item_id),
    INDEX idx_stock_levels (current_stock, reserved_stock)
);

-- Site surveys table
CREATE TABLE site_surveys (
    id INT PRIMARY KEY AUTO_INCREMENT,
    site_id INT NOT NULL,
    vendor_id INT NOT NULL,
    survey_data JSON,
    survey_date DATE NOT NULL,
    status ENUM('draft', 'submitted') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_site_survey (site_id),
    INDEX idx_vendor_survey (vendor_id),
    INDEX idx_survey_date (survey_date)
);

-- Material requests table
CREATE TABLE material_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    site_id INT NOT NULL,
    vendor_id INT NOT NULL,
    request_data JSON NOT NULL,
    status ENUM('pending', 'approved', 'dispatched') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_site_request (site_id),
    INDEX idx_vendor_request (vendor_id),
    INDEX idx_request_status (status)
);

-- Material dispatches table
CREATE TABLE material_dispatches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    material_request_id INT NOT NULL,
    dispatch_items JSON NOT NULL,
    courier_name VARCHAR(100),
    tracking_number VARCHAR(100),
    dispatch_date DATE NOT NULL,
    acknowledgment_status ENUM('pending', 'received') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (material_request_id) REFERENCES material_requests(id) ON DELETE CASCADE,
    INDEX idx_request_dispatch (material_request_id),
    INDEX idx_tracking (tracking_number),
    INDEX idx_dispatch_date (dispatch_date)
);

-- Installation progress table
CREATE TABLE installation_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    site_id INT NOT NULL,
    vendor_id INT NOT NULL,
    progress_date DATE NOT NULL,
    materials_used JSON,
    work_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_site_progress (site_id),
    INDEX idx_vendor_progress (vendor_id),
    INDEX idx_progress_date (progress_date),
    UNIQUE KEY unique_daily_progress (site_id, vendor_id, progress_date)
);

-- Audit log table for tracking user actions
CREATE TABLE audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_audit (user_id),
    INDEX idx_action (action),
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_created_at (created_at)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, phone, password_hash, plain_password, role) VALUES 
('admin', 'admin@example.com', '+1234567890', '$2y$12$0pfylKFS83H4kvAdPqGK7O4CgJBBKSTPxi0QXbFX28vmMfwfhFxpC', 'admin123', 'admin');

-- Insert sample master data

-- Countries
INSERT INTO countries (name) VALUES 
('India'),
('United States'),
('United Kingdom'),
('Canada'),
('Australia');

-- Zones
INSERT INTO zones (name) VALUES 
('North Zone'),
('South Zone'),
('East Zone'),
('West Zone'),
('Central Zone'),
('Northeast Zone');

-- States (for India)
INSERT INTO states (name, country_id, zone_id) VALUES 
('Maharashtra', 1, 4),
('Karnataka', 1, 2),
('Tamil Nadu', 1, 2),
('Delhi', 1, 1),
('Gujarat', 1, 4),
('Rajasthan', 1, 1),
('West Bengal', 1, 3),
('Uttar Pradesh', 1, 1),
('Madhya Pradesh', 1, 5),
('Kerala', 1, 2);

-- Cities
INSERT INTO cities (name, state_id, country_id, zone_id) VALUES 
('Mumbai', 1, 1, 4),
('Pune', 1, 1, 4),
('Nashik', 1, 1, 4),
('Bangalore', 2, 1, 2),
('Mysore', 2, 1, 2),
('Chennai', 3, 1, 2),
('Coimbatore', 3, 1, 2),
('New Delhi', 4, 1, 1),
('Ahmedabad', 5, 1, 4),
('Surat', 5, 1, 4),
('Jaipur', 6, 1, 1),
('Kolkata', 7, 1, 3),
('Lucknow', 8, 1, 1),
('Bhopal', 9, 1, 5),
('Kochi', 10, 1, 2);

-- Banks
INSERT INTO banks (name) VALUES 
('State Bank of India'),
('HDFC Bank'),
('ICICI Bank'),
('Axis Bank'),
('Punjab National Bank'),
('Bank of Baroda'),
('Canara Bank'),
('Union Bank of India'),
('Indian Bank'),
('Central Bank of India'),
('IDFC First Bank'),
('IndusInd Bank'),
('Kotak Mahindra Bank'),
('Yes Bank'),
('Federal Bank');

-- Customers
INSERT INTO customers (name) VALUES 
('Reliance Industries Ltd'),
('Tata Consultancy Services'),
('Infosys Limited'),
('Wipro Limited'),
('HCL Technologies'),
('Tech Mahindra'),
('Larsen & Toubro'),
('ITC Limited'),
('Bharti Airtel'),
('Maruti Suzuki India'),
('Asian Paints'),
('Titan Company'),
('UltraTech Cement'),
('Bajaj Finance'),
('HDFC Life Insurance');

-- Menu Items
INSERT INTO menu_items (id, parent_id, title, icon, url, sort_order) VALUES
-- Main Menu Items
(1, NULL, 'Dashboard', 'dashboard', '/admin/dashboard.php', 1),
(2, NULL, 'Sites', 'location', '/admin/sites/', 2),
(3, NULL, 'Admin', 'settings', NULL, 3),
(4, NULL, 'Inventory', 'inventory', '/admin/inventory/', 4),
(5, NULL, 'Material Requests', 'requests', '/admin/requests/', 5),
(6, NULL, 'Reports', 'reports', '/admin/reports/', 6),

-- Admin Submenu Items
(10, 3, 'Users', 'users', '/admin/users/', 1),
(11, 3, 'Location', 'location-sub', NULL, 2),
(12, 3, 'Business', 'business', NULL, 3),
(13, 3, 'BOQ', 'boq', '/admin/boq/', 4),

-- Location Submenu Items
(20, 11, 'Countries', 'country', '/admin/masters/?type=countries', 1),
(21, 11, 'Zones', 'zone', '/admin/masters/?type=zones', 2),
(22, 11, 'States', 'state', '/admin/masters/?type=states', 3),
(23, 11, 'Cities', 'city', '/admin/masters/?type=cities', 4),

-- Business Submenu Items
(30, 12, 'Banks', 'bank', '/admin/masters/?type=banks', 1),
(31, 12, 'Customers', 'customer', '/admin/masters/?type=customers', 2),
(32, 12, 'Vendors', 'vendor', '/admin/vendors/', 3);

-- Role-based menu permissions (Admin gets all access)
INSERT INTO role_menu_permissions (role, menu_item_id, can_access) VALUES
-- Admin role - full access
('admin', 1, TRUE), ('admin', 2, TRUE), ('admin', 3, TRUE), ('admin', 4, TRUE), ('admin', 5, TRUE), ('admin', 6, TRUE),
('admin', 10, TRUE), ('admin', 11, TRUE), ('admin', 12, TRUE), ('admin', 13, TRUE),
('admin', 20, TRUE), ('admin', 21, TRUE), ('admin', 22, TRUE), ('admin', 23, TRUE),
('admin', 30, TRUE), ('admin', 31, TRUE), ('admin', 32, TRUE),

-- Vendor role - limited access
('vendor', 1, TRUE), ('vendor', 2, TRUE), ('vendor', 5, TRUE);

-- Insert sample BOQ items
INSERT INTO boq_items (item_name, item_code, description, unit) VALUES 
('Ethernet Cable Cat6', 'ETH-CAT6-001', 'Category 6 Ethernet cable for network connections', 'meters'),
('Network Switch 24-port', 'NSW-24P-001', '24-port managed network switch', 'pieces'),
('Fiber Optic Cable', 'FOC-SM-001', 'Single-mode fiber optic cable', 'meters'),
('Router Enterprise', 'RTR-ENT-001', 'Enterprise grade router', 'pieces'),
('Cable Tray 2m', 'CT-2M-001', '2 meter cable tray section', 'pieces'),
('Wall Mount Rack 12U', 'WMR-12U-001', '12U wall mount server rack', 'pieces'),
('Power Strip 8-outlet', 'PS-8O-001', '8-outlet power distribution strip', 'pieces'),
('UPS 1000VA', 'UPS-1KVA-001', '1000VA uninterruptible power supply', 'pieces');

-- Insert sample inventory
INSERT INTO inventory (boq_item_id, current_stock, unit_cost) VALUES 
(1, 1000.00, 25.50),
(2, 50.00, 1250.00),
(3, 500.00, 45.75),
(4, 25.00, 3500.00),
(5, 100.00, 125.00),
(6, 30.00, 850.00),
(7, 75.00, 65.00),
(8, 20.00, 2250.00);
-- Add Bulk Operations menu and submenus
-- Insert main Bulk Operations menu
INSERT INTO `menu_items` (`parent_id`, `title`, `icon`, `url`, `sort_order`, `status`, `created_at`, `updated_at`)
VALUES (NULL, 'Bulk Operations', 'bulk', NULL, 8, 'active', NOW(), NOW());

-- Get the ID of the Bulk Operations menu we just created
SET @bulk_menu_id = LAST_INSERT_ID();

-- Add Bulk Operations submenus
INSERT INTO `menu_items` (`parent_id`, `title`, `icon`, `url`, `sort_order`, `status`, `created_at`, `updated_at`)
VALUES 
(@bulk_menu_id, 'Site Operations', 'location', '/admin/bulk/sites.php', 1, 'active', NOW(), NOW()),
(@bulk_menu_id, 'User Operations', 'users', '/admin/bulk/users.php', 2, 'active', NOW(), NOW()),
(@bulk_menu_id, 'Survey Operations', 'reports', '/admin/bulk/surveys.php', 3, 'active', NOW(), NOW()),
(@bulk_menu_id, 'Material Operations', 'inventory', '/admin/bulk/materials.php', 4, 'active', NOW(), NOW()),
(@bulk_menu_id, 'Data Import/Export', 'business', '/admin/bulk/data.php', 5, 'active', NOW(), NOW()),
(@bulk_menu_id, 'System Operations', 'settings', '/admin/bulk/system.php', 6, 'active', NOW(), NOW());
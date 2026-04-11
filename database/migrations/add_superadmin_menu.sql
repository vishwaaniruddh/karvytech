-- Add Superadmin Actions menu item
-- This menu should appear only for superadmin role

INSERT INTO `menu_items` (`parent_id`, `title`, `icon`, `url`, `sort_order`, `status`, `created_at`, `updated_at`)
VALUES 
(NULL, 'Superadmin Actions', 'shield', '/admin/superadmin/', 2, 'active', NOW(), NOW());

-- Note: The menu should appear right after Dashboard (sort_order = 2)
-- The icon 'shield' represents security and admin control
-- Only superadmin role can access this based on Auth::requireRole('superadmin') in the page


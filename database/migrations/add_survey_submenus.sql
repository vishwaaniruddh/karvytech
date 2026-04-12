-- Add Survey submenus
-- First, get the Survey parent menu ID (assuming it exists)

-- Update the main Survey menu to be a parent
UPDATE menu_items SET url = NULL WHERE title = 'Surveys';

-- Add Survey submenus
INSERT INTO `menu_items` (`parent_id`, `title`, `icon`, `url`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 
    (SELECT id FROM menu_items WHERE title = 'Surveys' AND parent_id IS NULL LIMIT 1) as parent_id,
    'Legacy Survey',
    'reports',
    '/admin/surveys/legacy.php',
    1,
    'active',
    NOW(),
    NOW()
WHERE EXISTS (SELECT 1 FROM menu_items WHERE title = 'Surveys' AND parent_id IS NULL);

INSERT INTO `menu_items` (`parent_id`, `title`, `icon`, `url`, `sort_order`, `status`, `created_at`, `updated_at`)
SELECT 
    (SELECT id FROM menu_items WHERE title = 'Surveys' AND parent_id IS NULL LIMIT 1) as parent_id,
    'Survey Responses',
    'reports',
    '/admin/surveys/',
    2,
    'active',
    NOW(),
    NOW()
WHERE EXISTS (SELECT 1 FROM menu_items WHERE title = 'Surveys' AND parent_id IS NULL);
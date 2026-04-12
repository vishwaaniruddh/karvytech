<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Update the main Survey menu to be a parent (remove URL)
    $db->exec("UPDATE menu_items SET url = NULL WHERE title = 'Survey' AND parent_id IS NULL");
    echo "Updated Survey menu to be parent\n";
    
    // Check if Legacy Survey submenu already exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM menu_items WHERE title = 'Legacy Survey' AND parent_id = (SELECT id FROM menu_items WHERE title = 'Survey' AND parent_id IS NULL LIMIT 1)");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        // Add Legacy Survey submenu
        $db->exec("INSERT INTO menu_items (parent_id, title, icon, url, sort_order, status, created_at, updated_at)
                    SELECT 
                        (SELECT id FROM menu_items WHERE title = 'Survey' AND parent_id IS NULL LIMIT 1) as parent_id,
                        'Legacy Survey',
                        'reports',
                        '/admin/surveys/legacy.php',
                        1,
                        'active',
                        NOW(),
                        NOW()");
        echo "Added Legacy Survey submenu\n";
    } else {
        echo "Legacy Survey submenu already exists\n";
    }
    
    // Check if Survey Responses submenu already exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM menu_items WHERE title = 'Survey Responses' AND parent_id = (SELECT id FROM menu_items WHERE title = 'Survey' AND parent_id IS NULL LIMIT 1)");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        // Add Survey Responses submenu
        $db->exec("INSERT INTO menu_items (parent_id, title, icon, url, sort_order, status, created_at, updated_at)
                    SELECT 
                        (SELECT id FROM menu_items WHERE title = 'Survey' AND parent_id IS NULL LIMIT 1) as parent_id,
                        'Survey Responses',
                        'reports',
                        '/admin/surveys/',
                        2,
                        'active',
                        NOW(),
                        NOW()");
        echo "Added Survey Responses submenu\n";
    } else {
        echo "Survey Responses submenu already exists\n";
    }
    
    echo "Survey submenus setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
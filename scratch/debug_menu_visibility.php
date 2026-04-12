<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/BaseModel.php';
require_once __DIR__ . '/../models/Menu.php';

$email = 'Elavarasan.d@karvytech.in';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, username, email, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found: $email\n");
    }

    echo "User ID: " . $user['id'] . "\n";
    echo "User Role: " . $user['role'] . "\n";

    $menuModel = new Menu();
    $menu = $menuModel->getMenuForUser($user['id'], $user['role']);
    
    echo "Menu Tree for User:\n";
    // Check if Masters (58) exists and has children
    foreach ($menu as $root) {
        if ($root['id'] == 58) {
            echo "Masters (58) found.\n";
            echo "Masters children count: " . count($root['children']) . "\n";
            foreach ($root['children'] as $child) {
                echo " - " . $child['title'] . " (ID: " . $child['id'] . ", URL: " . $child['url'] . ")\n";
                if (count($child['children']) > 0) {
                    echo "   - Child sub-items: " . count($child['children']) . "\n";
                }
            }
        }
    }

    // Check permissions for this user specifically
    $stmt = $db->prepare("SELECT COUNT(*) FROM user_menu_permissions WHERE user_id = ? AND can_access = 1");
    $stmt->execute([$user['id']]);
    echo "Direct user permissions count: " . $stmt->fetchColumn() . "\n";

    // Check permissions for their role
    $stmt = $db->prepare("SELECT COUNT(*) FROM role_menu_permissions WHERE role = ? AND can_access = 1");
    $stmt->execute([$user['role']]);
    echo "Role permissions count: " . $stmt->fetchColumn() . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

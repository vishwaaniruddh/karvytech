<?php
require_once __DIR__ . '/../models/Menu.php';

// Test with admin user (ID: 1)
$menuModel = new Menu();
$menuItems = $menuModel->getMenuForUser(1, 'admin');

echo "Menu structure for admin user:\n";
echo "==============================\n";

function printMenu($items, $level = 0) {
    foreach ($items as $item) {
        $indent = str_repeat('  ', $level);
        echo $indent . "- " . $item['title'] . " (ID: " . $item['id'] . ")";
        if ($item['url']) {
            echo " -> " . $item['url'];
        }
        echo "\n";
        
        if (!empty($item['children'])) {
            printMenu($item['children'], $level + 1);
        }
    }
}

printMenu($menuItems);
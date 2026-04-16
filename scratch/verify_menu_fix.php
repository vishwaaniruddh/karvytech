<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Menu.php';

$userId = 58; // Bela
$userRole = 'Inventory';

try {
    $menuModel = new Menu();
    $menuItems = $menuModel->getMenuForUser($userId, $userRole);
    echo "Successfully retrieved " . count($menuItems) . " menu items for Bela.\n";
    foreach ($menuItems as $item) {
        echo "- " . $item['title'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

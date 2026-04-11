<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Menu.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);
$currentUser = Auth::getCurrentUser();

$menuModel = new Menu();

echo "<h1>Menu Debug Information</h1>";

echo "<h2>Current User:</h2>";
echo "<pre>" . print_r($currentUser, true) . "</pre>";

echo "<h2>All Menu Items:</h2>";
$allMenus = $menuModel->getAllMenuItems();
echo "<pre>" . print_r($allMenus, true) . "</pre>";

echo "<h2>User's Menu Items:</h2>";
$userMenus = $menuModel->getMenuForUser($currentUser['id'], $currentUser['role']);
echo "<pre>" . print_r($userMenus, true) . "</pre>";

echo "<h2>User's Permissions:</h2>";
$userPermissions = $menuModel->getUserPermissions($currentUser['id']);
echo "<pre>" . print_r($userPermissions, true) . "</pre>";
?>
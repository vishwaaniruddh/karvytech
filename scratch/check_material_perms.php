<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

$stmt = $db->query("SELECT * FROM permissions WHERE module_name = 'material_requests'");
$perms = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Permissions for material_requests:\n";
print_r($perms);

$stmt = $db->query("SELECT * FROM roles");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\nRoles:\n";
print_r($roles);

<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$userId = 57;
$permissionId = 13; // Edit Survey

echo "--- User Override for Permission $permissionId ---\n";
$stmt = $db->prepare("SELECT * FROM user_permissions WHERE user_id = ? AND permission_id = ?");
$stmt->execute([$userId, $permissionId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($row) . "\n";

echo "\n--- Role Permission for superadmin role ---\n";
$stmt = $db->prepare("SELECT * FROM role_permissions rp JOIN roles r ON rp.role_id = r.id WHERE r.name = 'superadmin' AND rp.permission_id = ?");
$stmt->execute([$permissionId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($row) . "\n";

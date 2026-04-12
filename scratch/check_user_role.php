<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

$email = 'Elavarasan.d@karvytech.in';
$stmt = $db->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "User Details for $email:\n";
print_r($user);

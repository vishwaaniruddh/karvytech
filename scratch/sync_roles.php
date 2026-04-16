<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

$sql = "UPDATE users u JOIN roles r ON u.role = r.name SET u.role_id = r.id WHERE u.role_id IS NULL";
$affectedRows = $db->exec($sql);
echo "Successfully synchronized role_id for $affectedRows users.\n";

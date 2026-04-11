<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

try {
    // 1. Get superadmin role ID
    $stmt = $db->query("SELECT id FROM roles WHERE name = 'superadmin'");
    $superadminRoleId = $stmt->fetchColumn();
    
    if (!$superadminRoleId) {
        throw new Exception("Superadmin role not found.");
    }

    // 2. Get all active permissions
    $stmt = $db->query("SELECT id FROM permissions WHERE status = 'active'");
    $allPerms = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Found " . count($allPerms) . " active permissions.\n";

    // 3. Assign all to superadmin role
    $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) 
                          SELECT ?, ? WHERE NOT EXISTS (
                              SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?
                          )");
    
    $assignedCount = 0;
    foreach ($allPerms as $permId) {
        if ($stmt->execute([$superadminRoleId, $permId, $superadminRoleId, $permId]) && $stmt->rowCount() > 0) {
            $assignedCount++;
        }
    }
    
    echo "Assigned $assignedCount new permissions to superadmin role.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>

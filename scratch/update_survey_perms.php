<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

try {
    // 1. Get survey module ID
    $stmt = $db->query("SELECT id FROM modules WHERE name = 'surveys'");
    $moduleId = $stmt->fetchColumn();
    
    if (!$moduleId) {
        throw new Exception("Surveys module not found.");
    }

    // 2. Add 'reject' permission if it doesn't exist
    $stmt = $db->prepare("SELECT id FROM permissions WHERE module_id = ? AND name = 'reject'");
    $stmt->execute([$moduleId]);
    if (!$stmt->fetch()) {
        $stmt = $db->prepare("INSERT INTO permissions (module_id, name, display_name, description, action) 
                              VALUES (?, 'reject', 'Reject Survey', 'Reject survey submission', 'manage')");
        $stmt->execute([$moduleId]);
        echo "Created 'reject' permission.\n";
    } else {
        echo "'reject' permission already exists.\n";
    }

    // 3. Assign 'approve' and 'reject' to superadmin role (ID 11)
    $stmt = $db->query("SELECT id FROM roles WHERE name = 'superadmin'");
    $superadminRoleId = $stmt->fetchColumn();
    
    if ($superadminRoleId) {
        $neededPerms = ['approve', 'reject'];
        foreach ($neededPerms as $pName) {
            $stmt = $db->prepare("SELECT id FROM permissions WHERE module_id = ? AND name = ?");
            $stmt->execute([$moduleId, $pName]);
            $pId = $stmt->fetchColumn();
            
            if ($pId) {
                $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) 
                                      SELECT ?, ? WHERE NOT EXISTS (
                                          SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?
                                      )");
                $stmt->execute([$superadminRoleId, $pId, $superadminRoleId, $pId]);
                echo "Assigned '$pName' permission to superadmin role.\n";
            }
        }
    } else {
        echo "Superadmin role not found in roles table.\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>

<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $roleId = 4; // Contractor
    $permIds = [14, 7, 10, 25]; // surveys.approve, sites.create, sites.delegate, materials.approve
    
    echo "Revoking permissions for Contractor role (ID $roleId)...\n";
    
    $placeholders = implode(',', array_fill(0, count($permIds), '?'));
    $stmt = $db->prepare("DELETE FROM role_permissions WHERE role_id = ? AND permission_id IN ($placeholders)");
    
    $params = array_merge([$roleId], $permIds);
    $stmt->execute($params);
    
    $count = $stmt->rowCount();
    echo "Successfully removed $count permission mappings.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

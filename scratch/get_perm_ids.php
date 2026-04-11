<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Materials Permissions:\n";
    $stmt = $db->query("
        SELECT p.id, m.name as module, p.name 
        FROM permissions p 
        JOIN modules m ON p.module_id = m.id 
        WHERE m.name = 'materials' AND p.name = 'approve'
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo " - ID {$row['id']}: {$row['module']}.{$row['name']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

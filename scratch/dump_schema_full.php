<?php
require_once __DIR__ . '/../config/database.php';
$c = Database::getInstance()->getConnection();
$tables = ['sites', 'vendors', 'site_surveys', 'installation_delegations', 'material_requests', 'inventory_dispatches', 'inventory_inwards', 'project_category', 'inventory_summary'];
foreach($tables as $t) {
    echo "--- Table: $t ---\n";
    try {
        $cols = $c->query("DESCRIBE $t")->fetchAll(PDO::FETCH_ASSOC);
        foreach($cols as $col) {
            echo "{$col['Field']} ({$col['Type']})\n";
        }
    } catch(Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>

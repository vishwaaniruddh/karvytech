<?php
require_once 'config/database.php';
$db = Database::getInstance()->getConnection();
$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo implode("\n", $tables);
echo "\n\n--- COLUMN DETAILS ---\n";
foreach(['sites','vendors','site_surveys','installation_delegations','material_requests','inventory_dispatches','boq_items','dynamic_survey_responses','site_delegations','users'] as $t) {
    if (in_array($t, $tables)) {
        echo "\n## $t\n";
        $cols = $db->query("DESCRIBE $t")->fetchAll(PDO::FETCH_ASSOC);
        foreach($cols as $c) echo "  {$c['Field']} ({$c['Type']})\n";
        $count = $db->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        echo "  [COUNT: $count]\n";
    }
}

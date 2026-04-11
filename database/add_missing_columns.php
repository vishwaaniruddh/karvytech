<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();
$sql = file_get_contents(__DIR__ . '/add_survey_new_fields.sql');
$statements = array_filter(array_map('trim', explode(';', $sql)));

echo "Adding missing columns to site_surveys table...\n\n";

foreach ($statements as $stmt) {
    if (empty($stmt) || strpos($stmt, '--') === 0) continue;
    
    try {
        $db->exec($stmt);
        if (preg_match('/ADD COLUMN\s+(\w+)/i', $stmt, $matches)) {
            echo "✓ Added: {$matches[1]}\n";
        } elseif (preg_match('/MODIFY COLUMN\s+(\w+)/i', $stmt, $matches)) {
            echo "✓ Modified: {$matches[1]}\n";
        }
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            if (preg_match('/ADD COLUMN\s+(\w+)/i', $stmt, $matches)) {
                echo "⊙ Already exists: {$matches[1]}\n";
            }
        } else {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n✓ Migration complete!\n";
?>

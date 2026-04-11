<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

try {
    $db->exec('ALTER TABLE site_surveys ADD COLUMN floor_height_photo_remarks TEXT NULL');
    echo "✓ Added: floor_height_photo_remarks\n";
} catch (PDOException $e) {
    echo "⊙ floor_height_photo_remarks: " . $e->getMessage() . "\n";
}

try {
    $db->exec('ALTER TABLE site_surveys ADD COLUMN site_accessibility_others VARCHAR(255) NULL');
    echo "✓ Added: site_accessibility_others\n";
} catch (PDOException $e) {
    echo "⊙ site_accessibility_others: " . $e->getMessage() . "\n";
}

echo "\n✓ All columns added!\n";
?>

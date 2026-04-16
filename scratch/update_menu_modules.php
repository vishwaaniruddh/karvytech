<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

echo "Updating root menu module links...\n";

$updates = [
    'Sites' => 2,
    'Inventory' => 5,
    'Reports' => 7,
    'Installation' => 4,
    'Masters' => 8,
    'Survey' => 3,
    'Bulk Operations' => 11,
];

foreach ($updates as $title => $moduleId) {
    $stmt = $db->prepare("UPDATE menu_items SET module_id = ? WHERE title = ? AND parent_id IS NULL");
    $stmt->execute([$moduleId, $title]);
    echo "Updated $title to module $moduleId. Affected rows: " . $stmt->rowCount() . "\n";
}

echo "\nChecking results...\n";
$stmt = $db->query("SELECT id, title, module_id FROM menu_items WHERE parent_id IS NULL");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}

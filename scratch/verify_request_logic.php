<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

// Check the material_id mapping for Request 90
$stmt = $db->prepare("SELECT items FROM material_requests WHERE id = 90");
$stmt->execute();
$items = json_decode($stmt->fetchColumn(), true);

echo "Checking mappings for first 3 items:\n";
foreach (array_slice($items, 0, 3) as $item) {
    $id = $item['material_id'] ?? 'N/A';
    $name = $item['material_name'] ?? 'N/A';
    
    $check = $db->prepare("SELECT item_name FROM boq_items WHERE id = ?");
    $check->execute([$id]);
    $boqName = $check->fetchColumn();
    
    echo "Request Item: $name (ID: $id) -> BOQ Table Match: " . ($boqName ?: "NO MATCH") . "\n";
}

// Check if any requests use 'boq_item_id' directly in JSON
$stmt = $db->query("SELECT id, items FROM material_requests ORDER BY id DESC LIMIT 5");
while($row = $stmt->fetch()) {
    $data = json_decode($row['items'], true);
    $first = $data[0] ?? [];
    echo "Request #{$row['id']} keys: " . implode(', ', array_keys($first)) . "\n";
}
?>

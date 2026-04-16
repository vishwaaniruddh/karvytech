<?php
require_once __DIR__ . '/../config/database.php';
try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Get request #90 items
    $stmt = $db->prepare("SELECT items FROM material_requests WHERE id = 90");
    $stmt->execute();
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    $items = json_decode($request['items'], true);
    
    echo "--- Request #90 Items ---\n";
    print_r($items);
    
    foreach ($items as $item) {
        $id = $item['boq_item_id'] ?? $item['material_id'] ?? null;
        $name = $item['material_name'] ?? $item['item_name'] ?? 'Unknown';
        if (!$id) {
            echo "\n!!! No ID found for item: $name !!!\n";
            continue;
        }
        
        echo "\n--- Checking Stock for ID: $id ($name) ---\n";
        
        // Check if this ID exists in boq_items
        $stmt = $db->prepare("SELECT id, item_name FROM boq_items WHERE id = ?");
        $stmt->execute([$id]);
        $boq = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($boq) {
            echo "Found in boq_items: " . $boq['item_name'] . " (ID: " . $boq['id'] . ")\n";
        } else {
            echo "!!! NOT found in boq_items table !!!\n";
        }
        
        // Check total rows in inventory_stock
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM inventory_stock WHERE boq_item_id = ?");
        $stmt->execute([$id]);
        echo "Total rows in inventory_stock (by boq_item_id): " . $stmt->fetchColumn() . "\n";
        
        // Status Breakdown
        $stmt = $db->prepare("SELECT item_status, quality_status, activity_status, COUNT(*) as count 
                              FROM inventory_stock WHERE boq_item_id = ? 
                              GROUP BY item_status, quality_status, activity_status");
        $stmt->execute([$id]);
        echo "Status Breakdown:\n";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    
} catch (Exception $e) {
    echo $e->getMessage();
}
?>

<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    echo "Starting menu restructure...\n";

    // 1. Move Masters (58) to root if it's not already
    // And ensure its title is 'Masters'
    $stmt = $db->prepare("UPDATE menu_items SET parent_id = NULL, title = 'Masters', sort_order = 10 WHERE id = 58");
    $stmt->execute();
    echo "Updated Masters (ID 58) to root.\n";

    // 2. Move requested items under Masters (58)
    $itemsToMove = [
        10 => 'Users',
        11 => 'Location',
        12 => 'Business',
        13 => 'BOQ'
    ];

    $sortOrder = 1;
    foreach ($itemsToMove as $id => $title) {
        $stmt = $db->prepare("UPDATE menu_items SET parent_id = 58, sort_order = ? WHERE id = ?");
        $stmt->execute([$sortOrder++, $id]);
        echo "Moved $title (ID $id) under Masters.\n";
    }

    // 3. Optional: Move 'Survey' and 'Installation' form makers under a 'Forms' or 'Setup' sub if needed?
    // User structure says:
    // Masters
    // - Users
    // - Location
    // - Business
    // - BOQ
    // So 59 (Survey Form) and 60 (Installation Form) should probably stay under Masters
    // but maybe with lower priority or lower sort_order.
    // I'll keep them under 58 but after the requested items.
    
    $stmt = $db->prepare("UPDATE menu_items SET sort_order = 100 WHERE id = 59");
    $stmt->execute();
    $stmt = $db->prepare("UPDATE menu_items SET sort_order = 101 WHERE id = 60");
    $stmt->execute();

    $db->commit();
    echo "Menu restructure completed successfully!\n";

} catch (Exception $e) {
    if (isset($db)) $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}

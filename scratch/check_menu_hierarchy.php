<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$s = $db->query("SELECT id, parent_id, title, url FROM menu_items WHERE status = 'active' ORDER BY parent_id, sort_order");
$rows = $s->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r) {
    echo sprintf("%2d | %2s | %-20s | %s\n", $r['id'], $r['parent_id'], $r['title'], $r['url']);
}

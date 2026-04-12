<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$s = $db->query('SELECT id, title, sort_order FROM menu_items WHERE parent_id IS NULL AND status = "active" ORDER BY sort_order');
foreach($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "{$r['id']} | {$r['title']} | {$r['sort_order']}\n";
}

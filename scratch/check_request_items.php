<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();
$requestId = 90; // The one mentioned by the user
$stmt = $db->prepare("SELECT items FROM material_requests WHERE id = ?");
$stmt->execute([$requestId]);
$r = $stmt->fetch();
if ($r) {
    echo "Items for Request #$requestId:\n";
    print_r(json_decode($r['items'], true));
} else {
    echo "Request not found.\n";
}
?>

<?php
require_once __DIR__ . '/../models/MaterialRequest.php';

$mr = new MaterialRequest();
$data = $mr->findWithDetails(90);

echo "--- Material Request #90 Details ---\n";
echo "Status: " . $data['status'] . "\n";
echo "Items Raw: [" . $data['items'] . "]\n";
$decoded = json_decode($data['items'], true);
echo "Items Count: " . (is_array($decoded) ? count($decoded) : 0) . "\n";
if (is_array($decoded)) {
    print_r($decoded);
}
?>

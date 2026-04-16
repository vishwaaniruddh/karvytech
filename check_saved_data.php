<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');

$responseId = 12;

$db = Database::getInstance()->getConnection();

// Get the survey response
$stmt = $db->prepare("SELECT * FROM dynamic_survey_responses WHERE id = ?");
$stmt->execute([$responseId]);
$response = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$response) {
    die("Response not found");
}

$formData = json_decode($response['form_data'], true);

echo "<h2>Survey Response ID: $responseId</h2>";
echo "<h3>Survey Status: " . $response['survey_status'] . "</h3>";
echo "<h3>Last Updated: " . $response['updated_at'] . "</h3>";

// Get all fields from Floor Wise Camera Details section
$stmt = $db->prepare("
    SELECT s.id as section_id, s.title, f.id as field_id, f.label
    FROM dynamic_survey_sections s
    JOIN dynamic_survey_fields f ON f.section_id = s.id
    WHERE s.title LIKE '%Floor Wise%'
    ORDER BY f.sort_order
");
$stmt->execute();
$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Floor Wise Camera Details Fields:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field ID</th><th>Label</th><th>Value (Floor 1)</th><th>Value (Floor 2)</th></tr>";

foreach ($fields as $field) {
    $key1 = $field['field_id'] . '_1';
    $key2 = $field['field_id'] . '_2';
    $value1 = $formData[$key1] ?? 'NOT SET';
    $value2 = $formData[$key2] ?? 'NOT SET';
    
    echo "<tr>";
    echo "<td>" . $field['field_id'] . "</td>";
    echo "<td>" . htmlspecialchars($field['label']) . "</td>";
    echo "<td>" . htmlspecialchars($value1) . "</td>";
    echo "<td>" . htmlspecialchars($value2) . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>All Form Data Keys (first 50):</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Key</th><th>Value</th></tr>";

$count = 0;
foreach ($formData as $key => $value) {
    if ($count++ >= 50) break;
    
    $displayValue = is_array($value) ? json_encode($value) : $value;
    if (strlen($displayValue) > 100) {
        $displayValue = substr($displayValue, 0, 100) . '...';
    }
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($key) . "</td>";
    echo "<td>" . htmlspecialchars($displayValue) . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>Raw JSON (first 2000 chars):</h3>";
echo "<pre>" . htmlspecialchars(substr($response['form_data'], 0, 2000)) . "</pre>";

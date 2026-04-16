<?php
require_once __DIR__ . '/config/database.php';

$responseId = 12;

$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT form_data FROM dynamic_survey_responses WHERE id = ?");
$stmt->execute([$responseId]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    $formData = json_decode($result['form_data'], true);
    
    echo "<h2>Form Data for Response ID: $responseId</h2>";
    echo "<pre>";
    print_r($formData);
    echo "</pre>";
    
    echo "<h3>Keys containing 'camera' or 'blind':</h3>";
    echo "<pre>";
    foreach ($formData as $key => $value) {
        $keyLower = strtolower($key);
        if (strpos($keyLower, 'camera') !== false || strpos($keyLower, 'blind') !== false || strpos($keyLower, 'slp') !== false) {
            echo "$key => $value\n";
        }
    }
    echo "</pre>";
    
    // Get field IDs for camera fields
    $stmt = $db->prepare("
        SELECT f.id, f.label, s.title as section_title
        FROM dynamic_survey_fields f
        JOIN dynamic_survey_sections s ON f.section_id = s.id
        WHERE f.label LIKE '%camera%' OR f.label LIKE '%blind%' OR f.label LIKE '%slp%'
        ORDER BY s.sort_order, f.sort_order
    ");
    $stmt->execute();
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Camera/Blind Spot Field IDs:</h3>";
    echo "<pre>";
    print_r($fields);
    echo "</pre>";
    
} else {
    echo "Response not found";
}

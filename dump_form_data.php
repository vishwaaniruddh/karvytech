<?php
require_once __DIR__ . '/config/database.php';

$responseId = 12;
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("SELECT form_data FROM dynamic_survey_responses WHERE id = ?");
$stmt->execute([$responseId]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    $formData = json_decode($result['form_data'], true);
    
    $output = "=== FORM DATA FOR RESPONSE ID $responseId ===\n\n";
    
    // Look for camera-related fields
    $output .= "Camera/Blind Spot Fields:\n";
    foreach ($formData as $key => $value) {
        if (stripos($key, 'camera') !== false || stripos($key, 'blind') !== false || stripos($key, 'slp') !== false || preg_match('/_\d+$/', $key)) {
            $displayValue = is_array($value) ? json_encode($value) : $value;
            $output .= "$key => $displayValue\n";
        }
    }
    
    $output .= "\n\nAll Keys:\n";
    foreach ($formData as $key => $value) {
        $displayValue = is_array($value) ? '[array]' : (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value);
        $output .= "$key => $displayValue\n";
    }
    
    file_put_contents('form_data_dump.txt', $output);
    echo "Data dumped to form_data_dump.txt";
} else {
    echo "Response not found";
}

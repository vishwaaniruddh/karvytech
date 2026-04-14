<?php
require_once 'config/database.php';
try {
    $db = Database::getInstance()->getConnection();
    // 1. Get survey ID for site 621
    $stmt = $db->prepare("SELECT customer_id FROM sites WHERE id = 621");
    $stmt->execute();
    $customerId = $stmt->fetchColumn();
    echo "Customer ID for Site 621: $customerId\n";

    $stmt = $db->prepare("SELECT id FROM dynamic_surveys WHERE customer_id = ? OR (customer_id IS NULL AND status = 'active') ORDER BY customer_id DESC, created_at DESC LIMIT 1");
    $stmt->execute([$customerId]);
    $surveyId = $stmt->fetchColumn();
    echo "Resolved Survey ID: $surveyId\n";

    if ($surveyId) {
        // 2. Get sections for this survey
        $stmt = $db->prepare("SELECT id, title, is_repeatable, repeat_source_field_id FROM dynamic_survey_sections WHERE survey_id = ?");
        $stmt->execute([$surveyId]);
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Sections:\n";
        print_r($sections);

        // 3. Get all fields to see their IDs
        $stmt = $db->prepare("SELECT id, label, section_id FROM dynamic_survey_fields WHERE survey_id = ?");
        $stmt->execute([$surveyId]);
        $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Fields:\n";
        print_r($fields);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

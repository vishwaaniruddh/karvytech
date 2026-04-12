<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Simulate a user session if needed, but the script uses $currentUser['id']
// For this script, we'll just bypass Auth or mock it.
class MockAuth {
    public static function getCurrentUser() { return ['id' => 1]; }
    public static function requireRole($role) {}
}

$db = Database::getInstance()->getConnection();

// 1. Find a submitted response
$stmt = $db->query("SELECT id, site_id FROM dynamic_survey_responses WHERE survey_status = 'submitted' LIMIT 1");
$response = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$response) {
    echo "No submitted survey found. Creating one for site 561...\n";
    $db->exec("INSERT INTO dynamic_survey_responses (survey_form_id, site_id, surveyor_id, survey_status, submitted_date) 
               VALUES (1, 561, 1, 'submitted', NOW())");
    $responseId = $db->lastInsertId();
    $siteId = 561;
} else {
    $responseId = $response['id'];
    $siteId = $response['site_id'];
}

echo "Testing approval for Response #$responseId (Site #$siteId)\n";

// 2. Check initial site status
$stmt = $db->prepare("SELECT survey_status FROM sites WHERE id = ?");
$stmt->execute([$siteId]);
$initialStatus = $stmt->fetchColumn();
echo "Initial sites.survey_status: " . ($initialStatus ?? 'NULL') . "\n";

// 3. Trigger approval (POST simulation)
$_POST['response_id'] = $responseId;
$_POST['action'] = 'approve';
$_POST['remarks'] = 'Automation test approval';

// Mocking the environment for the script
// We'll just run the logic directly or include the file if we can control output
ob_start();
include __DIR__ . '/../admin/surveys/process-survey-action-dynamic.php';
$output = ob_get_clean();

echo "Process Output: $output\n";

// 4. Check final site status
$stmt = $db->prepare("SELECT survey_status FROM sites WHERE id = ?");
$stmt->execute([$siteId]);
$finalStatus = $stmt->fetchColumn();
echo "Final sites.survey_status: " . ($finalStatus ?? 'NULL') . "\n";

if ($finalStatus == 1) {
    echo "SUCCESS: Survey status synchronized correctly!\n";
} else {
    echo "FAILURE: Survey status did not sync.\n";
}

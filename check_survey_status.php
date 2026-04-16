<?php
require_once 'config/database.php';

$siteId = 625;

$db = Database::getInstance()->getConnection();

// Get delegation ID
$stmt = $db->prepare("SELECT id FROM site_delegations WHERE site_id = ?");
$stmt->execute([$siteId]);
$delegation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$delegation) {
    echo "No delegation found for site $siteId\n";
    exit;
}

$delegationId = $delegation['id'];
echo "Delegation ID: $delegationId\n\n";

// Check for existing survey
$stmt = $db->prepare("SELECT id, survey_status, is_draft, approval_status, survey_started_at, survey_ended_at 
                     FROM dynamic_survey_responses 
                     WHERE delegation_id = ? 
                     ORDER BY id DESC LIMIT 1");
$stmt->execute([$delegationId]);
$survey = $stmt->fetch(PDO::FETCH_ASSOC);

if ($survey) {
    echo "Existing survey found:\n";
    echo json_encode($survey, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "No survey found for delegation $delegationId\n";
}

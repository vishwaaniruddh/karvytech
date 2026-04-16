<?php
require_once 'config/database.php';

$responseId = 12;

$db = Database::getInstance()->getConnection();

// Fetch survey response
$stmt = $db->prepare("SELECT * FROM dynamic_survey_responses WHERE id = ?");
$stmt->execute([$responseId]);
$response = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$response) {
    echo "Survey response not found!\n";
    exit;
}

echo "=== Survey Response Data ===\n";
echo "ID: {$response['id']}\n";
echo "Site ID: {$response['site_id']}\n";
echo "Survey Form ID: {$response['survey_form_id']}\n";
echo "Survey Status: {$response['survey_status']}\n";
echo "\n";

echo "=== Site Master Data (JSON) ===\n";
echo $response['site_master_data'] ?: '(empty)';
echo "\n\n";

$siteMasterData = json_decode($response['site_master_data'], true);
echo "=== Site Master Data (Decoded) ===\n";
if (empty($siteMasterData)) {
    echo "(empty or null)\n";
} else {
    print_r($siteMasterData);
}
echo "\n";

// Try to fetch from sites table
if ($response['site_id']) {
    $stmt = $db->prepare("SELECT * FROM sites WHERE id = ?");
    $stmt->execute([$response['site_id']]);
    $siteData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "=== Site Data from Sites Table ===\n";
    if ($siteData) {
        echo "Site ID: {$siteData['site_id']}\n";
        echo "Store ID: {$siteData['store_id']}\n";
        echo "Location: {$siteData['location']}\n";
        echo "City: {$siteData['city']}\n";
        echo "State: {$siteData['state']}\n";
        echo "Country: {$siteData['country']}\n";
        echo "Zone: {$siteData['zone']}\n";
        echo "Customer: {$siteData['customer']}\n";
    } else {
        echo "(not found)\n";
    }
}

echo "\n=== Form Data (first 500 chars) ===\n";
echo substr($response['form_data'], 0, 500);

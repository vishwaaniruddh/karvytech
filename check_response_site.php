<?php
require_once __DIR__ . '/config/database.php';

$responseId = 12;
$db = Database::getInstance()->getConnection();

// Check what's in the survey response
$stmt = $db->prepare("SELECT id, site_id, survey_form_id, surveyor_id FROM dynamic_survey_responses WHERE id = ?");
$stmt->execute([$responseId]);
$response = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h2>Survey Response #$responseId</h2>";
echo "<pre>";
print_r($response);
echo "</pre>";

if ($response && $response['site_id']) {
    echo "<h3>Looking for site with site_id = '" . $response['site_id'] . "'</h3>";
    
    // Try to find the site
    $stmt = $db->prepare("SELECT * FROM sites WHERE site_id = ?");
    $stmt->execute([$response['site_id']]);
    $site = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($site) {
        echo "<h3>Site Found:</h3>";
        echo "<pre>";
        print_r($site);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>No site found with site_id = '" . $response['site_id'] . "'</p>";
        
        // Show all sites
        $stmt = $db->query("SELECT id, site_id, store_id, city FROM sites LIMIT 10");
        $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Available sites (first 10):</h3>";
        echo "<pre>";
        print_r($sites);
        echo "</pre>";
    }
} else {
    echo "<p style='color: red;'>site_id is NULL or empty in survey response!</p>";
}

// Test the join
echo "<h3>Testing JOIN query:</h3>";
$stmt = $db->prepare("
    SELECT sr.id, sr.site_id as response_site_id, 
           s.id as sites_id, s.site_id as sites_site_id, s.city
    FROM dynamic_survey_responses sr
    LEFT JOIN sites s ON sr.site_id = s.site_id
    WHERE sr.id = ?
");
$stmt->execute([$responseId]);
$joinResult = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($joinResult);
echo "</pre>";

<?php
require_once __DIR__ . '/config/database.php';

$responseId = 12;
$db = Database::getInstance()->getConnection();

// Check what site_id is in the response
$stmt = $db->prepare("SELECT site_id FROM dynamic_survey_responses WHERE id = ?");
$stmt->execute([$responseId]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>Survey Response site_id: " . ($result['site_id'] ?? 'NULL') . "</h3>";

if ($result && $result['site_id']) {
    // Try to find the site
    $stmt = $db->prepare("SELECT * FROM sites WHERE id = ?");
    $stmt->execute([$result['site_id']]);
    $site = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Site found by ID:</h3>";
    if ($site) {
        echo "<pre>";
        print_r($site);
        echo "</pre>";
    } else {
        echo "<p>No site found with id = " . $result['site_id'] . "</p>";
        
        // Try to find by site_id column
        $stmt = $db->prepare("SELECT * FROM sites WHERE site_id = ?");
        $stmt->execute([$result['site_id']]);
        $site = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Site found by site_id column:</h3>";
        if ($site) {
            echo "<pre>";
            print_r($site);
            echo "</pre>";
        } else {
            echo "<p>No site found with site_id = " . $result['site_id'] . "</p>";
        }
    }
}

// Check the join query
$stmt = $db->prepare("
    SELECT sr.site_id, s.id as sites_table_id, s.site_id as sites_site_id, s.city, s.state
    FROM dynamic_survey_responses sr
    LEFT JOIN sites s ON sr.site_id = s.id
    WHERE sr.id = ?
");
$stmt->execute([$responseId]);
$joinResult = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>Join Query Result:</h3>";
echo "<pre>";
print_r($joinResult);
echo "</pre>";

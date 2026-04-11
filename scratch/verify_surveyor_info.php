<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Site.php';

try {
    $siteModel = new Site();
    $result = $siteModel->getAllWithPagination(1, 5);
    
    echo "Results from getAllWithPagination:\n";
    foreach ($result['sites'] as $site) {
        echo "Site ID: {$site['site_id']}\n";
        echo "Surveyor: " . ($site['surveyor_name'] ?? 'NULL') . "\n";
        echo "Date: " . ($site['survey_submitted_date'] ?? 'NULL') . "\n";
        echo "-------------------\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

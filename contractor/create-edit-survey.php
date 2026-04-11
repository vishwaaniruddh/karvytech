<?php
// Script to create edit-survey.php from site-survey.php with modifications
$source = file_get_contents('site-survey.php');

// Replace header logic
$source = preg_replace(
    '/\$delegationId = \$_GET\[\'delegation_id\'\] \?\? null;/',
    '$surveyId = $_GET[\'id\'] ?? null;',
    $source
);

$source = preg_replace(
    '/if \(\!\$delegationId\) \{/',
    'if (!$surveyId) {',
    $source
);

$source = str_replace('Location: sites/', 'Location: surveys.php', $source);
$source = str_replace('action="process-survey-comprehensive.php"', 'action="process-survey-update.php"', $source);
$source = str_replace('Site Feasibility Survey', 'Edit Site Survey', $source);
$source = str_replace('Complete comprehensive feasibility assessment', 'Update your site feasibility assessment', $source);
$source = str_replace('Back to Sites', 'Back to Surveys', $source);
$source = str_replace('href="sites/"', 'href="surveys.php"', $source);

// Add survey loading and permission check after authentication
$newHeader = '<?php
require_once __DIR__ . \'/../config/auth.php\';
require_once __DIR__ . \'/../models/Site.php\';
require_once __DIR__ . \'/../models/SiteDelegation.php\';
require_once __DIR__ . \'/../models/SiteSurvey.php\';

// Require vendor authentication
Auth::requireVendor();

$surveyId = $_GET[\'id\'] ?? null;
if (!$surveyId) {
    header(\'Location: surveys.php\');
    exit;
}

$vendorId = Auth::getVendorId();
$surveyModel = new SiteSurvey();

// Get survey details
$survey = $surveyModel->findWithDetails($surveyId);
if (!$survey || $survey[\'vendor_id\'] != $vendorId) {
    header(\'Location: surveys.php\');
    exit;
}

// Only allow editing if status is pending or rejected
if (!in_array($survey[\'survey_status\'], [\'pending\', \'rejected\'])) {
    header(\'Location: ../shared/view-survey.php?id=\' . $surveyId);
    exit;
}

$siteModel = new Site();
$delegationModel = new SiteDelegation();

// Get site and delegation details
$site = $siteModel->findWithRelations($survey[\'site_id\']);
$delegation = $delegationModel->find($survey[\'delegation_id\']);

if (!$site) {
    header(\'Location: surveys.php\');
    exit;
}

$title = \'Edit Survey - \' . $site[\'site_id\'];
ob_start();
?>';

$source = preg_replace('/<\?php.*?ob_start\(\);.*?\?>/s', $newHeader, $source, 1);

file_put_contents('edit-survey.php', $source);
echo "edit-survey.php created successfully!\n";
?>

<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

// Auth::requireRole(ADMIN_ROLE);

$formId = $_GET['form_id'] ?? null;
if (!$formId) {
    die("Form ID is required");
}

$db = Database::getInstance()->getConnection();

// Get Form Details
$stmt = $db->prepare("SELECT title FROM dynamic_surveys WHERE id = ?");
$stmt->execute([$formId]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$form) die("Form not found");

// Get Fields
$stmt = $db->prepare("
    SELECT f.label 
    FROM dynamic_survey_fields f
    JOIN dynamic_survey_sections s ON f.section_id = s.id
    WHERE s.survey_id = ? AND f.field_type != 'section'
    ORDER BY s.sort_order, f.sort_order
");
$stmt->execute([$formId]);
$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = "Survey_Template_" . str_replace(' ', '_', $form['title']) . "_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Headers
$headers = ['Site ID'];
foreach ($fields as $field) {
    $headers[] = $field['label'];
}

fputcsv($output, $headers);

// Sample row (optional)
$sample = ['SITE001'];
foreach ($fields as $field) {
    $sample[] = '';
}
fputcsv($output, $sample);

fclose($output);
exit;

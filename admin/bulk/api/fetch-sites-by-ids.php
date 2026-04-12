<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/auth.php';

// Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$query = $input['query'] ?? '';

if (empty($query)) {
    echo json_encode(['success' => false, 'message' => 'No IDs provided']);
    exit;
}

// Split by comma or space
$idStrings = preg_split('/[\s,]+/', $query, -1, PREG_SPLIT_NO_EMPTY);
$idStrings = array_unique(array_map('trim', $idStrings));

if (empty($idStrings)) {
     echo json_encode(['success' => false, 'message' => 'Invalid IDs provided']);
    exit;
}

$db = Database::getInstance()->getConnection();

// Prepare placeholders
$placeholders = implode(',', array_fill(0, count($idStrings), '?'));

// SQL to fetch sites and their LATEST survey status (either dynamic or legacy)
// We prioritize dynamic surveys if both exist
$sql = "
    SELECT s.id, s.site_id, s.location,
           COALESCE(dsr.survey_status, ss.survey_status) as approval_status,
           CASE 
               WHEN dsr.id IS NOT NULL THEN 'dynamic'
               ELSE 'legacy'
           END as survey_type
    FROM sites s
    LEFT JOIN (
        SELECT d1.site_id, d1.survey_status, d1.id
        FROM dynamic_survey_responses d1
        INNER JOIN (SELECT site_id, MAX(id) as max_id FROM dynamic_survey_responses GROUP BY site_id) d2 
        ON d1.id = d2.max_id
    ) dsr ON s.id = dsr.site_id
    LEFT JOIN (
        SELECT s1.site_id, s1.survey_status, s1.id
        FROM site_surveys s1
        INNER JOIN (SELECT site_id, MAX(id) as max_id FROM site_surveys GROUP BY site_id) s2 
        ON s1.id = s2.max_id
    ) ss ON s.id = ss.site_id
    WHERE s.site_id IN ($placeholders)
    AND s.deleted_at IS NULL
";

$stmt = $db->prepare($sql);
$stmt->execute($idStrings);
$sites = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true, 
    'sites' => $sites,
    'found_count' => count($sites),
    'requested_count' => count($idStrings)
]);

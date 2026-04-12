<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../models/Site.php';

// Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

$formId = $_POST['form_id'] ?? null;
if (!$formId || !isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'Missing form ID or file']);
    exit;
}

$db = Database::getInstance()->getConnection();
$siteModel = new Site();

// 1. Get Field Mapping (Label -> ID + Type)
$stmt = $db->prepare("
    SELECT f.id, f.label, f.field_type 
    FROM dynamic_survey_fields f
    JOIN dynamic_survey_sections s ON f.section_id = s.id
    WHERE s.survey_id = ?
");
$stmt->execute([$formId]);
$fieldMapping = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $fieldMapping[trim($row['label'])] = [
        'id' => $row['id'],
        'type' => $row['field_type']
    ];
}

$file = $_FILES['file'];
$results = ['success' => true, 'imported' => 0, 'failed' => 0, 'rows' => []];

try {
    if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ",");
        $headers = array_map('trim', $headers);
        
        $rowNum = 1;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $rowNum++;
            $siteCode = trim($data[0] ?? '');
            if (empty($siteCode)) continue;

            $rowResult = ['row' => $rowNum, 'site_id' => $siteCode, 'status' => 'success', 'message' => ''];

            try {
                // Find Site
                $site = $siteModel->findBySiteId($siteCode);
                if (!$site) {
                    throw new Exception("Site ID '$siteCode' not found in database.");
                }

                // Construct Form Data
                $formData = [];
                $imagesToProcess = [];

                for ($i = 1; $i < count($headers); $i++) {
                    $label = $headers[$i];
                    if (isset($fieldMapping[$label])) {
                        $fieldInfo = $fieldMapping[$label];
                        $val = trim($data[$i] ?? '');
                        
                        // Handle File Type (Image URLs)
                        if ($fieldInfo['type'] === 'file' && !empty($val)) {
                            $imagesToProcess[$fieldInfo['id']] = $val;
                            // Initially put the URL, we'll replace after insert (once we have ID)
                            $formData[$fieldInfo['id']] = $val;
                        } else {
                            $formData[$fieldInfo['id']] = $val;
                        }
                    }
                }

                // Insert Response (Preliminary)
                $stmt = $db->prepare("
                    INSERT INTO dynamic_survey_responses 
                    (site_id, survey_form_id, surveyor_id, survey_status, submitted_date, form_data)
                    VALUES (?, ?, ?, 'approved', NOW(), ?)
                ");
                
                $stmt->execute([
                    $site['id'],
                    $formId,
                    $_SESSION['user_id'] ?? 0,
                    json_encode($formData)
                ]);

                $responseId = $db->lastInsertId();

                // Process Images if any
                if (!empty($imagesToProcess)) {
                    $surveyDir = "uploads/site_survey/$responseId/";
                    $fullSurveyDir = __DIR__ . "/../../../" . $surveyDir;
                    if (!is_dir($fullSurveyDir)) mkdir($fullSurveyDir, 0755, true);

                    $updatedFormData = $formData;
                    foreach ($imagesToProcess as $fieldId => $tmpUrl) {
                        // Extract relative path if absolute URL is provided
                        $relativePath = $tmpUrl;
                        if (strpos($tmpUrl, 'uploads/tmp_images/') !== false) {
                            $parts = explode('uploads/tmp_images/', $tmpUrl);
                            $relativePath = 'uploads/tmp_images/' . end($parts);
                        }
                        
                        $tmpPath = __DIR__ . "/../../../" . $relativePath;
                        if (file_exists($tmpPath)) {
                            $filename = basename($tmpPath);
                            $newPath = $surveyDir . $filename;
                            copy($tmpPath, __DIR__ . "/../../../" . $newPath);
                            
                            // Format according to system expectations (like SiteSurvey model)
                            $updatedFormData[$fieldId] = [
                                'file_path' => $newPath,
                                'original_name' => $filename,
                                'type' => mime_content_type($tmpPath)
                            ];
                        }
                    }

                    // Update with final JSON
                    $stmt = $db->prepare("UPDATE dynamic_survey_responses SET form_data = ? WHERE id = ?");
                    $stmt->execute([json_encode($updatedFormData), $responseId]);
                }

                $results['imported']++;
                $rowResult['message'] = 'Imported successfully with ' . count($imagesToProcess) . ' photos.';
            } catch (Exception $e) {
                $results['failed']++;
                $rowResult['status'] = 'failed';
                $rowResult['message'] = $e->getMessage();
            }
            $results['rows'][] = $rowResult;
        }
        fclose($handle);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

echo json_encode($results);

<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/DynamicSurvey.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$surveyModel = new DynamicSurvey();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data) throw new Exception("Invalid JSON payload");

        $id = $data['id'] ?? null;
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();

        // Create revision before updating (if editing existing survey)
        if ($id) {
            // Get current revision number
            $stmt = $db->prepare("SELECT COALESCE(MAX(revision_number), 0) + 1 as next_revision FROM dynamic_survey_revisions WHERE survey_id = ?");
            $stmt->execute([$id]);
            $nextRevision = $stmt->fetchColumn();
            
            // Get current survey data
            $currentSurvey = $surveyModel->find($id);
            
            // Get current sections and fields
            $stmt = $db->prepare("SELECT * FROM dynamic_survey_sections WHERE survey_id = ? ORDER BY sort_order ASC");
            $stmt->execute([$id]);
            $currentSections = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($currentSections as &$section) {
                $stmt = $db->prepare("SELECT * FROM dynamic_survey_fields WHERE section_id = ? ORDER BY sort_order ASC");
                $stmt->execute([$section['id']]);
                $section['fields'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get subsections
                $stmt = $db->prepare("SELECT * FROM dynamic_survey_sections WHERE parent_section_id = ? ORDER BY sort_order ASC");
                $stmt->execute([$section['id']]);
                $subsections = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($subsections as &$subsection) {
                    $stmt = $db->prepare("SELECT * FROM dynamic_survey_fields WHERE section_id = ? ORDER BY sort_order ASC");
                    $stmt->execute([$subsection['id']]);
                    $subsection['fields'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
                $section['subsections'] = $subsections;
            }
            
            // Save revision
            $revisionData = [
                'survey' => $currentSurvey,
                'sections' => $currentSections
            ];
            
            $stmt = $db->prepare("INSERT INTO dynamic_survey_revisions (survey_id, revision_number, title, description, form_data, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $id,
                $nextRevision,
                $currentSurvey['title'],
                $currentSurvey['description'],
                json_encode($revisionData),
                $_SESSION['user_id'] ?? null
            ]);
        }

        // 1. Save main survey info
        $surveyData = [
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'status' => $data['status'] ?? 'active',
            'form_type' => $data['form_type'] ?? 'survey',
            'customer_id' => $data['customer_id'] ?: null
        ];

        if ($id) {
            $surveyModel->update($id, $surveyData);
        } else {
            $id = $surveyModel->create($surveyData);
        }

        // 2. Clear existing sections and fields (Simpler for V2 redesign)
        // Note: In production, you might want to sync instead of delete.
        $db->prepare("DELETE FROM dynamic_survey_fields WHERE survey_id = ?")->execute([$id]); // Delete all fields first
        $db->prepare("DELETE FROM dynamic_survey_sections WHERE survey_id = ?")->execute([$id]); // Then delete sections

        // 3. Save sections, subsections, and nested fields
        foreach ($data['sections'] as $sIndex => $section) {
            // Save main section
            $stmt = $db->prepare("INSERT INTO dynamic_survey_sections (survey_id, parent_section_id, title, description, sort_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id, null, $section['title'], $section['description'] ?? '', $sIndex]);
            $sectionId = $db->lastInsertId();

            // Save fields directly in the main section
            foreach ($section['fields'] as $fIndex => $field) {
                $stmt = $db->prepare("INSERT INTO dynamic_survey_fields (
                    survey_id, section_id, label, placeholder, field_width, default_value, help_text, 
                    field_type, is_required, allow_negative, allow_multiple, max_files, 
                    file_type_restriction, custom_file_types, max_file_size, show_preview,
                    options, field_config, validation_rules, conditional_logic, sort_order
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $id,
                    $sectionId,
                    $field['label'],
                    $field['placeholder'] ?? null,
                    $field['field_width'] ?? 'full',
                    $field['default_value'] ?? null,
                    $field['help_text'] ?? null,
                    $field['field_type'],
                    ($field['is_required'] ?? false) ? 1 : 0,
                    ($field['allow_negative'] ?? true) ? 1 : 0,
                    ($field['allow_multiple'] ?? false) ? 1 : 0,
                    $field['max_files'] ?? 5,
                    $field['file_type_restriction'] ?? '',
                    $field['custom_file_types'] ?? '',
                    $field['max_file_size'] ?? 5,
                    ($field['show_preview'] ?? true) ? 1 : 0,
                    $field['options'] ?? null,
                    json_encode($field['field_config'] ?? []),
                    json_encode($field['validation_rules'] ?? []),
                    json_encode($field['conditional_logic'] ?? []),
                    $fIndex
                ]);
            }

            // Save subsections
            if (isset($section['subsections']) && is_array($section['subsections'])) {
                foreach ($section['subsections'] as $subIndex => $subsection) {
                    $stmt = $db->prepare("INSERT INTO dynamic_survey_sections (survey_id, parent_section_id, title, description, sort_order) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$id, $sectionId, $subsection['title'], $subsection['description'] ?? '', $subIndex]);
                    $subsectionId = $db->lastInsertId();

                    // Save fields in subsection
                    foreach ($subsection['fields'] as $fIndex => $field) {
                        $stmt = $db->prepare("INSERT INTO dynamic_survey_fields (
                            survey_id, section_id, label, placeholder, field_width, default_value, help_text, 
                            field_type, is_required, allow_negative, allow_multiple, max_files, 
                            file_type_restriction, custom_file_types, max_file_size, show_preview,
                            options, field_config, validation_rules, conditional_logic, sort_order
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        
                        $stmt->execute([
                            $id,
                            $subsectionId,
                            $field['label'],
                            $field['placeholder'] ?? null,
                            $field['field_width'] ?? 'full',
                            $field['default_value'] ?? null,
                            $field['help_text'] ?? null,
                            $field['field_type'],
                            ($field['is_required'] ?? false) ? 1 : 0,
                            ($field['allow_negative'] ?? true) ? 1 : 0,
                            ($field['allow_multiple'] ?? false) ? 1 : 0,
                            $field['max_files'] ?? 5,
                            $field['file_type_restriction'] ?? '',
                            $field['custom_file_types'] ?? '',
                            $field['max_file_size'] ?? 5,
                            ($field['show_preview'] ?? true) ? 1 : 0,
                            $field['options'] ?? null,
                            json_encode($field['field_config'] ?? []),
                            json_encode($field['validation_rules'] ?? []),
                            json_encode($field['conditional_logic'] ?? []),
                            $fIndex
                        ]);
                    }
                }
            }
        }

        $db->commit();
        echo json_encode(['success' => true, 'id' => $id]);
    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'load') {
    $id = $_GET['id'] ?? null;
    if (!$id) die(json_encode(['success' => false, 'message' => 'ID required']));

    $survey = $surveyModel->find($id);
    if (!$survey) die(json_encode(['success' => false, 'message' => 'Survey not found']));

    $db = Database::getInstance()->getConnection();
    
    // Fetch main sections (parent_section_id IS NULL)
    $stmt = $db->prepare("SELECT * FROM dynamic_survey_sections WHERE survey_id = ? AND parent_section_id IS NULL ORDER BY sort_order ASC");
    $stmt->execute([$id]);
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch fields and subsections for each main section
    foreach ($sections as &$section) {
        // Get fields for this section
        $stmt = $db->prepare("SELECT * FROM dynamic_survey_fields WHERE section_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$section['id']]);
        $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Decode JSON blobs
        foreach ($fields as &$field) {
            $field['field_config'] = json_decode($field['field_config'] ?? '[]', true);
            $field['validation_rules'] = json_decode($field['validation_rules'] ?? '[]', true);
            $field['conditional_logic'] = json_decode($field['conditional_logic'] ?? '[]', true);
            $field['is_required'] = (bool)$field['is_required'];
            $field['allow_negative'] = (bool)($field['allow_negative'] ?? true);
            $field['allow_multiple'] = (bool)($field['allow_multiple'] ?? false);
            $field['show_preview'] = (bool)($field['show_preview'] ?? true);
        }
        $section['fields'] = $fields;

        // Get subsections for this section
        $stmt = $db->prepare("SELECT * FROM dynamic_survey_sections WHERE parent_section_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$section['id']]);
        $subsections = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($subsections as &$subsection) {
            // Get fields for this subsection
            $stmt = $db->prepare("SELECT * FROM dynamic_survey_fields WHERE section_id = ? ORDER BY sort_order ASC");
            $stmt->execute([$subsection['id']]);
            $subFields = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON blobs
            foreach ($subFields as &$subField) {
                $subField['field_config'] = json_decode($subField['field_config'] ?? '[]', true);
                $subField['validation_rules'] = json_decode($subField['validation_rules'] ?? '[]', true);
                $subField['conditional_logic'] = json_decode($subField['conditional_logic'] ?? '[]', true);
                $subField['is_required'] = (bool)$subField['is_required'];
                $subField['allow_negative'] = (bool)($subField['allow_negative'] ?? true);
                $subField['allow_multiple'] = (bool)($subField['allow_multiple'] ?? false);
                $subField['show_preview'] = (bool)($subField['show_preview'] ?? true);
            }
            $subsection['fields'] = $subFields;
        }
        $section['subsections'] = $subsections;
    }

    echo json_encode(['success' => true, 'survey' => $survey, 'sections' => $sections]);
    exit;
}

if ($action === 'get_customers') {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, name FROM customers WHERE status = 'active' ORDER BY name ASC");
        $stmt->execute();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'customers' => $customers]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $action === 'delete') {
    try {
        $id = $_GET['id'] ?? null;
        if (!$id) throw new Exception("Survey ID required");
        
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        
        // Delete fields first (foreign key constraint)
        $db->prepare("DELETE FROM dynamic_survey_fields WHERE survey_id = ?")->execute([$id]);
        
        // Delete sections
        $db->prepare("DELETE FROM dynamic_survey_sections WHERE survey_id = ?")->execute([$id]);
        
        // Delete the survey
        $db->prepare("DELETE FROM dynamic_surveys WHERE id = ?")->execute([$id]);
        
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Survey deleted successfully']);
    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'get_revisions') {
    try {
        $id = $_GET['id'] ?? null;
        if (!$id) throw new Exception("Survey ID required");
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, revision_number, title, description, created_at, created_by FROM dynamic_survey_revisions WHERE survey_id = ? ORDER BY revision_number DESC");
        $stmt->execute([$id]);
        $revisions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'revisions' => $revisions]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'restore_revision') {
    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $revisionId = $data['revision_id'] ?? null;
        
        if (!$revisionId) throw new Exception("Revision ID required");
        
        $db = Database::getInstance()->getConnection();
        
        // Get revision data
        $stmt = $db->prepare("SELECT survey_id, form_data FROM dynamic_survey_revisions WHERE id = ?");
        $stmt->execute([$revisionId]);
        $revision = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$revision) throw new Exception("Revision not found");
        
        $revisionData = json_decode($revision['form_data'], true);
        $surveyId = $revision['survey_id'];
        
        $db->beginTransaction();
        
        // Create a new revision of current state before restoring
        $stmt = $db->prepare("SELECT COALESCE(MAX(revision_number), 0) + 1 as next_revision FROM dynamic_survey_revisions WHERE survey_id = ?");
        $stmt->execute([$surveyId]);
        $nextRevision = $stmt->fetchColumn();
        
        $currentSurvey = $surveyModel->find($surveyId);
        $stmt = $db->prepare("SELECT * FROM dynamic_survey_sections WHERE survey_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$surveyId]);
        $currentSections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($currentSections as &$section) {
            $stmt = $db->prepare("SELECT * FROM dynamic_survey_fields WHERE section_id = ? ORDER BY sort_order ASC");
            $stmt->execute([$section['id']]);
            $section['fields'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $db->prepare("SELECT * FROM dynamic_survey_sections WHERE parent_section_id = ? ORDER BY sort_order ASC");
            $stmt->execute([$section['id']]);
            $subsections = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($subsections as &$subsection) {
                $stmt = $db->prepare("SELECT * FROM dynamic_survey_fields WHERE section_id = ? ORDER BY sort_order ASC");
                $stmt->execute([$subsection['id']]);
                $subsection['fields'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            $section['subsections'] = $subsections;
        }
        
        $backupData = ['survey' => $currentSurvey, 'sections' => $currentSections];
        $stmt = $db->prepare("INSERT INTO dynamic_survey_revisions (survey_id, revision_number, title, description, form_data, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$surveyId, $nextRevision, $currentSurvey['title'] . ' (Before Restore)', $currentSurvey['description'], json_encode($backupData), $_SESSION['user_id'] ?? null]);
        
        // Update survey with revision data
        $surveyModel->update($surveyId, [
            'title' => $revisionData['survey']['title'],
            'description' => $revisionData['survey']['description'],
            'status' => $revisionData['survey']['status'],
            'form_type' => $revisionData['survey']['form_type'],
            'customer_id' => $revisionData['survey']['customer_id']
        ]);
        
        // Delete existing sections and fields
        $db->prepare("DELETE FROM dynamic_survey_fields WHERE survey_id = ?")->execute([$surveyId]);
        $db->prepare("DELETE FROM dynamic_survey_sections WHERE survey_id = ?")->execute([$surveyId]);
        
        // Restore sections and fields from revision
        foreach ($revisionData['sections'] as $sIndex => $section) {
            if ($section['parent_section_id'] === null) {
                $stmt = $db->prepare("INSERT INTO dynamic_survey_sections (survey_id, parent_section_id, title, description, sort_order) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$surveyId, null, $section['title'], $section['description'], $sIndex]);
                $sectionId = $db->lastInsertId();
                
                // Restore fields
                foreach ($section['fields'] as $fIndex => $field) {
                    $stmt = $db->prepare("INSERT INTO dynamic_survey_fields (survey_id, section_id, label, placeholder, field_width, default_value, help_text, field_type, is_required, options, field_config, validation_rules, conditional_logic, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $surveyId, $sectionId, $field['label'], $field['placeholder'], $field['field_width'] ?? 'full',
                        $field['default_value'], $field['help_text'], $field['field_type'], $field['is_required'],
                        $field['options'], $field['field_config'], $field['validation_rules'], $field['conditional_logic'], $fIndex
                    ]);
                }
                
                // Restore subsections
                if (isset($section['subsections'])) {
                    foreach ($section['subsections'] as $subIndex => $subsection) {
                        $stmt = $db->prepare("INSERT INTO dynamic_survey_sections (survey_id, parent_section_id, title, description, sort_order) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$surveyId, $sectionId, $subsection['title'], $subsection['description'], $subIndex]);
                        $subsectionId = $db->lastInsertId();
                        
                        foreach ($subsection['fields'] as $fIndex => $field) {
                            $stmt = $db->prepare("INSERT INTO dynamic_survey_fields (survey_id, section_id, label, placeholder, field_width, default_value, help_text, field_type, is_required, options, field_config, validation_rules, conditional_logic, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([
                                $surveyId, $subsectionId, $field['label'], $field['placeholder'], $field['field_width'] ?? 'full',
                                $field['default_value'], $field['help_text'], $field['field_type'], $field['is_required'],
                                $field['options'], $field['field_config'], $field['validation_rules'], $field['conditional_logic'], $fIndex
                            ]);
                        }
                    }
                }
            }
        }
        
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Revision restored successfully']);
    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'get_response') {
    try {
        $responseId = $_GET['response_id'] ?? null;
        if (!$responseId) throw new Exception("Response ID required");
        
        $db = Database::getInstance()->getConnection();
        
        // Get survey response
        $stmt = $db->prepare("SELECT sr.*, ds.title as survey_title, ds.description as survey_description,
                              CONCAT_WS(' ', u.first_name, u.last_name) as surveyor_name, s.site_id as site_code
                              FROM dynamic_survey_responses sr
                              LEFT JOIN dynamic_surveys ds ON sr.survey_form_id = ds.id
                              LEFT JOIN users u ON sr.surveyor_id = u.id
                              LEFT JOIN sites s ON sr.site_id = s.id
                              WHERE sr.id = ?");
        $stmt->execute([$responseId]);
        $response = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$response) throw new Exception("Response not found");
        
        $response['form_data'] = json_decode($response['form_data'], true);
        $response['site_master_data'] = json_decode($response['site_master_data'], true);
        
        // Get survey structure - ONLY parent sections
        $stmt = $db->prepare("SELECT * FROM dynamic_survey_sections WHERE survey_id = ? AND parent_section_id IS NULL ORDER BY sort_order ASC");
        $stmt->execute([$response['survey_form_id']]);
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($sections as &$section) {
            // Get direct fields for this section
            $stmt = $db->prepare("SELECT * FROM dynamic_survey_fields WHERE section_id = ? ORDER BY sort_order ASC");
            $stmt->execute([$section['id']]);
            $section['fields'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get subsections
            $stmt = $db->prepare("SELECT * FROM dynamic_survey_sections WHERE parent_section_id = ? ORDER BY sort_order ASC");
            $stmt->execute([$section['id']]);
            $subsections = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($subsections as &$subsection) {
                $stmt = $db->prepare("SELECT * FROM dynamic_survey_fields WHERE section_id = ? ORDER BY sort_order ASC");
                $stmt->execute([$subsection['id']]);
                $subsection['fields'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            $section['subsections'] = $subsections;
        }
        
        echo json_encode([
            'success' => true,
            'response' => $response,
            'sections' => $sections
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>

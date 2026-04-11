<?php
require_once __DIR__ . '/BaseModel.php';

class DynamicSurvey extends BaseModel {
    protected $table = 'dynamic_surveys';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getFields($surveyId) {
        $stmt = $this->db->prepare("SELECT * FROM dynamic_survey_fields WHERE survey_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$surveyId]);
        return $stmt->fetchAll();
    }

    public function getSections($surveyId) {
        $stmt = $this->db->prepare("SELECT * FROM dynamic_survey_sections WHERE survey_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$surveyId]);
        return $stmt->fetchAll();
    }

    public function getFieldsBySection($sectionId) {
        $stmt = $this->db->prepare("SELECT * FROM dynamic_survey_fields WHERE section_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$sectionId]);
        return $stmt->fetchAll();
    }
    
    public function createWithFields($data, $fields) {
        try {
            $this->db->beginTransaction();
            $surveyId = $this->create($data);
            foreach ($fields as $index => $field) {
                $field['survey_id'] = $surveyId;
                $field['sort_order'] = $index;
                $this->db->prepare("INSERT INTO dynamic_survey_fields (survey_id, label, field_type, is_required, options, file_config, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)")
                    ->execute([
                        $field['survey_id'],
                        $field['label'],
                        $field['field_type'],
                        $field['is_required'] ?? 0,
                        $field['options'] ?? null,
                        $field['file_config'] ?? null,
                        $field['sort_order']
                    ]);
            }
            $this->db->commit();
            return $surveyId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function createWithSectionsAndFields($data, $sections) {
        try {
            $this->db->beginTransaction();
            
            $surveyId = $this->create($data);
            
            foreach ($sections as $sIndex => $section) {
                $stmt = $this->db->prepare("INSERT INTO dynamic_survey_sections (survey_id, title, description, sort_order) VALUES (?, ?, ?, ?)");
                $stmt->execute([$surveyId, $section['title'], $section['description'] ?? '', $sIndex]);
                $sectionId = $this->db->lastInsertId();

                foreach ($section['fields'] as $fIndex => $field) {
                    $stmt = $this->db->prepare("INSERT INTO dynamic_survey_fields (
                        survey_id, section_id, label, placeholder, default_value, help_text, 
                        field_type, is_required, options, field_config, validation_rules, 
                        conditional_logic, sort_order
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    $stmt->execute([
                        $surveyId,
                        $sectionId,
                        $field['label'],
                        $field['placeholder'] ?? null,
                        $field['default_value'] ?? null,
                        $field['help_text'] ?? null,
                        $field['field_type'],
                        ($field['is_required'] ?? false) ? 1 : 0,
                        $field['options'] ?? null,
                        json_encode($field['field_config'] ?? []),
                        json_encode($field['validation_rules'] ?? []),
                        json_encode($field['conditional_logic'] ?? []),
                        $fIndex
                    ]);
                }
            }
            
            $this->db->commit();
            return $surveyId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
?>

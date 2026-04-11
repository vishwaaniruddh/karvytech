<?php
require_once __DIR__ . '/BaseModel.php';

class DynamicSurveyResponse extends BaseModel {
    protected $table = 'dynamic_survey_responses';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function submitResponse($surveyId, $data, $files = []) {
        try {
            $this->db->beginTransaction();
            
            $responseId = $this->create([
                'survey_id' => $surveyId,
                'site_id' => $data['site_id'] ?? null,
                'respondent_id' => $data['respondent_id'] ?? null,
                'submission_date' => date('Y-m-d H:i:s')
            ]);
            
            foreach ($data['values'] as $fieldId => $value) {
                $this->db->prepare("INSERT INTO dynamic_survey_response_values (response_id, field_id, field_value) VALUES (?, ?, ?)")
                    ->execute([$responseId, $fieldId, is_array($value) ? json_encode($value) : $value]);
            }
            
            foreach ($files as $fieldId => $filePaths) {
                foreach ((array)$filePaths as $path) {
                    $this->db->prepare("INSERT INTO dynamic_survey_response_values (response_id, field_id, file_path) VALUES (?, ?, ?)")
                        ->execute([$responseId, $fieldId, $path]);
                }
            }
            
            $this->db->commit();
            return $responseId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
?>

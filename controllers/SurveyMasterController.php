<?php
require_once __DIR__ . '/../models/SurveyType.php';
require_once __DIR__ . '/BaseMasterController.php';

class SurveyMasterController extends BaseMasterController {
    public function __construct() {
        parent::__construct();
        $this->model = new SurveyType();
        $this->modelName = 'Survey Type';
        $this->tableName = 'survey_types';
    }
}
?>

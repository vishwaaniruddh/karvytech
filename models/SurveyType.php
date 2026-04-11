<?php
require_once __DIR__ . '/BaseMaster.php';

class SurveyType extends BaseMaster {
    protected $table = 'survey_types';
    
    public function __construct() {
        parent::__construct();
    }
}
?>

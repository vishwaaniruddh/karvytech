<?php
require_once __DIR__ . '/../models/InstallationType.php';
require_once __DIR__ . '/BaseMasterController.php';

class InstallationMasterController extends BaseMasterController {
    public function __construct() {
        parent::__construct();
        $this->model = new InstallationType();
        $this->modelName = 'Installation Type';
        $this->tableName = 'installation_types';
    }
}
?>

<?php
require_once __DIR__ . '/BaseMaster.php';

class InstallationType extends BaseMaster {
    protected $table = 'installation_types';
    
    public function __construct() {
        parent::__construct();
    }
}
?>

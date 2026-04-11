<?php
require_once __DIR__ . '/BaseMaster.php';

class Zone extends BaseMaster {
    protected $table = 'zones';
    
    public function __construct() {
        parent::__construct();
    }
}
?>
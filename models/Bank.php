<?php
require_once __DIR__ . '/BaseMaster.php';

class Bank extends BaseMaster {
    protected $table = 'banks';
    
    public function __construct() {
        parent::__construct();
    }
}
?>
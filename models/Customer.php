<?php
require_once __DIR__ . '/BaseMaster.php';

class Customer extends BaseMaster {
    protected $table = 'customers';
    
    public function __construct() {
        parent::__construct();
    }
}
?>
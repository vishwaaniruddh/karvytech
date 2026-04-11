<?php
require_once __DIR__ . '/BaseMaster.php';

class Country extends BaseMaster {
    protected $table = 'countries';
    
    public function __construct() {
        parent::__construct();
    }
}
?>
<?php
require_once __DIR__ . '/BaseMasterController.php';
require_once __DIR__ . '/../models/Bank.php';

class BanksController extends BaseMasterController {
    
    public function __construct() {
        parent::__construct();
        $this->model = new Bank();
        $this->modelName = 'Bank';
        $this->tableName = 'banks';
    }
    
    protected function checkDependencies($id) {
        // Check if bank is used in sites
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sites WHERE bank = (SELECT name FROM banks WHERE id = ?)");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            return [
                'allowed' => false,
                'message' => "Cannot delete bank. It is associated with {$count} site(s)."
            ];
        }
        
        return ['allowed' => true, 'message' => ''];
    }
}
?>
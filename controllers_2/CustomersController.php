<?php
require_once __DIR__ . '/BaseMasterController.php';
require_once __DIR__ . '/../models/Customer.php';

class CustomersController extends BaseMasterController {
    
    public function __construct() {
        parent::__construct();
        $this->model = new Customer();
        $this->modelName = 'Customer';
        $this->tableName = 'customers';
    }
    
    protected function checkDependencies($id) {
        // Check if customer is used in sites
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sites WHERE customer = (SELECT name FROM customers WHERE id = ?)");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            return [
                'allowed' => false,
                'message' => "Cannot delete customer. It is associated with {$count} site(s)."
            ];
        }
        
        return ['allowed' => true, 'message' => ''];
    }
}
?>
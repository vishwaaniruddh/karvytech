<?php
require_once __DIR__ . '/BaseMasterController.php';
require_once __DIR__ . '/../models/Country.php';

class CountriesController extends BaseMasterController {
    
    public function __construct() {
        parent::__construct();
        $this->model = new Country();
        $this->modelName = 'Country';
        $this->tableName = 'countries';
    }
    
    protected function checkDependencies($id) {
        // Check if bank is used in sites
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sites WHERE country = (SELECT name FROM countries WHERE id = ?)");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            return [
                'allowed' => false,
                'message' => "Cannot delete country. It is associated with {$count} site(s)."
            ];
        }
        
        return ['allowed' => true, 'message' => ''];
    }
}
?>
<?php
require_once __DIR__ . '/BaseModel.php';

class BoqMasterItem extends BaseModel {
    protected $table = 'boq_master_items';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getItemsByMasterId($masterId) {
        $sql = "SELECT bmi.*, bi.item_name, bi.item_code, bi.unit, bi.category 
                FROM {$this->table} bmi
                JOIN boq_items bi ON bmi.boq_item_id = bi.id
                WHERE bmi.boq_master_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$masterId]);
        return $stmt->fetchAll();
    }
    
    public function deleteByMasterId($masterId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE boq_master_id = ?");
        return $stmt->execute([$masterId]);
    }
    
    public function addItems($masterId, $items) {
        if (empty($items)) return true;
        
        $sql = "INSERT INTO {$this->table} (boq_master_id, boq_item_id, quantity, notes) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        foreach ($items as $item) {
            $itemId = is_array($item) ? $item['id'] : $item;
            $quantity = is_array($item) ? ($item['quantity'] ?? 1) : 1;
            $notes = is_array($item) ? ($item['notes'] ?? null) : null;
            $stmt->execute([$masterId, $itemId, $quantity, $notes]);
        }
        return true;
    }
}

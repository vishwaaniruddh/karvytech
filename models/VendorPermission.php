<?php
require_once __DIR__ . '/BaseModel.php';

class VendorPermission extends BaseModel {
    protected $table = 'vendor_permissions';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getUserPermissions($userId) {
        $stmt = $this->db->prepare("SELECT permission_key, permission_value FROM {$this->table} WHERE user_id = ?");
        $stmt->execute([$userId]);
        $permissions = $stmt->fetchAll();
        
        $result = [];
        foreach ($permissions as $permission) {
            $result[$permission['permission_key']] = (bool)$permission['permission_value'];
        }
        
        return $result;
    }
    
    public function getVendorPermissions($vendorId) {
        $stmt = $this->db->prepare("SELECT permission_key, permission_value FROM {$this->table} WHERE vendor_id = ?");
        $stmt->execute([$vendorId]);
        $permissions = $stmt->fetchAll();
        
        $result = [];
        foreach ($permissions as $permission) {
            $result[$permission['permission_key']] = (bool)$permission['permission_value'];
        }
        
        return $result;
    }
    
    public function hasPermission($userId, $permissionKey) {
        $stmt = $this->db->prepare("SELECT permission_value FROM {$this->table} WHERE user_id = ? AND permission_key = ?");
        $stmt->execute([$userId, $permissionKey]);
        $result = $stmt->fetch();
        
        return $result ? (bool)$result['permission_value'] : false;
    }
    
    public function setPermission($vendorId, $permissionKey, $value, $grantedBy, $userId = null) {
        // Use INSERT ... ON DUPLICATE KEY UPDATE
        // NOTE: We do NOT update user_id in the UPDATE clause because the unique key is (user_id, permission_key)
        // If a record exists for this user_id + permission_key, we only update the value, not the user_id
        $sql = "INSERT INTO {$this->table} (vendor_id, user_id, permission_key, permission_value, granted_by) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                permission_value = VALUES(permission_value), 
                granted_by = VALUES(granted_by),
                updated_at = CURRENT_TIMESTAMP";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$vendorId, $userId, $permissionKey, $value ? 1 : 0, $grantedBy]);
    }
    
    public function getAllPermissions() {
        return [
            'view_sites' => 'View Sites',
            'update_progress' => 'Update Progress',
            'view_masters' => 'View Master Data',
            'view_reports' => 'View Reports',
            'view_inventory' => 'View Inventory',
            'view_material_requests' => 'View Material Requests'
        ];
    }
    
    public function getVendorPermissionsWithDetails($vendorId) {
        $sql = "SELECT vp.*, u.username as granted_by_name 
                FROM {$this->table} vp 
                LEFT JOIN users u ON vp.granted_by = u.id 
                WHERE vp.vendor_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$vendorId]);
        return $stmt->fetchAll();
    }
    
    public function getVendorSites($vendorId) {
        $sql = "SELECT s.*, ct.name as city_name, st.name as state_name, co.name as country_name
                FROM sites s
                LEFT JOIN site_delegations sd ON s.id = sd.site_id AND sd.status = 'active'
                LEFT JOIN cities ct ON s.city_id = ct.id
                LEFT JOIN states st ON s.state_id = st.id
                LEFT JOIN countries co ON s.country_id = co.id
                WHERE s.vendor = ? OR sd.vendor_id = ?
                ORDER BY s.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$vendorId, $vendorId]);
        return $stmt->fetchAll();
    }
}
?>
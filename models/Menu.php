<?php
require_once __DIR__ . '/BaseModel.php';

class Menu extends BaseModel {
    protected $table = 'menu_items';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getMenuForUser($userId, $userRole) {
        // Get menu items that the user has explicit access to (no default access)
        $sql = "
            SELECT DISTINCT m.*
            FROM menu_items m
            INNER JOIN user_menu_permissions ump ON m.id = ump.menu_item_id 
            WHERE m.status = 'active' 
            AND ump.user_id = ?
            AND ump.can_access = TRUE
            ORDER BY m.parent_id ASC, m.sort_order ASC, m.title ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $menuItems = $stmt->fetchAll();
        
        return $this->buildMenuTree($menuItems);
    }
    
    public function getMenuForRole($role) {
        // Get menu items for a specific role
        $sql = "
            SELECT DISTINCT m.*
            FROM menu_items m
            INNER JOIN role_menu_permissions rmp ON m.id = rmp.menu_item_id
            WHERE m.status = 'active' 
            AND rmp.role = ? 
            AND rmp.can_access = TRUE
            ORDER BY m.parent_id ASC, m.sort_order ASC, m.title ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$role]);
        $menuItems = $stmt->fetchAll();
        
        return $this->buildMenuTree($menuItems);
    }
    
    private function buildMenuTree($menuItems) {
        $tree = [];
        $lookup = [];
        
        // First pass: create lookup array
        foreach ($menuItems as $item) {
            $lookup[$item['id']] = $item;
            $lookup[$item['id']]['children'] = [];
        }
        
        // Second pass: build tree structure
        foreach ($menuItems as $item) {
            if ($item['parent_id'] === null) {
                // Root level item
                $tree[] = &$lookup[$item['id']];
            } else {
                // Child item
                if (isset($lookup[$item['parent_id']])) {
                    $lookup[$item['parent_id']]['children'][] = &$lookup[$item['id']];
                }
            }
        }
        
        return $tree;
    }
    
    public function getAllMenuItems() {
        $stmt = $this->db->prepare("
            SELECT m.*, 
                   CASE WHEN m.parent_id IS NULL THEN m.title 
                        ELSE CONCAT(p.title, ' > ', m.title) 
                   END as full_path
            FROM menu_items m
            LEFT JOIN menu_items p ON m.parent_id = p.id
            ORDER BY m.parent_id ASC, m.sort_order ASC, m.title ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getUserPermissions($userId) {
        $stmt = $this->db->prepare("
            SELECT ump.*, m.title as menu_title
            FROM user_menu_permissions ump
            INNER JOIN menu_items m ON ump.menu_item_id = m.id
            WHERE ump.user_id = ?
            ORDER BY m.title
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function setUserPermission($userId, $menuItemId, $canAccess) {
        $stmt = $this->db->prepare("
            INSERT INTO user_menu_permissions (user_id, menu_item_id, can_access) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE can_access = VALUES(can_access)
        ");
        return $stmt->execute([$userId, $menuItemId, $canAccess ? 1 : 0]);
    }
    
    public function removeUserPermission($userId, $menuItemId) {
        $stmt = $this->db->prepare("DELETE FROM user_menu_permissions WHERE user_id = ? AND menu_item_id = ?");
        return $stmt->execute([$userId, $menuItemId]);
    }
    
    public function getRolePermissions($role) {
        $stmt = $this->db->prepare("
            SELECT rmp.*, m.title as menu_title
            FROM role_menu_permissions rmp
            INNER JOIN menu_items m ON rmp.menu_item_id = m.id
            WHERE rmp.role = ?
            ORDER BY m.title
        ");
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }
    
    public function setRolePermission($role, $menuItemId, $canAccess) {
        $stmt = $this->db->prepare("
            INSERT INTO role_menu_permissions (role, menu_item_id, can_access) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE can_access = VALUES(can_access)
        ");
        return $stmt->execute([$role, $menuItemId, $canAccess ? 1 : 0]);
    }
    
    public function hasAccess($userId, $userRole, $url) {
        // Check if user has explicit access to a specific URL
        $sql = "
            SELECT COUNT(*) > 0 as has_access
            FROM menu_items m
            INNER JOIN user_menu_permissions ump ON m.id = ump.menu_item_id
            WHERE m.url = ? 
            AND m.status = 'active'
            AND ump.user_id = ?
            AND ump.can_access = TRUE
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$url, $userId]);
        $result = $stmt->fetch();
        
        return $result['has_access'] == 1;
    }
    
    public function createMenuItem($data) {
        $stmt = $this->db->prepare("
            INSERT INTO menu_items (parent_id, title, icon, url, sort_order, status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['parent_id'],
            $data['title'],
            $data['icon'],
            $data['url'],
            $data['sort_order'] ?? 0,
            $data['status'] ?? 'active'
        ]);
    }
    
    public function updateMenuItem($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE menu_items 
            SET parent_id = ?, title = ?, icon = ?, url = ?, sort_order = ?, status = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['parent_id'],
            $data['title'],
            $data['icon'],
            $data['url'],
            $data['sort_order'] ?? 0,
            $data['status'] ?? 'active',
            $id
        ]);
    }
}
?>
<?php
require_once __DIR__ . '/../config/database.php';

class Warehouse {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Get all warehouses with optional filters
     */
    public function getAll($search = '', $status = '') {
        $sql = "SELECT * FROM warehouses WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (name LIKE ? OR warehouse_code LIKE ? OR city LIKE ? OR state LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if (!empty($status)) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY is_default DESC, name ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get warehouse by ID
     */
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM warehouses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get warehouse by code
     */
    public function getByCode($code) {
        $stmt = $this->conn->prepare("SELECT * FROM warehouses WHERE warehouse_code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get default warehouse
     */
    public function getDefault() {
        $stmt = $this->conn->prepare("SELECT * FROM warehouses WHERE is_default = TRUE AND status = 'active' LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new warehouse
     */
    public function create($data) {
        try {
            $this->conn->beginTransaction();
            
            // If this warehouse is set as default, unset other defaults
            if (!empty($data['is_default'])) {
                $this->conn->exec("UPDATE warehouses SET is_default = FALSE");
            }
            
            $sql = "INSERT INTO warehouses (
                warehouse_code, name, address, city, state, pincode,
                contact_person, contact_phone, contact_email,
                is_default, status, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                $data['warehouse_code'],
                $data['name'],
                $data['address'],
                $data['city'],
                $data['state'],
                $data['pincode'],
                $data['contact_person'],
                $data['contact_phone'],
                $data['contact_email'] ?? null,
                !empty($data['is_default']) ? 1 : 0,
                $data['status'] ?? 'active',
                $_SESSION['user_id'] ?? 1
            ]);
            
            $warehouseId = $this->conn->lastInsertId();
            
            // Log the action
            $this->logAction('create', $warehouseId, null, $data);
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'message' => 'Warehouse created successfully',
                'id' => $warehouseId
            ];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create warehouse: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update warehouse
     */
    public function update($id, $data) {
        try {
            $this->conn->beginTransaction();
            
            // Get old data for audit log
            $oldData = $this->getById($id);
            
            // If this warehouse is set as default, unset other defaults
            if (!empty($data['is_default'])) {
                $this->conn->exec("UPDATE warehouses SET is_default = FALSE WHERE id != {$id}");
            }
            
            $sql = "UPDATE warehouses SET
                warehouse_code = ?,
                name = ?,
                address = ?,
                city = ?,
                state = ?,
                pincode = ?,
                contact_person = ?,
                contact_phone = ?,
                contact_email = ?,
                is_default = ?,
                status = ?,
                updated_by = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                $data['warehouse_code'],
                $data['name'],
                $data['address'],
                $data['city'],
                $data['state'],
                $data['pincode'],
                $data['contact_person'],
                $data['contact_phone'],
                $data['contact_email'] ?? null,
                !empty($data['is_default']) ? 1 : 0,
                $data['status'] ?? 'active',
                $_SESSION['user_id'] ?? 1,
                $id
            ]);
            
            // Log the action
            $this->logAction('update', $id, $oldData, $data);
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'message' => 'Warehouse updated successfully'
            ];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to update warehouse: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete warehouse
     */
    public function delete($id) {
        try {
            // Check if warehouse is default
            $warehouse = $this->getById($id);
            if ($warehouse['is_default']) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete default warehouse'
                ];
            }
            
            // Check if warehouse has stock
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM inventory_stock WHERE warehouse_id = ?");
            $stmt->execute([$id]);
            $stockCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($stockCount > 0) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete warehouse with existing stock. Please transfer stock first.'
                ];
            }
            
            $this->conn->beginTransaction();
            
            // Log the action
            $this->logAction('delete', $id, $warehouse, null);
            
            $stmt = $this->conn->prepare("DELETE FROM warehouses WHERE id = ?");
            $stmt->execute([$id]);
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'message' => 'Warehouse deleted successfully'
            ];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to delete warehouse: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get warehouse stock summary
     */
    public function getStockSummary($warehouseId) {
        $sql = "SELECT * FROM warehouse_stock_summary WHERE warehouse_id = ? ORDER BY item_name";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$warehouseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get warehouse statistics
     */
    public function getStats($warehouseId) {
        $sql = "SELECT 
            COUNT(DISTINCT boq_item_id) as total_items,
            SUM(CASE WHEN item_status = 'available' THEN 1 ELSE 0 END) as available_stock,
            SUM(CASE WHEN item_status = 'dispatched' THEN 1 ELSE 0 END) as dispatched_stock,
            SUM(total_value) as total_value,
            SUM(available_value) as available_value
        FROM warehouse_stock_summary
        WHERE warehouse_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$warehouseId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Log warehouse action to audit log
     */
    private function logAction($action, $warehouseId, $oldValues, $newValues) {
        $sql = "INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent)
                VALUES (?, ?, 'warehouses', ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $_SESSION['user_id'] ?? 1,
            'warehouse_' . $action,
            $warehouseId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
}

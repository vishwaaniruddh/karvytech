<?php
require_once __DIR__ . '/BaseModel.php';

class DeletedDataBackup extends BaseModel {
    protected $table = 'deleted_data_backups';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Create a backup of data before deletion
     */
    public function createBackup($requestId, $tableName, $recordId, $data, $deletedBy, $notes = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} 
                (request_id, table_name, record_id, data, deleted_by, notes, status)
                VALUES (?, ?, ?, ?, ?, ?, 'deleted')
            ");
            
            $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
            
            $stmt->execute([
                $requestId,
                $tableName,
                $recordId,
                $jsonData,
                $deletedBy,
                $notes
            ]);
            
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            error_log("Failed to create backup: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get all backups for a specific request
     */
    public function getBackupsByRequest($requestId) {
        $stmt = $this->db->prepare("
            SELECT 
                ddb.*,
                u1.username as deleted_by_name,
                u2.username as restored_by_name
            FROM {$this->table} ddb
            LEFT JOIN users u1 ON ddb.deleted_by = u1.id
            LEFT JOIN users u2 ON ddb.restored_by = u2.id
            WHERE ddb.request_id = ?
            ORDER BY ddb.table_name, ddb.record_id
        ");
        
        $stmt->execute([$requestId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get backup by ID
     */
    public function getBackup($id) {
        $stmt = $this->db->prepare("
            SELECT 
                ddb.*,
                u1.username as deleted_by_name,
                u2.username as restored_by_name,
                sr.request_title,
                sr.request_type
            FROM {$this->table} ddb
            LEFT JOIN users u1 ON ddb.deleted_by = u1.id
            LEFT JOIN users u2 ON ddb.restored_by = u2.id
            LEFT JOIN superadmin_requests sr ON ddb.request_id = sr.id
            WHERE ddb.id = ?
        ");
        
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Restore a backed up record
     */
    public function restoreBackup($backupId, $restoredBy) {
        try {
            $this->db->beginTransaction();
            
            // Get backup data
            $backup = $this->getBackup($backupId);
            if (!$backup) {
                throw new Exception("Backup not found");
            }
            
            if ($backup['status'] === 'restored') {
                throw new Exception("This backup has already been restored");
            }
            
            // Decode JSON data
            $data = json_decode($backup['data'], true);
            if (!$data) {
                throw new Exception("Invalid backup data");
            }
            
            // Restore the record to its original table
            $tableName = $backup['table_name'];
            $columns = array_keys($data);
            $placeholders = array_fill(0, count($columns), '?');
            
            $sql = "INSERT INTO {$tableName} (" . implode(', ', $columns) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($data));
            
            $restoredId = $this->db->lastInsertId();
            
            // Mark backup as restored
            $stmt = $this->db->prepare("
                UPDATE {$this->table}
                SET status = 'restored',
                    restored_by = ?,
                    restored_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$restoredBy, $backupId]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'restored_id' => $restoredId,
                'table' => $tableName
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Failed to restore backup: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Restore all backups for a request
     */
    public function restoreAllByRequest($requestId, $restoredBy) {
        try {
            $backups = $this->getBackupsByRequest($requestId);
            $restored = [];
            $errors = [];
            
            foreach ($backups as $backup) {
                if ($backup['status'] === 'deleted') {
                    try {
                        $result = $this->restoreBackup($backup['id'], $restoredBy);
                        $restored[] = $result;
                    } catch (Exception $e) {
                        $errors[] = [
                            'backup_id' => $backup['id'],
                            'table' => $backup['table_name'],
                            'error' => $e->getMessage()
                        ];
                    }
                }
            }
            
            return [
                'success' => empty($errors),
                'restored' => $restored,
                'errors' => $errors
            ];
            
        } catch (Exception $e) {
            error_log("Failed to restore request backups: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get statistics
     */
    public function getStats() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_backups,
                SUM(CASE WHEN status = 'deleted' THEN 1 ELSE 0 END) as can_restore,
                SUM(CASE WHEN status = 'restored' THEN 1 ELSE 0 END) as already_restored,
                COUNT(DISTINCT request_id) as total_requests,
                COUNT(DISTINCT table_name) as tables_affected
            FROM {$this->table}
        ");
        
        return $stmt->fetch();
    }
    
    /**
     * Get all restorable backups (not yet restored)
     */
    public function getRestorableBackups($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        // Get total count
        $stmt = $this->db->query("
            SELECT COUNT(DISTINCT request_id) 
            FROM {$this->table} 
            WHERE status = 'deleted'
        ");
        $total = $stmt->fetchColumn();
        
        // Get paginated results grouped by request
        $stmt = $this->db->prepare("
            SELECT 
                ddb.request_id,
                sr.request_title,
                sr.request_type,
                sr.requested_by_name,
                COUNT(ddb.id) as backup_count,
                MIN(ddb.deleted_at) as deleted_at,
                GROUP_CONCAT(DISTINCT ddb.table_name) as tables
            FROM {$this->table} ddb
            JOIN superadmin_requests sr ON ddb.request_id = sr.id
            WHERE ddb.status = 'deleted'
            GROUP BY ddb.request_id
            ORDER BY ddb.deleted_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$limit, $offset]);
        $records = $stmt->fetchAll();
        
        return [
            'records' => $records,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
}
?>

<?php
require_once __DIR__ . '/../config/database.php';

class AuditLog {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Log an audit entry
     */
    public function log($data) {
        $sql = "INSERT INTO user_audit_logs (
            user_id, username, user_role, action_type, endpoint, 
            http_method, status_code, ip_address, user_agent, 
            request_data, response_data, error_message, execution_time, session_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['user_id'] ?? null,
            $data['username'] ?? null,
            $data['user_role'] ?? null,
            $data['action_type'],
            $data['endpoint'],
            $data['http_method'] ?? $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $data['status_code'] ?? 200,
            $data['ip_address'] ?? $this->getClientIP(),
            $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null,
            $data['request_data'] ?? null,
            $data['response_data'] ?? null,
            $data['error_message'] ?? null,
            $data['execution_time'] ?? null,
            $data['session_id'] ?? session_id()
        ]);
    }
    
    /**
     * Log user login
     */
    public function logLogin($userId, $username, $userRole) {
        $sessionId = session_id();
        
        // Log in user_audit_logs
        $this->log([
            'user_id' => $userId,
            'username' => $username,
            'user_role' => $userRole,
            'action_type' => 'login',
            'endpoint' => $_SERVER['REQUEST_URI'] ?? '/login',
            'status_code' => 200
        ]);
        
        // Create session record
        $sql = "INSERT INTO user_audit_sessions (user_id, username, session_id, ip_address, user_agent, last_activity)
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $userId,
            $username,
            $sessionId,
            $this->getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
    
    /**
     * Log user logout
     */
    public function logLogout($userId, $username) {
        $sessionId = session_id();
        
        // Log in user_audit_logs
        $this->log([
            'user_id' => $userId,
            'username' => $username,
            'action_type' => 'logout',
            'endpoint' => $_SERVER['REQUEST_URI'] ?? '/logout',
            'status_code' => 200
        ]);
        
        // Update session record
        $sql = "UPDATE user_audit_sessions SET logout_time = NOW(), is_active = 0 
                WHERE session_id = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sessionId]);
    }
    
    /**
     * Update session activity
     */
    public function updateSessionActivity($sessionId = null) {
        $sessionId = $sessionId ?? session_id();
        $sql = "UPDATE user_audit_sessions SET last_activity = NOW() WHERE session_id = ? AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sessionId]);
    }
    
    /**
     * Get all audit logs with filters and pagination
     */
    public function getAllLogsWithPagination($page = 1, $limit = 10, $filters = []) {
        $offset = ($page - 1) * $limit;
        
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $whereClause .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['username'])) {
            $whereClause .= " AND al.username LIKE ?";
            $params[] = '%' . $filters['username'] . '%';
        }
        
        if (!empty($filters['action_type'])) {
            $whereClause .= " AND al.action_type = ?";
            $params[] = $filters['action_type'];
        }
        
        if (!empty($filters['status_code'])) {
            $whereClause .= " AND al.status_code = ?";
            $params[] = $filters['status_code'];
        }
        
        if (!empty($filters['endpoint'])) {
            $whereClause .= " AND al.endpoint LIKE ?";
            $params[] = '%' . $filters['endpoint'] . '%';
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND DATE(al.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND DATE(al.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM user_audit_logs al $whereClause";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        $sql = "SELECT al.*, u.email as user_email 
                FROM user_audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                $whereClause
                ORDER BY al.created_at DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        
        // Bind all parameters
        $i = 1;
        foreach ($params as $val) {
            $stmt->bindValue($i++, $val);
        }
        
        // Bind pagination parameters with explicit type
        $stmt->bindValue($i++, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue($i++, (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'records' => $records,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }

    /**
     * Get all audit logs with filters
     */
    public function getAllLogs($filters = []) {
        $sql = "SELECT al.*, u.email as user_email 
                FROM user_audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['username'])) {
            $sql .= " AND al.username LIKE ?";
            $params[] = '%' . $filters['username'] . '%';
        }
        
        if (!empty($filters['action_type'])) {
            $sql .= " AND al.action_type = ?";
            $params[] = $filters['action_type'];
        }
        
        if (!empty($filters['status_code'])) {
            $sql .= " AND al.status_code = ?";
            $params[] = $filters['status_code'];
        }
        
        if (!empty($filters['endpoint'])) {
            $sql .= " AND al.endpoint LIKE ?";
            $params[] = '%' . $filters['endpoint'] . '%';
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(al.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(al.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY al.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get audit statistics
     */
    public function getStatistics($dateFrom = null, $dateTo = null) {
        $params = [];
        $whereClause = "WHERE 1=1";
        
        if ($dateFrom) {
            $whereClause .= " AND DATE(created_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $whereClause .= " AND DATE(created_at) <= ?";
            $params[] = $dateTo;
        }
        
        $stats = [];
        
        // Total logs
        $sql = "SELECT COUNT(*) as total FROM user_audit_logs $whereClause";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stats['total_logs'] = $stmt->fetchColumn();
        
        // Unique users
        $sql = "SELECT COUNT(DISTINCT user_id) as total FROM user_audit_logs $whereClause AND user_id IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stats['unique_users'] = $stmt->fetchColumn();
        
        // By action type
        $sql = "SELECT action_type, COUNT(*) as count FROM user_audit_logs $whereClause GROUP BY action_type";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stats['by_action_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // By status code
        $sql = "SELECT status_code, COUNT(*) as count FROM user_audit_logs $whereClause GROUP BY status_code";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stats['by_status_code'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Top endpoints
        $sql = "SELECT endpoint, COUNT(*) as count FROM user_audit_logs $whereClause GROUP BY endpoint ORDER BY count DESC LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stats['top_endpoints'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Top users
        $sql = "SELECT username, COUNT(*) as count FROM user_audit_logs $whereClause AND username IS NOT NULL GROUP BY username ORDER BY count DESC LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stats['top_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
    
    /**
     * Get active sessions
     */
    public function getActiveSessions() {
        $sql = "SELECT * FROM user_audit_sessions WHERE is_active = 1 ORDER BY last_activity DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user activity history
     */
    public function getUserActivity($userId, $limit = 50) {
        $sql = "SELECT * FROM user_audit_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get API statistics
     */
    public function getAPIStatistics() {
        $sql = "SELECT * FROM user_audit_api_stats ORDER BY total_calls DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update API statistics
     */
    public function updateAPIStats($endpoint, $method, $statusCode, $executionTime) {
        $isSuccess = $statusCode >= 200 && $statusCode < 300;
        
        $sql = "INSERT INTO user_audit_api_stats (endpoint, http_method, total_calls, success_calls, error_calls, avg_execution_time, last_called)
                VALUES (?, ?, 1, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    total_calls = total_calls + 1,
                    success_calls = success_calls + ?,
                    error_calls = error_calls + ?,
                    avg_execution_time = (avg_execution_time * total_calls + ?) / (total_calls + 1),
                    last_called = NOW()";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $endpoint,
            $method,
            $isSuccess ? 1 : 0,
            $isSuccess ? 0 : 1,
            $executionTime,
            $isSuccess ? 1 : 0,
            $isSuccess ? 0 : 1,
            $executionTime
        ]);
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
    
    /**
     * Clean old logs (older than specified days)
     */
    public function cleanOldLogs($days = 90) {
        $sql = "DELETE FROM user_audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$days]);
    }
}
?>

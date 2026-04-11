<?php
// Authentication configuration
session_start();

// Define auth-specific constants first (before including constants.php)
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
}
if (!defined('ADMIN_ROLE')) {
    define('ADMIN_ROLE', 'admin');
}
if (!defined('VENDOR_ROLE')) {
    define('VENDOR_ROLE', 'contractor'); // Changed from 'vendor' to 'contractor' to match database
}
if (!defined('CONTRACTOR_ROLE')) {
    define('CONTRACTOR_ROLE', 'contractor');
}

// Include constants for BASE_URL (used by other parts of the application)
require_once __DIR__ . '/constants.php';

class Auth {
    public static function login($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['role_id'] = $user['role_id'] ?? null;
        $_SESSION['vendor_id'] = $user['vendor_id'] ?? null;
        $_SESSION['login_time'] = time();
        
        // Cache user permissions
        self::cacheUserPermissions($user['id']);
    }
    
    public static function logout() {
        session_destroy();
        // Use relative path to avoid BASE_URL dependency
        $loginPath = self::getLoginPath();
        header('Location: ' . $loginPath);
        exit();
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && self::isSessionValid();
    }
    
    public static function isSessionValid() {
        if (!isset($_SESSION['login_time'])) {
            return false;
        }
        
        if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
            self::clearSession();
            return false;
        }
        
        return true;
    }
    
    public static function requireAuth() {
        if (!self::isLoggedIn()) {
            // If it's an AJAX request, return JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Session expired', 'redirect' => true]);
                exit();
            }
            
            // For regular requests, redirect to login
            $loginPath = self::getLoginPath();
            header('Location: ' . $loginPath);
            exit();
        }
    }
    
    public static function requireRole($role) {
        self::requireAuth();
        
        // Superadmin can access everything
        if (self::isSuperAdmin()) {
            return;
        }
        
        if ($_SESSION['role'] !== $role) {
            // If it's an AJAX request, return JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access denied', 'redirect' => false]);
                exit();
            }
            
            header('HTTP/1.0 403 Forbidden');
            exit('Access denied - Required role: ' . $role . ' (Your role: ' . ($_SESSION['role'] ?? 'none') . ')');
        }
    }
    
    public static function getCurrentUser() {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role'],
                'vendor_id' => $_SESSION['vendor_id'] ?? null
            ];
        }
        return null;
    }
    
    public static function updateSession($user) {
        if (self::isLoggedIn()) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['vendor_id'] = $user['vendor_id'] ?? null;
        }
    }
    
    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    public static function getVendorId() {
        return $_SESSION['vendor_id'] ?? null;
    }
    
    public static function isVendor() {
        return isset($_SESSION['role']) && ($_SESSION['role'] === VENDOR_ROLE || $_SESSION['role'] === 'contractor');
    }
    
    public static function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === ADMIN_ROLE;
    }
    
    public static function isAdminOrSuperadmin() {
        return self::isAdmin() || self::isSuperAdmin();
    }
    
    public static function requireVendor() {
        self::requireAuth();
        
        // Superadmin can access everything
        if (self::isSuperAdmin()) {
            return;
        }
        
        if (!self::isVendor()) {
            // If it's an AJAX request, return JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Vendor access required', 'redirect' => false]);
                exit();
            }
            
            header('HTTP/1.0 403 Forbidden');
            exit('Access denied - Vendor access required');
        }
    }
    
    public static function requireAdminOrVendor() {
        self::requireAuth();
        
        // Superadmin can access everything
        if (self::isSuperAdmin()) {
            return;
        }
        
        if (!self::isAdmin() && !self::isVendor()) {
            header('HTTP/1.0 403 Forbidden');
            exit('Access denied');
        }
    }
    
    public static function requireVendorPermission($permission) {
        self::requireVendor();
        
        // Superadmin bypasses permission checks
        if (self::isSuperAdmin()) {
            return;
        }
        
        require_once __DIR__ . '/../models/VendorPermission.php';
        $permissionModel = new VendorPermission();
        
        if (!$permissionModel->hasPermission(self::getVendorId(), $permission)) {
            // If it's an AJAX request, return JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Permission required: ' . $permission, 'redirect' => false]);
                exit();
            }
            
            header('HTTP/1.0 403 Forbidden');
            exit('Access denied - Permission required: ' . $permission);
        }
    }
    
    public static function hasVendorPermission($permission) {
        if (!self::isVendor()) {
            return false;
        }
        
        require_once __DIR__ . '/../models/VendorPermission.php';
        $permissionModel = new VendorPermission();
        
        return $permissionModel->hasPermission(self::getVendorId(), $permission);
    }
    
    private static function getLoginPath() {
        // Determine the correct path to login based on current location
        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
        
        if (strpos($currentPath, '/admin/') !== false) {
            return '../../auth/login.php';
        } elseif (strpos($currentPath, '/vendor/') !== false) {
            return '../auth/login.php';
        } else {
            return '../auth/login.php';
        }
    }
    
    private static function clearSession() {
        // Clear session without redirect to avoid BASE_URL issues
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
    
    /**
     * Cache user permissions in session
     */
    private static function cacheUserPermissions($userId) {
        try {
            require_once __DIR__ . '/../models/User.php';
            $userModel = new User();
            $permissions = $userModel->getUserPermissions($userId);
            $_SESSION['permissions'] = $permissions;
        } catch (Exception $e) {
            // If caching fails, permissions will be checked on demand
            $_SESSION['permissions'] = [];
        }
    }
    
    /**
     * Check if user has permission
     */
    public static function hasPermission($moduleName, $permissionName = 'view') {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        // Superadmin has all permissions
        if (self::isSuperAdmin()) {
            return true;
        }
        
        // Check cached permissions
        if (isset($_SESSION['permissions'])) {
            foreach ($_SESSION['permissions'] as $perm) {
                if ($perm['module_name'] === $moduleName && $perm['name'] === $permissionName) {
                    return true;
                }
            }
        }
        
        // If not in cache, check database
        try {
            require_once __DIR__ . '/../models/User.php';
            $userModel = new User();
            return $userModel->hasPermission(self::getUserId(), $moduleName, $permissionName);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if user has any permission in a module
     */
    public static function hasModuleAccess($moduleName) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        // Superadmin has access to all modules
        if (self::isSuperAdmin()) {
            return true;
        }
        
        // Check cached permissions
        if (isset($_SESSION['permissions'])) {
            foreach ($_SESSION['permissions'] as $perm) {
                if ($perm['module_name'] === $moduleName) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get user role
     */
    public static function getRole() {
        return $_SESSION['role'] ?? null;
    }
    
    /**
     * Get user role ID
     */
    public static function getRoleId() {
        return $_SESSION['role_id'] ?? null;
    }
    
    /**
     * Check if user is superadmin
     */
    public static function isSuperAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin';
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdminOrAbove() {
        return isset($_SESSION['role']) && in_array($_SESSION['role'], ['superadmin', 'admin']);
    }
    
    /**
     * Check if user is manager or above
     */
    public static function isManagerOrAbove() {
        return isset($_SESSION['role']) && in_array($_SESSION['role'], ['superadmin', 'admin', 'manager']);
    }
    
    /**
     * Require specific permission
     */
    public static function requirePermission($moduleName, $permissionName = 'view') {
        self::requireAuth();
        
        if (!self::hasPermission($moduleName, $permissionName)) {
            // If it's an AJAX request, return JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Permission denied', 'redirect' => false]);
                exit();
            }
            
            header('HTTP/1.0 403 Forbidden');
            exit('Access denied - Permission required: ' . $moduleName . '.' . $permissionName);
        }
    }
    
    /**
     * Require module access
     */
    public static function requireModuleAccess($moduleName) {
        self::requireAuth();
        
        if (!self::hasModuleAccess($moduleName)) {
            // If it's an AJAX request, return JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Module access denied', 'redirect' => false]);
                exit();
            }
            
            header('HTTP/1.0 403 Forbidden');
            exit('Access denied - Module access required: ' . $moduleName);
        }
    }
}
?>
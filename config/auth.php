<?php
// Authentication configuration
session_start();

// Define auth-specific constants first (before including constants.php)
if (!defined('SESSION_TIMEOUT')) {
    define('SESSION_TIMEOUT', 7200); // 2 hours in seconds (increased from 1 hour)
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
        $_SESSION['role_category'] = $user['role_category'] ?? 'internal';
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
        
        $timeElapsed = time() - $_SESSION['login_time'];
        
        if ($timeElapsed > SESSION_TIMEOUT) {
            self::clearSession();
            return false;
        }
        
        // Auto-extend session if user has been active (less than half timeout)
        if ($timeElapsed > SESSION_TIMEOUT / 2) {
            $_SESSION['login_time'] = time();
        }
        
        return true;
    }
    
    public static function requireAuth() {
        if (!self::isLoggedIn()) {
            // Check for AJAX/JSON request
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
                      (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Session expired', 'redirect' => true]);
                exit();
            }
            
            // For regular requests, redirect to login
            $loginPath = self::getLoginPath();
            header('Location: ' . $loginPath);
            exit();
        }

        // Session Self-Healing: If session exists but core metadata is missing (pre-migration session)
        if (isset($_SESSION['user_id'])) {
            $needsRefresh = false;
            
            // 1. Refresh role_category if missing
            if (!isset($_SESSION['role_category'])) {
                try {
                    require_once __DIR__ . '/../models/User.php';
                    $userModel = new User();
                    $user = $userModel->findByEmailOrPhone($_SESSION['username'] ?? '');
                    if ($user && isset($user['role_category'])) {
                        $_SESSION['role_category'] = $user['role_category'];
                        $needsRefresh = true;
                    } else {
                        $_SESSION['role_category'] = ($_SESSION['role'] === 'contractor') ? 'external' : 'internal';
                    }
                } catch (Exception $e) {}
            }
            
            // 2. Refresh permissions cache if missing
            if ($needsRefresh || !isset($_SESSION['permissions'])) {
                self::cacheUserPermissions($_SESSION['user_id']);
            }
        }
    }
    
    public static function requireRole($role) {
        self::requireAuth();
        
        // Superadmin can access everything
        if (self::isSuperAdmin()) {
            return;
        }
        
        $roleMatch = false;
        if ($role === ADMIN_ROLE) {
            // If admin role is required, allow any internal role
            $roleMatch = self::isInternal();
        } else {
            // Otherwise, match the exact role name
            $roleMatch = ($_SESSION['role'] === $role);
        }
        
        if (!$roleMatch) {
            // Robust AJAX detection for modern fetch()
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
                      (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Access denied', 'redirect' => false]);
                exit();
            }
            
            header('Location: ' . url('/shared/403.php?role=' . $role));
            exit();
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
        return self::isExternal();
    }
    
    public static function isAdmin() {
        return self::isInternal();
    }
    
    public static function isInternal() {
        if (isset($_SESSION['role_category'])) {
            return $_SESSION['role_category'] === 'internal';
        }
        // Legacy fallback: anyone who isn't a contractor is internal
        return isset($_SESSION['role']) && !in_array($_SESSION['role'], ['contractor', 'vendor']);
    }
    
    public static function isExternal() {
        if (isset($_SESSION['role_category'])) {
            return $_SESSION['role_category'] === 'external';
        }
        // Legacy fallback
        return isset($_SESSION['role']) && in_array($_SESSION['role'], ['contractor', 'vendor']);
    }
    
    public static function isAdminOrSuperadmin() {
        return self::isInternal() || self::isSuperAdmin();
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
            
            header('Location: ' . url('/shared/403.php?role=' . VENDOR_ROLE));
            exit();
        }
    }
    
    public static function requireAdminOrVendor() {
        self::requireAuth();
        
        // Superadmin can access everything
        if (self::isSuperAdmin()) {
            return;
        }
        
        if (!self::isAdmin() && !self::isVendor()) {
            header('Location: ' . url('/shared/403.php'));
            exit();
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
            
            header('Location: ' . url('/shared/403.php?permission=' . $permission));
            exit();
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
        
        // Superadmin bypasses all permission checks
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
        
        // Check cached permissions
        if (isset($_SESSION['permissions']) && !empty($_SESSION['permissions'])) {
            foreach ($_SESSION['permissions'] as $perm) {
                if ($perm['module_name'] === $moduleName) {
                    return true;
                }
            }
            // If cache exists but doesn't have the module, we can trust it (deny by default)
            return false;
        }
        
        // If cache is missing, check database directly
        try {
            require_once __DIR__ . '/../models/User.php';
            $userModel = new User();
            return $userModel->hasModuleAccess(self::getUserId(), $moduleName);
        } catch (Exception $e) {
            return false;
        }
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
            // If it's an AJAX request (Check X-Requested-With OR Accept header)
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
                      (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Permission denied: ' . $moduleName . '.' . $permissionName, 
                    'redirect' => false
                ]);
                exit();
            }
            
            header('Location: ' . url('/shared/403.php?permission=' . $moduleName . '.' . $permissionName));
            exit();
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
            
            header('Location: ' . url('/shared/403.php?module=' . $moduleName));
            exit();
        }
    }
}
?>
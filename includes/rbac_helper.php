<?php
/**
 * RBAC Helper Functions
 * Provides convenient functions for role and permission checks
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Role.php';
require_once __DIR__ . '/../models/Permission.php';

/**
 * Check if current user has permission
 */
function can($moduleName, $permissionName = 'view') {
    return Auth::hasPermission($moduleName, $permissionName);
}

/**
 * Check if current user has module access
 */
function canAccess($moduleName) {
    return Auth::hasModuleAccess($moduleName);
}

/**
 * Check if current user is superadmin
 */
function isSuperAdmin() {
    return Auth::isSuperAdmin();
}

/**
 * Check if current user is admin or above
 */
function isAdmin() {
    return Auth::isAdminOrAbove();
}

/**
 * Check if current user is manager or above
 */
function isManager() {
    return Auth::isManagerOrAbove();
}

/**
 * Get current user role
 */
function getUserRole() {
    return Auth::getRole();
}

/**
 * Get all roles
 */
function getAllRoles() {
    $roleModel = new Role();
    return $roleModel->getAllRoles();
}

/**
 * Get all modules
 */
function getAllModules() {
    $permissionModel = new Permission();
    return $permissionModel->getAllModules();
}

/**
 * Get all permissions grouped by module
 */
function getAllPermissionsGrouped() {
    $permissionModel = new Permission();
    return $permissionModel->getAllPermissionsGrouped();
}

/**
 * Get role permissions
 */
function getRolePermissions($roleId) {
    $roleModel = new Role();
    return $roleModel->getRolePermissions($roleId);
}

/**
 * Get role permissions grouped by module
 */
function getRolePermissionsByModule($roleId) {
    $roleModel = new Role();
    return $roleModel->getRolePermissionsByModule($roleId);
}

/**
 * Get user permissions
 */
function getUserPermissions($userId) {
    require_once __DIR__ . '/../models/User.php';
    $userModel = new User();
    return $userModel->getUserPermissions($userId);
}

/**
 * Check if user has permission
 */
function userHasPermission($userId, $moduleName, $permissionName = 'view') {
    require_once __DIR__ . '/../models/User.php';
    $userModel = new User();
    return $userModel->hasPermission($userId, $moduleName, $permissionName);
}

/**
 * Get role by name
 */
function getRoleByName($name) {
    $roleModel = new Role();
    return $roleModel->getRoleByName($name);
}

/**
 * Get module by name
 */
function getModuleByName($name) {
    $permissionModel = new Permission();
    return $permissionModel->getModuleByName($name);
}

/**
 * Display permission badge
 */
function permissionBadge($moduleName, $permissionName = 'view') {
    if (can($moduleName, $permissionName)) {
        return '<span class="badge badge-success">✓</span>';
    }
    return '<span class="badge badge-danger">✗</span>';
}

/**
 * Display role badge
 */
function roleBadge($role) {
    $colors = [
        'superadmin' => 'danger',
        'admin' => 'warning',
        'manager' => 'info',
        'contractor' => 'secondary'
    ];
    
    $color = $colors[$role] ?? 'secondary';
    $displayName = ucfirst(str_replace('_', ' ', $role));
    
    return '<span class="badge badge-' . $color . '">' . $displayName . '</span>';
}

/**
 * Require permission or die
 */
function requirePermission($moduleName, $permissionName = 'view') {
    Auth::requirePermission($moduleName, $permissionName);
}

/**
 * Require module access or die
 */
function requireModuleAccess($moduleName) {
    Auth::requireModuleAccess($moduleName);
}
?>

<?php
require_once '../config/auth.php';
require_once '../includes/logger.php';
require_once '../includes/audit_integration.php';

// Log the logout action
if (Auth::isLoggedIn()) {
    $user = Auth::getCurrentUser();
    Logger::logUserLogout($user['username']);
    
    // Audit logging
    auditLogLogout($user['id'], $user['username']);
}

// Perform logout
Auth::logout();
?>
<?php
/**
 * Audit Integration Helper
 * Include this file to enable automatic audit logging
 */

// Only load if not already loaded
if (!class_exists('AuditLog')) {
    require_once __DIR__ . '/../models/AuditLog.php';
}

if (!class_exists('AuditMiddleware')) {
    require_once __DIR__ . '/../middleware/AuditMiddleware.php';
}

/**
 * Helper function to log user login
 */
function auditLogLogin($userId, $username, $userRole) {
    try {
        $auditLog = new AuditLog();
        $auditLog->logLogin($userId, $username, $userRole);
    } catch (Exception $e) {
        // Silently fail - don't break login process
        error_log('Audit log error: ' . $e->getMessage());
    }
}

/**
 * Helper function to log user logout
 */
function auditLogLogout($userId, $username) {
    try {
        $auditLog = new AuditLog();
        $auditLog->logLogout($userId, $username);
    } catch (Exception $e) {
        // Silently fail - don't break logout process
        error_log('Audit log error: ' . $e->getMessage());
    }
}

/**
 * Helper function to log API call
 */
function auditLogAPI($endpoint, $statusCode = 200, $requestData = null, $responseData = null, $errorMessage = null) {
    try {
        AuditMiddleware::logAPICall($endpoint, $statusCode, $requestData, $responseData, $errorMessage);
    } catch (Exception $e) {
        // Silently fail
        error_log('Audit log error: ' . $e->getMessage());
    }
}

/**
 * Helper function to log custom action
 */
function auditLogAction($actionType, $endpoint, $statusCode = 200, $additionalData = []) {
    try {
        AuditMiddleware::logAction($actionType, $endpoint, $statusCode, $additionalData);
    } catch (Exception $e) {
        // Silently fail
        error_log('Audit log error: ' . $e->getMessage());
    }
}

// Auto-register shutdown handler for page access logging
AuditMiddleware::registerShutdownHandler();
?>

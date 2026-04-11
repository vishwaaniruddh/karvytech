<?php
require_once __DIR__ . '/../models/Menu.php';
require_once __DIR__ . '/../config/auth.php';

class MenuAccess {
    
    public static function checkAccess($requiredUrl = null) {
        // Get current user
        $currentUser = Auth::getCurrentUser();
        if (!$currentUser) {
            http_response_code(401);
            header('Location: ' . BASE_URL . '/auth/login.php');
            exit;
        }
        
        // If no specific URL provided, use current request URI
        if ($requiredUrl === null) {
            $requiredUrl = $_SERVER['REQUEST_URI'];
            // Remove base URL if present
            if (defined('BASE_URL') && BASE_URL) {
                $requiredUrl = str_replace(BASE_URL, '', $requiredUrl);
            }
            // Remove query parameters
            $requiredUrl = strtok($requiredUrl, '?');
        }
        
        // Check if user has access to this URL
        $menuModel = new Menu();
        $hasAccess = $menuModel->hasAccess($currentUser['id'], $currentUser['role'], $requiredUrl);
        
        if (!$hasAccess) {
            // Check if it's an AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Access denied. You do not have permission to access this resource.'
                ]);
                exit;
            } else {
                // Redirect to access denied page or dashboard
                http_response_code(403);
                header('Location: ' . BASE_URL . '/admin/dashboard.php?error=access_denied');
                exit;
            }
        }
        
        return true;
    }
    
    public static function requireMenuAccess($menuUrl) {
        return self::checkAccess($menuUrl);
    }
}
?>
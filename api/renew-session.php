<?php
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!Auth::isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }
    
    // Update session login time to extend the session
    $_SESSION['login_time'] = time();
    
    // Calculate new expiration time
    $expiresAt = time() + SESSION_TIMEOUT;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Session renewed successfully',
        'expires_at' => $expiresAt,
        'expires_in' => SESSION_TIMEOUT
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Session renewal failed: ' . $e->getMessage()]);
}
?>
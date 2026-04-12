<?php
require_once __DIR__ . '/../config/auth.php';

header('Content-Type: application/json');

try {
    $isValid = Auth::isLoggedIn();
    $timeRemaining = 0;
    
    if ($isValid && isset($_SESSION['login_time'])) {
        $timeRemaining = SESSION_TIMEOUT - (time() - $_SESSION['login_time']);
        $timeRemaining = max(0, $timeRemaining); // Ensure non-negative
    }
    
    echo json_encode([
        'valid' => $isValid,
        'time_remaining' => $timeRemaining,
        'user_id' => Auth::getUserId(),
        'username' => $_SESSION['username'] ?? null
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'valid' => false, 
        'message' => 'Session check failed: ' . $e->getMessage()
    ]);
}
?>
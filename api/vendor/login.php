<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';
require_once '../../includes/error_handler.php';
require_once '../../includes/logger.php';
require_once '../../includes/jwt_helper.php';
require_once '../../models/User.php';

header('Content-Type: application/json');

session_start();

// =======================
// Rate Limiting
// =======================
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = 0;
}

// Reset after 15 minutes
if (time() - $_SESSION['last_attempt'] > 900) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SESSION['login_attempts'] >= 5) {
    echo json_encode([
        'success' => false,
        'message' => 'Too many failed attempts. Try again after 15 minutes.',
        'locked' => true
    ]);
    exit;
}

// =======================
// Only POST allowed
// =======================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// =======================
// Input
// =======================
$emailOrPhone = trim($_POST['email_or_phone'] ?? '');
$password     = $_POST['password'] ?? '';
$rememberMe   = $_POST['remember_me'] ?? false;

if (!$emailOrPhone || !$password) {
    echo json_encode([
        'success' => false,
        'message' => 'Email/Phone and password are required'
    ]);
    exit;
}

try {
    $userModel = new User();
    $user = $userModel->findByEmailOrPhone($emailOrPhone);

    if (
        $user &&
        password_verify($password, $user['password_hash']) &&
        $user['status'] === 'active'
    ) {
        // =======================
        // Generate JWT
        // =======================
        $token = JWTHelper::generateToken(
            $user['id'],
            $user['username'],
            $user['role'],
            $user['vendor_id']
        );

        // Save token
        $userModel->updateToken($user['id'], $token);

        // Reset attempts
        $_SESSION['login_attempts'] = 0;

        Logger::logUserLogin($emailOrPhone, true);

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => [
                'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
                'phone'    => $user['phone'],
                'role'     => $user['role'],
                'vendor_id' => $user['vendor_id']
            ]
        ]);
        exit;
    }

    // =======================
    // Invalid credentials
    // =======================
    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt'] = time();

    Logger::logUserLogin($emailOrPhone, false);

    echo json_encode([
        'success'  => false,
        'message'  => 'Invalid credentials',
        'attempts' => $_SESSION['login_attempts']
    ]);
    exit;

} catch (Exception $e) {
    Logger::error('API Login Error', [
        'input' => $emailOrPhone,
        'error' => $e->getMessage()
    ]);

    echo json_encode([
        'success' => false,
        'message' => 'Server error. Please try again.'
    ]);
    exit;
}

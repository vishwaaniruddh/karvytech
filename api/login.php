<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed',
        'debug' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'expected' => 'POST'
        ]
    ]);
    exit;
}

// Get JSON input or form data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$response = [
    'success' => false,
    'message' => '',
    'debug' => [
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION,
        'input_received' => !empty($input),
        'database_connection' => false,
        'user_found' => false,
        'password_verified' => false,
        'role_check' => false,
        'session_started' => false
    ]
];

try {
    // Step 1: Validate input
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    
    $response['debug']['username_provided'] = !empty($username);
    $response['debug']['password_provided'] = !empty($password);
    
    if (empty($username) || empty($password)) {
        $response['message'] = 'Username and password are required';
        $response['debug']['validation_error'] = 'Missing credentials';
        echo json_encode($response);
        exit;
    }
    
    // Step 2: Test database connection
    try {
        $db = Database::getInstance()->getConnection();
        $response['debug']['database_connection'] = true;
        $response['debug']['database_type'] = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    } catch (Exception $e) {
        $response['message'] = 'Database connection failed';
        $response['debug']['database_error'] = $e->getMessage();
        echo json_encode($response);
        exit;
    }
    
    // Step 3: Find user by email, phone, or username
    $userModel = new User();
    
    // Try to find user by email or phone first (primary method)
    $user = $userModel->findByEmailOrPhone($username);
    $response['debug']['found_by_email_phone'] = !empty($user);
    
    // If not found, try by username (fallback)
    if (!$user) {
        $user = $userModel->findByUsername($username);
        $response['debug']['found_by_username'] = !empty($user);
    }
    
    $response['debug']['user_found'] = !empty($user);
    
    if (!$user) {
        // Check if user exists with different case
        $stmt = $db->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) OR phone = ?");
        $stmt->execute([$username, $username, $username]);
        $userCaseInsensitive = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $response['debug']['user_case_insensitive'] = !empty($userCaseInsensitive);
        
        if ($userCaseInsensitive) {
            $response['message'] = 'Try with: ' . ($userCaseInsensitive['email'] ?: $userCaseInsensitive['phone'] ?: $userCaseInsensitive['username']);
        } else {
            // Check if any users exist at all
            $stmt = $db->query("SELECT COUNT(*) as count FROM users");
            $userCount = $stmt->fetch(PDO::FETCH_ASSOC);
            $response['debug']['total_users'] = $userCount['count'];
            
            // Get list of existing emails/phones for debugging
            $stmt = $db->query("SELECT username, email, phone FROM users LIMIT 5");
            $existingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['debug']['existing_users'] = $existingUsers;
            
            $response['message'] = 'User not found. Try with email or phone number.';
        }
        echo json_encode($response);
        exit;
    }
    
    // Step 4: Check user status
    $response['debug']['user_status'] = $user['status'] ?? 'unknown';
    $response['debug']['user_role'] = $user['role'] ?? 'unknown';
    
    if (isset($user['status']) && $user['status'] !== 'active') {
        $response['message'] = 'User account is not active';
        $response['debug']['status_error'] = 'User status: ' . $user['status'];
        echo json_encode($response);
        exit;
    }
    
    // Step 5: Verify password
    $response['debug']['password_hash_exists'] = !empty($user['password_hash']);
    $response['debug']['plain_password_exists'] = !empty($user['plain_password']);
    
    $passwordVerified = false;
    
    // Try password_hash first
    if (!empty($user['password_hash'])) {
        $passwordVerified = password_verify($password, $user['password_hash']);
        $response['debug']['password_hash_verified'] = $passwordVerified;
    }
    
    // If password_hash fails, try plain_password (for testing)
    if (!$passwordVerified && !empty($user['plain_password'])) {
        $passwordVerified = ($password === $user['plain_password']);
        $response['debug']['plain_password_verified'] = $passwordVerified;
    }
    
    $response['debug']['password_verified'] = $passwordVerified;
    
    if (!$passwordVerified) {
        $response['message'] = 'Invalid password';
        $response['debug']['password_debug'] = [
            'provided_length' => strlen($password),
            'hash_length' => strlen($user['password_hash'] ?? ''),
            'plain_length' => strlen($user['plain_password'] ?? '')
        ];
        echo json_encode($response);
        exit;
    }
    
    // Step 6: Check role for admin login
    $requiredRole = $input['required_role'] ?? 'admin';
    $response['debug']['required_role'] = $requiredRole;
    $response['debug']['role_check'] = ($user['role'] === $requiredRole);
    
    if ($user['role'] !== $requiredRole) {
        $response['message'] = ucfirst($requiredRole) . ' access required. Your role: ' . $user['role'];
        echo json_encode($response);
        exit;
    }
    
    // Step 7: Start session and login
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    Auth::login($user);
    $response['debug']['session_started'] = true;
    $response['debug']['session_id'] = session_id();
    
    // Step 8: Success response
    $response['success'] = true;
    $response['message'] = 'Login successful';
    $response['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'email' => $user['email'] ?? ''
    ];
    $response['redirect'] = $requiredRole === 'admin' ? 'dashboard.php' : '../vendor/index.php';
    
} catch (Exception $e) {
    $response['message'] = 'Login error: ' . $e->getMessage();
    $response['debug']['exception'] = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
<?php
require_once '../config/database.php';
require_once '../config/auth.php';
// constants.php is already included by auth.php
require_once '../includes/error_handler.php';
require_once '../includes/logger.php';

$error = '';
$success = '';
$loginAttempts = 0;
$isLocked = false;

// Check if user is already logged in
if (Auth::isLoggedIn()) {
    $user = Auth::getCurrentUser();
    if ($user['role'] === VENDOR_ROLE || $user['role'] === 'contractor') {
        header('Location: ' . BASE_URL . '/contractor/');
    } else {
        // All other roles go to admin dashboard (superadmin, admin, manager, accountmanager, etc.)
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
    }
    exit();
}

// Check for rate limiting (simple implementation)
session_start();
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = 0;
}

// Reset attempts after 15 minutes
if (time() - $_SESSION['last_attempt'] > 900) {
    $_SESSION['login_attempts'] = 0;
}

$loginAttempts = $_SESSION['login_attempts'];
$isLocked = $loginAttempts >= 5;

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isLocked) {
        $error = 'Too many failed attempts. Please try again in 15 minutes.';
    } else {
        $emailOrPhone = trim($_POST['email_or_phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);
        
        if (empty($emailOrPhone) || empty($password)) {
            $error = 'Please enter both email/phone and password.';
        } else {
            try {
                require_once '../includes/jwt_helper.php';
                require_once '../models/User.php';
                
                $userModel = new User();
                $user = $userModel->findByEmailOrPhone($emailOrPhone);
                
                if ($user && password_verify($password, $user['password_hash']) && $user['status'] === 'active') {
                    // Generate JWT token
                    $token = JWTHelper::generateToken($user['id'], $user['username'], $user['role']);
                    
                    // Update user token in database
                    $userModel->updateToken($user['id'], $token);
                    
                    // Handle remember me
                    if ($rememberMe) {
                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true); // 30 days
                    }
                    
                    // Reset login attempts on successful login
                    $_SESSION['login_attempts'] = 0;
                    
                    // Login successful
                    Auth::login($user);
                    Logger::logUserLogin($emailOrPhone, true);
                    
                    // Audit logging
                    require_once '../includes/audit_integration.php';
                    auditLogLogin($user['id'], $user['username'], $user['role']);
                    
                    // Return JSON response for AJAX
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        $redirectUrl = ($user['role'] === VENDOR_ROLE || $user['role'] === 'contractor') 
                            ? BASE_URL . '/contractor/' 
                            : BASE_URL . '/admin/dashboard.php';
                        echo json_encode([
                            'success' => true,
                            'redirect' => $redirectUrl
                        ]);
                        exit();
                    }
                    
                    // Redirect based on role
                    if ($user['role'] === VENDOR_ROLE || $user['role'] === 'contractor') {
                        header('Location: ' . BASE_URL . '/contractor/');
                    } else {
                        // All other roles go to admin dashboard (superadmin, admin, manager, accountmanager, etc.)
                        header('Location: ' . BASE_URL . '/admin/dashboard.php');
                    }
                    exit();
                } else {
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt'] = time();
                    $error = 'Invalid email/phone or password.';
                    Logger::logUserLogin($emailOrPhone, false);
                    
                    // Return JSON response for AJAX
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => false,
                            'message' => $error,
                            'attempts' => $_SESSION['login_attempts']
                        ]);
                        exit();
                    }
                }
            } catch (Exception $e) {
                $error = 'Login failed. Please try again.';
                Logger::error('Login error', ['email_or_phone' => $emailOrPhone, 'error' => $e->getMessage()]);
                
                // Return JSON response for AJAX
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => $error
                    ]);
                    exit();
                }
            }
        }
    }
}

$title = 'Login - ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/custom.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .karvy-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .login-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        @media (max-width: 768px) {
            .login-card .grid {
                grid-template-columns: 1fr;
            }
            
            .login-card .grid > div:first-child {
                min-height: 300px;
            }
            
            .login-card .grid > div:last-child {
                padding: 2rem 1.5rem;
            }
        }
        
        .login-card {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .floating-label {
            position: relative;
        }
        
        .floating-label input {
            padding: 1rem 1rem 0.5rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            background: #f9fafb;
            transition: all 0.3s ease;
        }
        
        .floating-label input:focus {
            border-color: #6366f1;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .floating-label label {
            position: absolute;
            left: 1rem;
            top: 1rem;
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s ease;
            pointer-events: none;
        }
        
        .floating-label input:focus + label,
        .floating-label input:not(:placeholder-shown) + label {
            top: 0.25rem;
            font-size: 0.75rem;
            color: #6366f1;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-primary.loading {
            pointer-events: none;
        }
        
        .spinner {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 2px solid white;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .slide-in {
            animation: slideIn 0.8s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-weight: 500;
            animation: slideDown 0.3s ease-out;
        }
        
        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6b7280;
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: #6366f1;
        }
        
        .checkbox-custom {
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .checkbox-custom:checked {
            background: #6366f1;
            border-color: #6366f1;
        }
        
        .checkbox-custom:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 0.875rem;
            font-weight: bold;
        }
        
        .security-info {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 1px solid #bae6fd;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1.5rem;
        }
        
        .floating-particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>
<body class="login-container">
    <!-- Floating Particles Background -->
    <div class="floating-particles">
        <div class="particle" style="left: 10%; width: 20px; height: 20px; animation-delay: 0s;"></div>
        <div class="particle" style="left: 20%; width: 15px; height: 15px; animation-delay: 1s;"></div>
        <div class="particle" style="left: 35%; width: 25px; height: 25px; animation-delay: 2s;"></div>
        <div class="particle" style="left: 50%; width: 18px; height: 18px; animation-delay: 3s;"></div>
        <div class="particle" style="left: 65%; width: 22px; height: 22px; animation-delay: 4s;"></div>
        <div class="particle" style="left: 80%; width: 16px; height: 16px; animation-delay: 5s;"></div>
        <div class="particle" style="left: 90%; width: 24px; height: 24px; animation-delay: 6s;"></div>
    </div>

    <div class="min-h-screen flex items-center justify-center py-8 px-4 sm:px-6 lg:px-8 relative">
        <div class="max-w-6xl w-full">
            <!-- Main Login Container -->
            <div class="login-card rounded-2xl overflow-hidden shadow-2xl slide-in">
                <div class="grid md:grid-cols-2 min-h-[600px]">
                    
                    <!-- Left Side - Branding -->
                    <div class="bg-gradient-to-br from-purple-600 via-purple-700 to-indigo-800 p-8 flex flex-col justify-center items-center text-white relative overflow-hidden">
                        <!-- Background Pattern -->
                        <div class="absolute inset-0 opacity-10">
                            <div class="absolute top-10 left-10 w-32 h-32 border border-white/20 rounded-full"></div>
                            <div class="absolute top-32 right-16 w-24 h-24 border border-white/20 rounded-full"></div>
                            <div class="absolute bottom-20 left-20 w-40 h-40 border border-white/20 rounded-full"></div>
                            <div class="absolute bottom-10 right-10 w-20 h-20 border border-white/20 rounded-full"></div>
                        </div>
                        
                        <div class="relative z-10 text-center fade-in">
                            <!-- Logo -->
                            <div class="mx-auto w-24 h-24 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center shadow-lg mb-8 border border-white/20">
                                <svg class="w-14 h-14 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                                </svg>
                            </div>
                            
                            <!-- Company Name -->
                            <h1 class="text-4xl font-bold mb-4" style="font-family: 'Playfair Display', serif;">
                                Karvy Technologies
                            </h1>
                            
                            <!-- Tagline -->
                            <p class="text-xl text-purple-100 mb-6 font-medium">
                                Site Installation Management
                            </p>
                            
                            <!-- Description -->
                            <div class="space-y-4 text-purple-100">
                                <p class="text-lg font-medium">Secure Access Portal</p>
                                <div class="w-16 h-1 bg-white/30 mx-auto rounded"></div>
                                <p class="text-sm leading-relaxed max-w-sm mx-auto">
                                    Complete site installation management system for administrators and field vendors.
                                </p>
                            </div>
                            
                            <!-- Features -->
                            <div class="mt-8 space-y-3 text-left max-w-sm mx-auto">
                                <div class="flex items-center text-sm text-purple-100">
                                    <svg class="w-4 h-4 mr-3 text-purple-300" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Admin & Vendor Management
                                </div>
                                <div class="flex items-center text-sm text-purple-100">
                                    <svg class="w-4 h-4 mr-3 text-purple-300" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Site Survey & Installation
                                </div>
                                <div class="flex items-center text-sm text-purple-100">
                                    <svg class="w-4 h-4 mr-3 text-purple-300" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Material & Inventory Tracking
                                </div>
                                <div class="flex items-center text-sm text-purple-100">
                                    <svg class="w-4 h-4 mr-3 text-purple-300" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Comprehensive Reporting
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Side - Login Form -->
                    <div class="bg-white p-8 flex flex-col justify-center">
                        <div class="text-center mb-8">
                            <h2 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back</h2>
                            <p class="text-gray-600">Sign in to access your dashboard</p>
                        </div>

                        <!-- Alert Messages -->
                        <div id="alert-container">
                            <?php if ($error): ?>
                                <div class="alert alert-error">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <?php echo htmlspecialchars($error); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <?php echo htmlspecialchars($success); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Login Form -->
                        <form id="loginForm" class="space-y-6" method="POST">
                            <!-- Email/Phone Field -->
                            <div class="floating-label">
                        <input 
                            type="text" 
                            id="email_or_phone" 
                            name="email_or_phone" 
                            placeholder=" "
                            required 
                            autocomplete="username"
                            value="<?php echo htmlspecialchars($_POST['email_or_phone'] ?? ''); ?>"
                            <?php echo $isLocked ? 'disabled' : ''; ?>
                        >
                        <label for="email_or_phone">Email or Phone Number</label>
                    </div>

                            <!-- Password Field -->
                            <div class="floating-label relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder=" "
                            required 
                            autocomplete="current-password"
                            <?php echo $isLocked ? 'disabled' : ''; ?>
                        >
                        <label for="password">Password</label>
                        <div class="password-toggle" onclick="togglePassword()">
                            <svg id="eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg id="eye-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                            </svg>
                        </div>
                    </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="remember_me" 
                                name="remember_me" 
                                class="checkbox-custom"
                                <?php echo $isLocked ? 'disabled' : ''; ?>
                            >
                            <label for="remember_me" class="ml-3 text-sm text-gray-700 cursor-pointer">
                                Remember me for 30 days
                            </label>
                        </div>
                        <a href="#" class="text-sm text-indigo-600 hover:text-indigo-500 font-medium">
                            Forgot password?
                        </a>
                    </div>

                            <!-- Login Button -->
                            <button 
                        type="submit" 
                        id="loginBtn"
                        class="btn-primary w-full flex items-center justify-center"
                        <?php echo $isLocked ? 'disabled' : ''; ?>
                    >
                        <span id="btn-text">Sign In Securely</span>
                        <div id="btn-spinner" class="spinner hidden ml-2"></div>
                    </button>

                            <?php if ($isLocked): ?>
                                <div class="text-center text-red-600 text-sm font-medium">
                                    Account temporarily locked due to multiple failed attempts.
                                </div>
                            <?php endif; ?>
                        </form>

                        <!-- Security Information -->
                        <div class="mt-8 p-4 bg-purple-50 rounded-lg border border-purple-200">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-purple-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-semibold text-purple-900 mb-1">Secure Access</h4>
                                    <p class="text-xs text-purple-700">
                                        Encrypted connections with role-based access control
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Demo Credentials -->
                        <!-- <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-900 mb-2">Demo Credentials</h4>
                            <div class="text-xs text-gray-600 space-y-1">
                                <p><strong>Admin:</strong> admin@example.com / admin123</p>
                                <p><strong>Vendor:</strong> vendor@example.com / vendor123</p>
                                <p><strong>Note:</strong> Use email or phone number to login</p>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="text-center text-white/60 text-sm mt-8 fade-in">
                <p>&copy; <?php echo date('Y'); ?> Karvy Technologies Pvt Ltd. All rights reserved.</p>
                <p class="mt-1">Powered by advanced security protocols</p>
            </div>
        </div>
    </div>

    <script>
        // Password visibility toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }

        // Enhanced form submission with AJAX
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('loginBtn');
            const btnText = document.getElementById('btn-text');
            const btnSpinner = document.getElementById('btn-spinner');
            const alertContainer = document.getElementById('alert-container');
            
            // Show loading state
            btn.classList.add('loading');
            btnText.textContent = 'Signing In...';
            btnSpinner.classList.remove('hidden');
            
            // Clear previous alerts
            alertContainer.innerHTML = '';
            
            // Prepare form data
            const formData = new FormData(this);
            
            // Send AJAX request
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alertContainer.innerHTML = `
                        <div class="alert alert-success">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Login successful! Redirecting...
                            </div>
                        </div>
                    `;
                    
                    btnText.textContent = 'Redirecting...';
                    
                    // Redirect after a short delay
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    // Show error message
                    alertContainer.innerHTML = `
                        <div class="alert alert-error">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                ${data.message}
                                ${data.attempts ? ` (${data.attempts}/5 attempts)` : ''}
                            </div>
                        </div>
                    `;
                    
                    // Reset button state
                    btn.classList.remove('loading');
                    btnText.textContent = 'Sign In Securely';
                    btnSpinner.classList.add('hidden');
                    
                    // Shake animation for error
                    btn.style.animation = 'shake 0.5s ease-in-out';
                    setTimeout(() => {
                        btn.style.animation = '';
                    }, 500);
                }
            })
            .catch(error => {
                console.error('Login error:', error);
                alertContainer.innerHTML = `
                    <div class="alert alert-error">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            Connection error. Please try again.
                        </div>
                    </div>
                `;
                
                // Reset button state
                btn.classList.remove('loading');
                btnText.textContent = 'Sign In Securely';
                btnSpinner.classList.add('hidden');
            });
        });

        // Add shake animation CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
        `;
        document.head.appendChild(style);

        // Auto-focus first input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email_or_phone').focus();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Alt + L to focus login field
            if (e.altKey && e.key === 'l') {
                e.preventDefault();
                document.getElementById('email_or_phone').focus();
            }
            // Alt + P to focus password field
            if (e.altKey && e.key === 'p') {
                e.preventDefault();
                document.getElementById('password').focus();
            }
        });
    </script>
</body>
</html>
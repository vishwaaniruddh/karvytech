<?php
require_once 'config/auth.php';
// constants.php is already included by auth.php

// Check if user is already logged in and redirect to appropriate dashboard
if (Auth::isLoggedIn()) {
    $user = Auth::getCurrentUser();
    if ($user['role'] === ADMIN_ROLE) {
        header('Location: admin/dashboard.php');
        exit();
    } elseif ($user['role'] === VENDOR_ROLE) {
        header('Location: vendor/');
        exit();
    }
}


// If no session found, redirect to login
header('Location: auth/login.php');
exit();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Site Installation Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                <?php echo APP_NAME; ?>
            </h1>
            <p class="text-xl text-gray-600 mb-8">
                Complete Site Installation Management System
            </p>
            <div class="w-24 h-1 bg-indigo-600 mx-auto rounded"></div>
        </div>

        <!-- Main Navigation Cards -->
        <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            
            <!-- Admin Panel Card -->
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6">
                    <div class="flex items-center">
                        <div class="bg-white bg-opacity-20 rounded-lg p-3 mr-4">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-white">Admin Panel</h2>
                            <p class="text-blue-100">Complete system management</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-center text-gray-600">
                            <svg class="w-4 h-4 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Site & Vendor Management
                        </li>
                        <li class="flex items-center text-gray-600">
                            <svg class="w-4 h-4 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Survey & Installation Tracking
                        </li>
                        <li class="flex items-center text-gray-600">
                            <svg class="w-4 h-4 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Inventory & Material Management
                        </li>
                        <li class="flex items-center text-gray-600">
                            <svg class="w-4 h-4 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Comprehensive Reports
                        </li>
                    </ul>
                    <a href="auth/login.php" class="block w-full bg-blue-600 text-white text-center py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors duration-200 font-medium">
                        Access Admin Panel
                    </a>
                </div>
            </div>

            <!-- Vendor Portal Card -->
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-6">
                    <div class="flex items-center">
                        <div class="bg-white bg-opacity-20 rounded-lg p-3 mr-4">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-white">Vendor Portal</h2>
                            <p class="text-green-100">Field operations management</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-center text-gray-600">
                            <svg class="w-4 h-4 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Site Survey Management
                        </li>
                        <li class="flex items-center text-gray-600">
                            <svg class="w-4 h-4 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Material Request & Tracking
                        </li>
                        <li class="flex items-center text-gray-600">
                            <svg class="w-4 h-4 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Installation Progress Updates
                        </li>
                        <li class="flex items-center text-gray-600">
                            <svg class="w-4 h-4 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Mobile-Friendly Interface
                        </li>
                    </ul>
                    <a href="auth/login.php" class="block w-full bg-green-600 text-white text-center py-3 px-4 rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium">
                        Access Vendor Portal
                    </a>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <!-- <div class="mt-12 max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">System Status</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div class="p-3">
                        <div class="w-3 h-3 bg-green-500 rounded-full mx-auto mb-2"></div>
                        <div class="text-sm font-medium text-gray-900">Database</div>
                        <div class="text-xs text-gray-500">Connected</div>
                    </div>
                    <div class="p-3">
                        <div class="w-3 h-3 bg-green-500 rounded-full mx-auto mb-2"></div>
                        <div class="text-sm font-medium text-gray-900">Authentication</div>
                        <div class="text-xs text-gray-500">Active</div>
                    </div>
                    <div class="p-3">
                        <div class="w-3 h-3 bg-green-500 rounded-full mx-auto mb-2"></div>
                        <div class="text-sm font-medium text-gray-900">File System</div>
                        <div class="text-xs text-gray-500">Ready</div>
                    </div>
                    <div class="p-3">
                        <div class="w-3 h-3 bg-green-500 rounded-full mx-auto mb-2"></div>
                        <div class="text-sm font-medium text-gray-900">Reports</div>
                        <div class="text-xs text-gray-500">Available</div>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Test Credentials -->
        <!-- <div class="mt-8 max-w-lg mx-auto">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="text-sm font-medium text-blue-800 mb-3 text-center">Test Credentials</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-blue-600">
                    <div class="text-center">
                        <div class="font-semibold">Admin Access</div>
                        <div>Username: admin_test</div>
                        <div>Password: admin123</div>
                    </div>
                    <div class="text-center">
                        <div class="font-semibold">Vendor Access</div>
                        <div>Username: vendor_test1</div>
                        <div>Password: vendor123</div>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Footer -->
        <div class="mt-12 text-center text-gray-500 text-sm">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
            <p class="mt-1">Complete Site Installation Management System</p>
        </div>
    </div>
</body>
</html>
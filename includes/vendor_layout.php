<?php
require_once __DIR__ . '/../models/VendorPermission.php';
require_once __DIR__ . '/../includes/audit_integration.php';
require_once __DIR__ . '/../includes/rbac_helper.php';

$permissionModel = new VendorPermission();
$vendorPermissions = $permissionModel->getUserPermissions(Auth::getUserId());

// If no vendor permissions are set, check RBAC permissions or use defaults
if (empty($vendorPermissions)) {
    $currentUser = Auth::getCurrentUser();
    $userRole = $currentUser['role'] ?? '';
    
    // For contractor/vendor users, check RBAC permissions or apply defaults
    if ($userRole === 'contractor' || $userRole === 'vendor') {
        // Try to get RBAC permissions
        $hasRbacPerms = false;
        if (!empty($currentUser['role_id'])) {
            // Check if user has any RBAC permissions
            require_once __DIR__ . '/../models/Role.php';
            $roleModel = new Role();
            $rolePerms = $roleModel->getRolePermissions($currentUser['role_id']);
            $hasRbacPerms = !empty($rolePerms);
        }
        
        // Apply default permissions (these will work regardless of RBAC)
        $vendorPermissions = [
            'view_my_sites' => true,
            'view_site_surveys' => true,
            'view_installations' => true,
            'view_inventory' => true,
            'view_inventory_overview' => true,
            'view_material_requests' => true,
            'view_material_received' => true,
            'view_material_dispatches' => true
        ];
    }
}



// Ensure url() function is available (fallback)
if (!function_exists('url')) {
    function url($path = '') {
        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        $path = ltrim($path, '/');
        return $path ? $baseUrl . '/' . $path : $baseUrl;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Contractor Portal'; ?> - Site Installation Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo url('/assets/css/admin.css'); ?>">
    <style>
        .vendor-sidebar {
            background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Karvy Brand Styling */
        .karvy-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.125rem;
            background: linear-gradient(135deg, #ffffff 0%, #e0e7ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 0.5px;
        }
        
        /* Fallback for browsers that don't support background-clip */
        @supports not (-webkit-background-clip: text) {
            .karvy-brand {
                color: #ffffff;
                text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            }
        }
        
        .karvy-subtitle {
            font-family: 'Inter', sans-serif;
            font-weight: 300;
            font-size: 0.75rem;
            color: #e0e7ff;
            letter-spacing: 1px;
        }
        
        /* Enhanced Form Styles */
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
            background-color: #ffffff;
            transition: all 0.2s ease;
        }
        
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-input:disabled {
            background-color: #f3f4f6;
            color: #6b7280;
            cursor: not-allowed;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #ffffff;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
        }
        
        .form-section h4 {
            color: #1f2937;
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        /* Grid layouts */
        .grid {
            display: grid;
        }
        
        .grid-cols-1 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        
        .grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        
        .gap-6 {
            gap: 1.5rem;
        }
        
        @media (min-width: 768px) {
            .md\\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .md\\:col-span-2 {
                grid-column: span 2 / span 2;
            }
        }
        
        /* Button enhancements */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: #3b82f6;
            color: #ffffff;
            border-color: #3b82f6;
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        
        .btn-secondary {
            background-color: #ffffff;
            color: #374151;
            border-color: #d1d5db;
        }
        
        .btn-secondary:hover {
            background-color: #f9fafb;
            border-color: #9ca3af;
        }
        
        /* Image preview styles */
        .preview img {
            border: 2px solid #e5e7eb;
            transition: all 0.2s ease;
        }
        
        .preview img:hover {
            border-color: #3b82f6;
            transform: scale(1.05);
        }
        
        /* Alert styles */
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background-color: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        
        .alert-error {
            background-color: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
        }
        
        /* Utility classes */
        .flex {
            display: flex;
        }
        
        .justify-between {
            justify-content: space-between;
        }
        
        .justify-end {
            justify-content: flex-end;
        }
        
        .items-center {
            align-items: center;
        }
        
        .space-x-2 > * + * {
            margin-left: 0.5rem;
        }
        
        .space-x-4 > * + * {
            margin-left: 1rem;
        }
        
        .space-y-6 > * + * {
            margin-top: 1.5rem;
        }
        
        .mb-8 {
            margin-bottom: 2rem;
        }
        
        .mt-2 {
            margin-top: 0.5rem;
        }
        
        .text-xs {
            font-size: 0.75rem;
        }
        
        .text-gray-500 {
            color: #6b7280;
        }
        .vendor-badge {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            margin: 4px 0;
            border-radius: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .sidebar-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(4px);
        }
        .sidebar-item.active {
            background-color: rgba(255, 255, 255, 0.15);
            border-left: 4px solid #f59e0b;
        }
        
        .sidebar-subitem {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        
        .sidebar-subitem:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(2px);
        }
        .vendor-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .stats-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        .stats-icon {
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .professional-table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .table-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        .mobile-responsive {
            display: none;
        }
        @media (max-width: 1024px) {
            .vendor-sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 50;
                height: 100vh;
            }
            .vendor-sidebar.show {
                transform: translateX(0);
            }
            .mobile-responsive {
                display: block;
            }
        }
        
        /* User dropdown styles */
        #user-dropdown {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        #user-dropdown.hidden {
            display: none !important;
        }
        
        #user-dropdown:not(.hidden) {
            display: block !important;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <div class="vendor-sidebar w-64 shadow-lg">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-blue-800">
                    <a href="<?php echo url('/contractor/'); ?>" class="flex items-center hover:opacity-80 transition-opacity">
                        <div class="sidebar-text">
                            <h1 class="text-lg font-bold text-white karvy-brand">Karvy Technologies</h1>
                            <p class="text-xs text-gray-300 karvy-subtitle">Pvt Ltd</p>
                        </div>
                    </a>
                </div>
                
              

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-4 space-y-2">
                    <a href="<?php echo url('/contractor/'); ?>" class="sidebar-item text-white hover:bg-blue-800">
                        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                        </svg>
                        Dashboard
                    </a>
                    
                    <?php if ($vendorPermissions['view_my_sites'] ?? false): ?>
                    <a href="<?php echo url('/contractor/sites/'); ?>" class="sidebar-item text-white hover:bg-blue-800">
                        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"></path>
                        </svg>
                        My Sites
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($vendorPermissions['view_site_surveys'] ?? false): ?>
                    <a href="<?php echo url('/contractor/surveys.php'); ?>" class="sidebar-item text-white hover:bg-blue-800">
                        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                        </svg>
                        Site Surveys
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($vendorPermissions['view_installations'] ?? false): ?>
                    <a href="<?php echo url('/contractor/installations.php'); ?>" class="sidebar-item text-white hover:bg-blue-800">
                        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9.504 1.132a1 1 0 01.992 0l1.75 1a1 1 0 11-.992 1.736L10 3.152l-1.254.716a1 1 0 11-.992-1.736l1.75-1zM5.618 4.504a1 1 0 01-.372 1.364L5.016 6l.23.132a1 1 0 11-.992 1.736L3 7.723V8a1 1 0 01-2 0V6a.996.996 0 01.52-.878l1.734-.99a1 1 0 011.364.372zm8.764 0a1 1 0 011.364-.372l1.734.99A.996.996 0 0118 6v2a1 1 0 11-2 0v-.277l-1.254.145a1 1 0 11-.992-1.736L14.984 6l-.23-.132a1 1 0 01-.372-1.364zm-7 4a1 1 0 011.364-.372L10 8.848l1.254-.716a1 1 0 11.992 1.736L11 10.723V12a1 1 0 11-2 0v-1.277l-1.246-.855a1 1 0 01-.372-1.364zM3 11a1 1 0 011 1v1.277l1.246.855a1 1 0 11-.992 1.736l-1.75-1A1 1 0 012 14v-2a1 1 0 011-1zm14 0a1 1 0 011 1v2a1 1 0 01-.504.868l-1.75 1a1 1 0 11-.992-1.736L16 13.277V12a1 1 0 011-1zm-9.618 5.504a1 1 0 011.364-.372l.254.145V16a1 1 0 112 0v.277l.254-.145a1 1 0 11.992 1.736l-1.75 1a.996.996 0 01-.992 0l-1.75-1a1 1 0 01-.372-1.364z" clip-rule="evenodd"></path>
                        </svg>
                        Installations
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($vendorPermissions['view_inventory'] ?? true): ?>
                    <!-- Inventory Main Menu with Dropdown -->
                    <div class="relative">
                        <button onclick="toggleInventoryMenu()" class="sidebar-item text-white hover:bg-blue-800 w-full flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM8 15a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                Inventory
                            </div>
                            <svg id="inventory-arrow" class="w-4 h-4 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        
                        <!-- Inventory Submenu -->
                        <div id="inventory-submenu" class="hidden ml-8 mt-2 space-y-1">
                            <?php if ($vendorPermissions['view_inventory_overview'] ?? false): ?>
                            <a href="<?php echo url('/contractor/inventory/'); ?>" class="sidebar-subitem text-gray-300 hover:text-white hover:bg-blue-800">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                                </svg>
                                Inventory Overview
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($vendorPermissions['view_material_requests'] ?? false): ?>
                            <a href="<?php echo url('/contractor/material-requests-list.php'); ?>" class="sidebar-subitem text-gray-300 hover:text-white hover:bg-blue-800">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                </svg>
                                Material Requests
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($vendorPermissions['view_material_received'] ?? false): ?>
                            <a href="<?php echo url('/contractor/material-received.php'); ?>" class="sidebar-subitem text-gray-300 hover:text-white hover:bg-blue-800">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                Material Received
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($vendorPermissions['view_material_dispatches'] ?? false): ?>
                            <a href="<?php echo url('/contractor/material-dispatches.php'); ?>" class="sidebar-subitem text-gray-300 hover:text-white hover:bg-blue-800">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Material Dispatches
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </nav>


            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="vendor-header">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center">
                        <button id="toggleSidebar" class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <div class="ml-2">
                            <h1 class="text-xl font-semibold text-gray-900"><?php echo $title ?? 'Contractor Portal'; ?></h1>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Environment Indicator -->
                        <?php 
                        // Ensure constants are loaded
                        if (!function_exists('getEnvironment')) {
                            require_once __DIR__ . '/../config/constants.php';
                        }
                        
                        $env = function_exists('getEnvironment') ? getEnvironment() : (defined('APP_ENV') ? APP_ENV : 'unknown');
                        $envColors = [
                            'development' => 'bg-green-500 text-white',
                            'testing' => 'bg-yellow-500 text-black',
                            'production' => 'bg-red-500 text-white'
                        ];
                        $envColor = $envColors[$env] ?? 'bg-gray-500 text-white';
                        ?>
                        <span class="<?php echo $envColor; ?> px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide shadow-sm">
                            <?php echo strtoupper($env); ?>
                        </span>
                        
                        <div class="relative" id="user-menu">
                            <button id="user-menu-button" onclick="toggleUserDropdown()" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 hover:bg-gray-50 px-3 py-2 rounded-lg transition-colors">
                                <div class="vendor-badge w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-xs">
                                    V
                                </div>
                                <span class="ml-2 text-gray-700 font-medium"><?php echo htmlspecialchars(Auth::getCurrentUser()['username']); ?></span>
                                <svg class="ml-2 w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                            <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 border border-gray-200" style="z-index: 9999;">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars(Auth::getCurrentUser()['username']); ?></p>
                                    <p class="text-xs text-gray-500">Vendor Account</p>
                                </div>
                                <a href="<?php echo url('/contractor/profile.php'); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                    My Profile
                                </a>
                                <div class="border-t border-gray-100 mt-2 pt-2">
                                    <a href="<?php echo url('/auth/logout.php'); ?>" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4 mr-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        Sign Out
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <div class="w-full">
                    <?php echo $content ?? ''; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-40 lg:hidden hidden"></div>

    <!-- Scripts -->
    <script src="<?php echo url('/assets/js/admin.js'); ?>"></script>
    <script>
        // Mobile sidebar toggle
        document.getElementById('toggleSidebar')?.addEventListener('click', function() {
            const sidebar = document.querySelector('.vendor-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('show');
            overlay.classList.toggle('hidden');
        });

        // Close sidebar when clicking overlay
        document.getElementById('sidebar-overlay')?.addEventListener('click', function() {
            const sidebar = document.querySelector('.vendor-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.remove('show');
            overlay.classList.add('hidden');
        });

        // User dropdown toggle function
        function toggleUserDropdown() {
            const dropdown = document.getElementById('user-dropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
                console.log('Dropdown toggled, hidden class:', dropdown.classList.contains('hidden'));
            }
        }

        // User dropdown toggle event listener (backup)
        document.getElementById('user-menu-button')?.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleUserDropdown();
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('user-menu');
            const dropdown = document.getElementById('user-dropdown');
            
            if (userMenu && dropdown && !userMenu.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Add active class to current page sidebar item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const sidebarItems = document.querySelectorAll('.sidebar-item');
            
            sidebarItems.forEach(item => {
                if (item.getAttribute('href') && currentPath.includes(item.getAttribute('href'))) {
                    item.classList.add('active');
                }
            });
        });
        
        // Toggle inventory submenu
        function toggleInventoryMenu() {
            const submenu = document.getElementById('inventory-submenu');
            const arrow = document.getElementById('inventory-arrow');
            
            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                arrow.style.transform = 'rotate(180deg)';
            } else {
                submenu.classList.add('hidden');
                arrow.style.transform = 'rotate(0deg)';
            }
        }
        
        // Auto-expand inventory menu if on inventory page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const inventoryPaths = ['/contractor/inventory/', '/vendor/material-request.php', '/contractor/material-received.php', '/contractor/material-dispatches.php'];
            
            if (inventoryPaths.some(path => currentPath.includes(path))) {
                const submenu = document.getElementById('inventory-submenu');
                const arrow = document.getElementById('inventory-arrow');
                if (submenu && arrow) {
                    submenu.classList.remove('hidden');
                    arrow.style.transform = 'rotate(180deg)';
                }
            }
        });
        // Premium Toast Notification System
        function showToast(message, type = 'success', duration = 4000) {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.style.cssText = `
                    position: fixed;
                    top: 24px;
                    right: 24px;
                    z-index: 9999;
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                    pointer-events: none;
                `;
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = `toast toast-${type} transform translate-x-full opacity-0 transition-all duration-300 ease-out`;
            
            const icons = {
                success: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                error: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                warning: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
                info: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
            };

            const colors = {
                success: 'bg-emerald-500 shadow-emerald-200',
                error: 'bg-rose-500 shadow-rose-200',
                warning: 'bg-amber-500 shadow-amber-200',
                info: 'bg-sky-500 shadow-sky-200'
            };

            toast.style.pointerEvents = 'auto';
            toast.innerHTML = `
                <div class="flex items-center p-4 min-w-[320px] max-w-md ${colors[type]} text-white rounded-2xl shadow-xl space-x-4 border border-white/20 backdrop-blur-sm">
                    <div class="flex-shrink-0 text-white/90">
                        ${icons[type] || icons.info}
                    </div>
                    <div class="flex-1 font-medium text-sm">
                        ${message}
                    </div>
                    <button onclick="this.closest('.toast').remove()" class="flex-shrink-0 text-white/60 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            `;

            container.appendChild(toast);

            // Animate in
            requestAnimationFrame(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            });

            // Auto-remove
            if (duration !== 0) {
                setTimeout(() => {
                    toast.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, duration);
            }
        }

        // Global Alert Bridge
        function showAlert(message, type = 'info') {
            showToast(message, type);
        }

        // Override standard browser alert
        window.alert = function(message) {
            showAlert(message, 'info');
        };

        // Global Confirmation Modal
        function showConfirm(title, message, options = {}) {
            const {
                confirmText = 'Yes, Proceed',
                cancelText = 'Cancel',
                confirmType = 'primary' // primary, danger, success
            } = options;

            // Create modal if not exists
            let modal = document.getElementById('global-confirm-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'global-confirm-modal';
                modal.className = 'fixed inset-0 z-[10000] hidden flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-md transition-all duration-300 opacity-0';
                modal.innerHTML = `
                    <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 transform transition-all scale-95 duration-300">
                        <div id="confirm-icon-container" class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6"></div>
                        <h3 id="confirm-title" class="text-2xl font-bold text-gray-900 text-center mb-3"></h3>
                        <p id="confirm-message" class="text-gray-500 text-center mb-8 leading-relaxed"></p>
                        <div class="flex space-x-3">
                            <button id="confirm-cancel" class="flex-1 px-5 py-3 border border-gray-200 text-gray-700 font-semibold rounded-2xl hover:bg-gray-50 transition-all active:scale-95">
                                ${cancelText}
                            </button>
                            <button id="confirm-ok" class="flex-1 px-5 py-3 text-white font-semibold rounded-2xl shadow-lg transition-all active:scale-95">
                                ${confirmText}
                            </button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            const iconContainer = modal.querySelector('#confirm-icon-container');
            const okBtn = modal.querySelector('#confirm-ok');
            const cancelBtn = modal.querySelector('#confirm-cancel');
            
            modal.querySelector('#confirm-title').textContent = title;
            modal.querySelector('#confirm-message').textContent = message;
            okBtn.textContent = confirmText;
            cancelBtn.textContent = cancelText;

            // Configure style based on type
            const typeConfig = {
                danger: {
                    icon: '<svg class="w-10 h-10 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
                    bg: 'bg-rose-100',
                    btn: 'bg-rose-600 hover:bg-rose-700 shadow-rose-200'
                },
                success: {
                    icon: '<svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                    bg: 'bg-emerald-100',
                    btn: 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-200'
                },
                primary: {
                    icon: '<svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                    bg: 'bg-blue-100',
                    btn: 'bg-blue-600 hover:bg-blue-700 shadow-blue-200'
                }
            };

            const config = typeConfig[confirmType] || typeConfig.primary;
            iconContainer.innerHTML = config.icon;
            iconContainer.className = `w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 ${config.bg}`;
            okBtn.className = `flex-1 px-5 py-3 text-white font-semibold rounded-2xl shadow-lg transition-all active:scale-95 ${config.btn}`;

            // Show modal
            modal.classList.remove('hidden');
            requestAnimationFrame(() => {
                modal.classList.add('opacity-100');
                modal.querySelector('div').classList.remove('scale-95');
                modal.querySelector('div').classList.add('scale-100');
            });

            return new Promise((resolve) => {
                const cleanup = () => {
                    modal.classList.remove('opacity-100');
                    modal.querySelector('div').classList.remove('scale-100');
                    modal.querySelector('div').classList.add('scale-95');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 300);
                };

                okBtn.onclick = () => {
                    cleanup();
                    resolve(true);
                };
                cancelBtn.onclick = () => {
                    cleanup();
                    resolve(false);
                };
            });
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modals = document.querySelectorAll('.modal, #global-confirm-modal');
                modals.forEach(modal => {
                    if (!modal.classList.contains('hidden')) {
                        if (modal.id === 'global-confirm-modal') {
                            modal.querySelector('#confirm-cancel').click();
                        } else if (typeof closeModal === 'function') {
                            closeModal(modal.id);
                        } else {
                            modal.classList.add('hidden');
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
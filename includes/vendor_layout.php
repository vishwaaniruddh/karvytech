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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo url('/assets/css/admin.css'); ?>">
    <style>
        * { font-family: 'Inter', sans-serif; }

        .vendor-sidebar {
            background: linear-gradient(180deg, #0a0e1a 0%, #0f172a 50%, #131c31 100%);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.2);
            border-right: 1px solid rgba(255,255,255,0.04);
        }
        
        /* Karvy Brand Styling */
        .karvy-brand {
            font-family: 'Inter', sans-serif;
            font-weight: 800;
            font-size: 1rem;
            color: #ffffff;
            letter-spacing: -0.02em;
        }
        
        .karvy-subtitle {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 0.625rem;
            color: rgba(148, 163, 184, 0.6);
            letter-spacing: 0.12em;
            text-transform: uppercase;
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
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }
        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 10px 14px;
            margin: 2px 0;
            border-radius: 10px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            color: rgba(148, 163, 184, 0.8);
            position: relative;
        }
        .sidebar-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
        }
        .sidebar-item.active {
            background: linear-gradient(135deg, rgba(59,130,246,0.12) 0%, rgba(99,102,241,0.08) 100%);
            color: #60a5fa;
            border-left: none;
        }
        .sidebar-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 20px;
            background: linear-gradient(180deg, #3b82f6, #8b5cf6);
            border-radius: 0 4px 4px 0;
        }
        .sidebar-item svg {
            opacity: 0.5;
            transition: opacity 0.2s;
        }
        .sidebar-item:hover svg,
        .sidebar-item.active svg {
            opacity: 1;
        }
        
        .sidebar-subitem {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            color: rgba(148, 163, 184, 0.6);
            transition: all 0.2s;
        }
        .sidebar-subitem:hover {
            background: rgba(255, 255, 255, 0.04);
            color: #e2e8f0;
        }
        .sidebar-subitem svg {
            opacity: 0.4;
        }
        .sidebar-subitem:hover svg {
            opacity: 0.8;
        }

        .sidebar-section-label {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: rgba(148, 163, 184, 0.3);
            padding: 16px 16px 6px;
        }

        .vendor-header {
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }
        .stats-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }
        .stats-icon {
            border-radius: 12px;
        }
        .professional-table {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        .table-header {
            background: #f8fafc;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 0.1em;
            color: #94a3b8;
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
        <div class="vendor-sidebar w-64">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center gap-3 px-5 py-5" style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                    <a href="<?php echo url('/contractor/'); ?>" class="flex items-center gap-3 hover:opacity-90 transition-opacity">
                        <div style="width:34px; height:34px; border-radius:10px; background:linear-gradient(135deg,#3b82f6,#6366f1); display:flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(99,102,241,0.3);">
                            <span style="font-weight:900; font-size:13px; color:#fff; letter-spacing:-0.03em;">KT</span>
                        </div>
                        <div>
                            <h1 class="karvy-brand">Karvy Technologies</h1>
                            <p class="karvy-subtitle">Contractor Portal</p>
                        </div>
                    </a>
                </div>
                
                <div style="overflow-y:auto; flex:1;">
                <!-- Navigation -->
                <div class="sidebar-section-label">Main</div>
                <nav class="px-3 space-y-1">
                    <a href="<?php echo url('/contractor/'); ?>" class="sidebar-item">
                        <svg class="w-[18px] h-[18px] mr-3" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 13a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"/></svg>
                        Dashboard
                    </a>
                    
                    <?php if ($vendorPermissions['view_my_sites'] ?? false): ?>
                    <a href="<?php echo url('/contractor/sites/'); ?>" class="sidebar-item">
                        <svg class="w-[18px] h-[18px] mr-3" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        My Sites
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($vendorPermissions['view_site_surveys'] ?? false): ?>
                    <a href="<?php echo url('/contractor/surveys.php'); ?>" class="sidebar-item">
                        <svg class="w-[18px] h-[18px] mr-3" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Site Surveys
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($vendorPermissions['view_installations'] ?? false): ?>
                    <a href="<?php echo url('/contractor/installations.php'); ?>" class="sidebar-item">
                        <svg class="w-[18px] h-[18px] mr-3" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Installations
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($vendorPermissions['view_inventory'] ?? true): ?>
                    </nav>
                    <div class="sidebar-section-label">Supply Chain</div>
                    <nav class="px-3 space-y-1">
                    <!-- Inventory Main Menu with Dropdown -->
                    <div class="relative">
                        <button onclick="toggleInventoryMenu()" class="sidebar-item w-full flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-[18px] h-[18px] mr-3" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                Inventory
                            </div>
                            <svg id="inventory-arrow" class="w-3.5 h-3.5 transition-transform duration-200" style="opacity:0.4;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <!-- Inventory Submenu -->
                        <div id="inventory-submenu" class="hidden ml-7 mt-1 space-y-0.5" style="border-left:1px solid rgba(255,255,255,0.06); padding-left:12px;">
                            <?php if ($vendorPermissions['view_inventory_overview'] ?? false): ?>
                            <a href="<?php echo url('/contractor/inventory/'); ?>" class="sidebar-subitem">
                                <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                Inventory Overview
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($vendorPermissions['view_material_requests'] ?? false): ?>
                            <a href="<?php echo url('/contractor/material-requests-list.php'); ?>" class="sidebar-subitem">
                                <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Material Requests
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($vendorPermissions['view_material_received'] ?? false): ?>
                            <a href="<?php echo url('/contractor/material-received.php'); ?>" class="sidebar-subitem">
                                <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                Material Received
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($vendorPermissions['view_material_dispatches'] ?? false): ?>
                            <a href="<?php echo url('/contractor/material-dispatches.php'); ?>" class="sidebar-subitem">
                                <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                                Material Dispatches
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </nav>
                </div>


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
                            <h1 style="font-size:15px; font-weight:700; color:#0f172a; letter-spacing:-0.01em;"><?php echo $title ?? 'Contractor Portal'; ?></h1>
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
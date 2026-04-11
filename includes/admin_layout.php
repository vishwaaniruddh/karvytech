<?php
require_once __DIR__ . '/../config/auth.php';
// constants.php is already included by auth.php
require_once __DIR__ . '/../includes/audit_integration.php';

// Ensure url() function is available (fallback)
if (!function_exists('url')) {
    function url($path = '') {
        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        $path = ltrim($path, '/');
        return $path ? $baseUrl . '/' . $path : $baseUrl;
    }
}

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);
$currentUser = Auth::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Admin Panel'; ?> - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo url('/assets/css/custom.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('/assets/css/admin.css'); ?>">
    <!-- Fallback CSS for subdirectories -->
    <style>
        .btn {
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #2563eb;
            color: white;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #4b5563;
        }
        .btn-success {
            background-color: #059669;
            color: white;
        }
        .btn-success:hover {
            background-color: #047857;
        }
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }
        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .card-body {
            padding: 1.5rem;
        }
        .form-input, .form-select,.form-textarea {
            display: block;
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background-color: white;
        }
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.25rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th,
        .data-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .data-table th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        .modal-content {
            position: relative;
            background-color: white;
            margin: 2rem auto;
            padding: 0;
            border-radius: 0.5rem;
            max-width: 32rem;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .modal-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
        }
        .modal-body {
            padding: 1.5rem;
        }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            padding: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }
        .modal-close {
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 0.25rem;
        }
        .modal-close:hover {
            color: #374151;
        }
        /* Grid and layout utilities */
        .grid {
            display: grid;
        }
        .grid-cols-1 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        .grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .grid-cols-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .gap-3 {
            gap: 0.75rem;
        }
        .gap-4 {
            gap: 1rem;
        }
        .gap-6 {
            gap: 1.5rem;
        }
        .flex {
            display: flex;
        }
        .flex-1 {
            flex: 1 1 0%;
        }
        .items-center {
            align-items: center;
        }
        .justify-between {
            justify-content: space-between;
        }
        .space-x-2 > * + * {
            margin-left: 0.5rem;
        }
        .mb-4 {
            margin-bottom: 1rem;
        }
        .mb-6 {
            margin-bottom: 1.5rem;
        }
        .text-2xl {
            font-size: 1.5rem;
            line-height: 2rem;
        }
        .font-semibold {
            font-weight: 600;
        }
        .text-gray-900 {
            color: #111827;
        }
        .text-gray-700 {
            color: #374151;
        }
        .text-gray-500 {
            color: #6b7280;
        }
        .text-sm {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }
        .mt-2 {
            margin-top: 0.5rem;
        }
        .overflow-x-auto {
            overflow-x: auto;
        }
        .max-w-4xl {
            max-width: 56rem;
        }
        .md\:grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .md\:col-span-2 {
            grid-column: span 2 / span 2;
        }
        @media (min-width: 768px) {
            .md\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .md\:grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .md\:grid-cols-4 {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
            .md\:col-span-2 {
                grid-column: span 2 / span 2;
            }
        }
        @media (min-width: 1024px) {
            .lg\:grid-cols-4 {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Global fix: Prevent login page decorative elements from appearing */
        .floating-particles,
        .particle {
            display: none !important;
        }
        
        /* Ensure admin content is properly layered */
        .admin-container {
            position: relative;
            z-index: 1;
        }
        
        /* Clean Modern Sidebar Styling */
        .admin-sidebar {
            background: #1f2937;
            border-right: 1px solid #374151;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.2);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 50;
            height: 100vh;
            width: 256px;
            transform: translateX(-100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .admin-sidebar.show {
            transform: translateX(0);
        }
        
        /* Ensure sidebar is visible on mobile when show class is added */
        @media (max-width: 1023px) {
            .admin-sidebar {
                transform: translateX(-100%);
                position: fixed;
                top: 0;
                left: 0;
                z-index: 50;
                height: 100vh;
                width: 256px;
            }
            
            .admin-sidebar.show {
                transform: translateX(0);
            }
            
            /* Ensure main content doesn't have left margin on mobile */
            .main-content {
                margin-left: 0 !important;
            }
        }
        
        /* Collapsed sidebar state */
        .admin-sidebar.collapsed {
            width: 80px;
        }
        
        .admin-sidebar.collapsed .sidebar-text {
            opacity: 0;
            visibility: hidden;
            width: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .sidebar-text {
            transition: all 0.3s ease;
        }
        
        .admin-sidebar.collapsed .datetime-display {
            display: none;
        }
        
        .admin-sidebar.collapsed #clock {
            display: none;
        }
        
        .admin-sidebar.collapsed .nav-link {
            display: none;
        }
        
        .admin-sidebar.collapsed .menu-section-header {
            display: none;
        }
        
        .admin-sidebar.collapsed .sidebar-item {
            justify-content: center;
            padding: 8px;
            margin: 1px 4px;
            position: relative;
            overflow: visible;
        }
        
        /* Simple tooltip implementation */
        .admin-sidebar.collapsed .sidebar-item[data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            left: calc(100% + 15px);
            top: 50%;
            transform: translateY(-50%);
            background: #1f2937;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.875rem;
            white-space: nowrap;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            border: 1px solid #374151;
            pointer-events: none;
        }
        
        .admin-sidebar.collapsed .sidebar-item[data-tooltip]:hover::before {
            content: '';
            position: absolute;
            left: calc(100% + 9px);
            top: 50%;
            transform: translateY(-50%);
            border: 6px solid transparent;
            border-right-color: #1f2937;
            z-index: 9999;
            pointer-events: none;
        }
        
        .admin-sidebar.collapsed button.sidebar-item[data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            left: calc(100% + 15px);
            top: 50%;
            transform: translateY(-50%);
            background: #1f2937;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.875rem;
            white-space: nowrap;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            border: 1px solid #374151;
            pointer-events: none;
        }
        
        .admin-sidebar.collapsed button.sidebar-item[data-tooltip]:hover::before {
            content: '';
            position: absolute;
            left: calc(100% + 9px);
            top: 50%;
            transform: translateY(-50%);
            border: 6px solid transparent;
            border-right-color: #1f2937;
            z-index: 9999;
            pointer-events: none;
        }
        
        .admin-sidebar.collapsed .sidebar-item svg {
            margin-right: 0;
        }
        
        .admin-sidebar.collapsed .sidebar-subitem {
            display: none;
        }
        
        .admin-sidebar.collapsed [id$="-submenu"] {
            display: none;
        }
        
        @media (min-width: 1024px) {
            .admin-sidebar {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 256px;
                transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .main-content.sidebar-collapsed {
                margin-left: 80px;
            }
            
            /* Hide hamburger menu on desktop */
            #toggleSidebar {
                display: none;
            }
        }
        
        /* Ensure hamburger menu is visible on mobile */
        @media (max-width: 1023px) {
            #toggleSidebar {
                display: flex !important;
                align-items: center;
                justify-content: center;
            }
        }
        
        .admin-badge {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 8px 16px;
            margin: 1px 8px;
            border-radius: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
            min-height: 40px;
            color: #e5e7eb !important;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .sidebar-item:hover {
            background-color: #374151;
            color: #f9fafb !important;
            transform: translateX(2px);
        }
        
        .sidebar-item.active {
            background-color: transparent;
            color: #6366f1 !important;
            font-weight: 600;
            border-left: 3px solid #6366f1;
            padding-left: 13px;
        }
        
        .sidebar-item svg {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
            margin-right: 12px;
            color: inherit;
        }
        
        .sidebar-subitem {
            display: flex;
            align-items: center;
            padding: 6px 10px;
            margin: 1px 12px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.8rem;
            transition: all 0.2s;
            min-height: 32px;
            color: #d1d5db !important;
            font-weight: 400;
        }
        
        .sidebar-subitem:hover {
            background-color: #4b5563;
            color: #e5e7eb !important;
            transform: translateX(2px);
        }
        
        .sidebar-subitem.active {
            background-color: transparent;
            color: #6366f1 !important;
            font-weight: 600;
        }
        
        .sidebar-subitem svg {
            flex-shrink: 0;
            width: 16px;
            height: 16px;
            margin-right: 10px;
            color: inherit;
        }
        
        /* Override any conflicting text colors */
        /* .admin-sidebar .sidebar-item span,
        .admin-sidebar .sidebar-subitem {
            color: inherit !important;
        } */
        
        /* Ensure button text is visible */
        .admin-sidebar button.sidebar-item {
            color: #e5e7eb !important;
        }
        
        .admin-sidebar button.sidebar-item:hover {
            color: #f9fafb !important;
        }
        
        /* Large device improvements */
        @media (min-width: 1024px) {
            .sidebar-item {
                margin: 1px 8px;
                padding: 8px 16px;
                font-size: 0.9rem;
            }
            
            [id$="-submenu"] {
                margin: 4px 8px;
                padding: 4px 0;
            }
            
            [id$="-submenu"] .sidebar-subitem {
                margin: 1px 12px;
                padding: 6px 10px;
                font-size: 0.8rem;
            }
        }
        
        /* Mobile sidebar overlay */
        #sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 40;
            transition: opacity 0.3s ease;
        }
        
        #sidebar-overlay.hidden {
            display: none;
        }
        
        /* Responsive styles */
        @media (max-width: 1023px) {
            .admin-sidebar {
                background: #1f2937;
                z-index: 50;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar-item {
                padding: 8px 12px;
                font-size: 0.9rem;
                margin: 1px 6px;
            }
            
            [id$="-submenu"] {
                margin: 3px 6px;
                padding: 3px 0;
            }
            
            [id$="-submenu"] .sidebar-subitem {
                margin: 1px 10px;
                padding: 4px 6px;
                font-size: 0.75rem;
            }
            
            [id$="-submenu"] .sidebar-subitem svg {
                width: 12px;
                height: 12px;
                margin-right: 6px;
            }
            
            /* Make sure overlay doesn't interfere with sidebar clicks */
            #sidebar-overlay {
                z-index: 40;
            }
        }
        
        /* Card responsive styles */
        .card {
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 640px) {
            .card-body {
                padding: 1rem;
            }
            
            .data-table th,
            .data-table td {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }
            
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
            }
            
            .btn-sm {
                padding: 0.375rem 0.5rem;
                font-size: 0.7rem;
            }
            
            /* Hide page header title on mobile */
            .header-page-title {
                display: none;
            }
        }
        
        /* Modal responsive styles */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                top: 2rem;
                padding: 1rem;
            }
            
            .modal-content-large {
                width: 98%;
                top: 1rem;
                max-height: 95vh;
            }
            
            .modal-body-scrollable {
                padding: 1rem;
                max-height: calc(95vh - 120px);
            }
        }
        .btn {
            font-weight: 500;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            padding: 0.625rem 1.25rem;
            text-align: center;
            transition: all 0.2s;
        }
        .btn-primary {
            color: white;
            background-color: #2563eb;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        .btn-secondary {
            color: #6b7280;
            background-color: white;
            border: 1px solid #e5e7eb;
        }
        .btn-secondary:hover {
            background-color: #f9fafb;
            color: #374151;
        }
        .btn-danger {
            color: white;
            background-color: #dc2626;
        }
        .btn-danger:hover {
            background-color: #b91c1c;
        }
        .btn-info {
            color: white;
            background-color: #0ea5e9;
        }
        .btn-info:hover {
            background-color: #0284c7;
        }
        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
        }
        .card {
            background-color: white;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
        }
        .card-body {
            padding: 1.5rem;
        }
        .data-table {
            width: 100%;
            font-size: 0.875rem;
            text-align: left;
            color: #6b7280;
        }
        .data-table thead {
            font-size: 0.75rem;
            color: #374151;
            text-transform: uppercase;
            background-color: #f9fafb;
        }
        .data-table th {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }
        .data-table td {
            padding: 1rem 1.5rem;
            white-space: nowrap;
            /* white-space: nowrap; */
        }
        .data-table tbody tr {
            background-color: white;
            border-bottom: 1px solid #e5e7eb;
        }
        .data-table tbody tr:hover {
            background-color: #f9fafb;
        }
        
        /* Responsive table */
        @media (max-width: 768px) {
            .data-table {
                font-size: 0.8rem;
            }
            .data-table th,
            .data-table td {
                padding: 0.5rem 0.75rem;
            }
            .data-table td {
                white-space: normal;
                word-break: break-word;
                white-space: nowrap;
            }
        }
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 50;
            display: none;
        }
        .modal.show {
            display: block;
        }
        .modal-content {
            position: relative;
            /* top: 5rem; */
            margin: 0 auto;
            padding: 1.25rem;
            border: 1px solid #e5e7eb;
            width: 91.666667%;
            max-width: 42rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-radius: 0.375rem;
            background-color: white;
        }
        .modal-content-large {
            position: relative;
            top: 2rem;
            margin: 0 auto;
            border: 1px solid #e5e7eb;
            width: 95%;
            max-width: 900px;
            max-height: 90vh;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-radius: 0.375rem;
            background-color: white;
            display: flex;
            flex-direction: column;
        }
        .modal-header-fixed {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background-color: white;
            border-radius: 0.375rem 0.375rem 0 0;
        }
        .modal-body-scrollable {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            max-height: calc(90vh - 140px);
        }
        .modal-footer-fixed {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            gap: 0.5rem;
            border-top: 1px solid #e5e7eb;
            background-color: white;
            border-radius: 0 0 0.375rem 0.375rem;
        }
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .form-section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        /* Dynamic Menu Styles */
        .menu-group {
            margin-bottom: 0.25rem;
        }
        
        .menu-group-title {
            color: #9ca3af;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.5rem 1rem;
            margin-top: 1rem;
        }
        
        .submenu {
            background-color: #1f2937;
            border-radius: 0.375rem;
            margin: 0.25rem 0.5rem;
            overflow: hidden;
        }
        
        .submenu .sidebar-item {
            padding-left: 2.5rem;
            margin: 0;
            border-radius: 0;
        }
        
        .submenu .sidebar-item:hover {
            background-color: #374151;
        }
        
        .submenu {
            overflow: hidden;
            transition: max-height 0.3s ease-in-out;
            padding-left: 1rem;
        }
        
        .submenu-open {
            max-height: 500px;
        }
        
        .submenu-closed {
            max-height: 0;
        }
        
        .submenu-arrow {
            transition: transform 0.3s ease-in-out;
        }
        
        .submenu-open .submenu-arrow {
            transform: rotate(90deg);
        }
        
        /* Improved submenu transitions */
        [id^="submenu-"], #inventory-submenu, #admin-submenu {
            transition: all 0.3s ease-in-out;
            overflow: hidden;
        }
        
        [id^="submenu-"].hidden, #inventory-submenu.hidden, #admin-submenu.hidden {
            opacity: 0;
            max-height: 0;
            margin-top: 0;
            margin-bottom: 0;
            padding-top: 0;
            padding-bottom: 0;
        }
        
        [id^="submenu-"]:not(.hidden), #inventory-submenu:not(.hidden), #admin-submenu:not(.hidden) {
            opacity: 1;
            max-height: 500px;
        }
        
        /* Arrow transitions */
        [id^="arrow-"], #inventory-arrow, #admin-arrow {
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            color: #9ca3af;
        }
        
        .sidebar-item:hover [id^="arrow-"], 
        .sidebar-item:hover #inventory-arrow, 
        .sidebar-item:hover #admin-arrow {
            color: #6b7280;
        }
        
        /* Dark sidebar styles already defined above */
        
        /* Nested menu items */
        .ml-4 {
            margin-left: 1rem;
        }
        
        .ml-8 {
            margin-left: 2rem;
        }
        
        .ml-12 {
            margin-left: 3rem;
        }
        
        /* Submenu container styling */
        [id$="-submenu"] {
            background: #374151;
            border-radius: 8px;
            margin: 4px 8px;
            padding: 4px 0;
            border-left: 3px solid #6366f1;
        }
        
        [id$="-submenu"] .sidebar-subitem {
            margin: 1px 12px;
            padding: 6px 10px;
            font-size: 0.8rem;
        }
        
        [id$="-submenu"] .sidebar-subitem svg {
            width: 14px;
            height: 14px;
            margin-right: 8px;
        }
        
        /* Menu section headers */
        .menu-section-header {
            color: #9ca3af !important;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 16px 6px 16px;
            margin-top: 16px;
        }
        
        .menu-section-header:first-child {
            margin-top: 6px;
        }
        .form-input, .form-select,.form-textarea,.search-input {
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
            color: #111827;
            font-size: 0.875rem;
            border-radius: 0.5rem;
            display: block;
            width: 100%;
            padding: 0.625rem;
        }
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #166534;
        }
        .badge-danger {
            background-color: #fecaca;
            color: #991b1b;
        }
        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .badge-secondary {
            background-color: #f3f4f6;
            color: #374151;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        /* User dropdown styles */
        #user-dropdown {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        #user-dropdown.hidden {
            display: none !important;
        }
        
        #user-dropdown:not(.hidden) {
            display: block !important;adow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        /* Smooth scrolling for sidebar */
        .admin-sidebar nav {
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
        }
        
        .admin-sidebar nav::-webkit-scrollbar {
            width: 6px;
        }
        
        .admin-sidebar nav::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .admin-sidebar nav::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 3px;
        }
        
        .admin-sidebar nav::-webkit-scrollbar-thumb:hover {
            background-color: rgba(156, 163, 175, 0.7);
        }
        
        /* Karvy Brand Styling */
        .karvy-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.2rem;
            color: #ffffff; /* Fallback color */
            background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 0.3px;
            line-height: 1.2;
            margin-bottom: 2px;
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
            font-weight: 400;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            font-size: 0.65rem;
            color: #9ca3af;
        }
        
        .admin-sidebar.collapsed .karvy-brand,
        .admin-sidebar.collapsed .karvy-subtitle {
            display: none;
        }

        /* Real-time datetime display */
        .datetime-display {
            background: #374151;
            border: 1px solid #4b5563;
            border-radius: 8px;
            padding: 8px;
            margin: 8px 6px;
            text-align: center;
        }
        
        .datetime-display .date {
            font-size: 0.7rem;
            font-weight: 600;
            color: #f9fafb;
            margin-bottom: 2px;
        }
        
        .datetime-display .time {
            font-size: 0.65rem;
            color: #d1d5db;
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
            font-weight: 500;
        }
        
        /* Responsive datetime display */
        @media (max-width: 1023px) {
            .datetime-display {
                margin: 6px 4px;
                padding: 6px;
            }
            
            .datetime-display .date {
                font-size: 0.65rem;
            }
            
            .datetime-display .time {
                font-size: 0.6rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen bg-gray-50">

    <!-- Sidebar -->
    <div class="admin-sidebar w-64 shadow-lg">
        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-600">
                <a href="<?php echo url('/admin/dashboard.php'); ?>" class="flex items-center hover:opacity-80 transition-opacity">
                    <div class="sidebar-text">
                        <h1 class="text-lg font-bold text-white karvy-brand">Karvy Technologies</h1>
                        <p class="text-xs text-gray-300 karvy-subtitle">Pvt Ltd</p>
                    </div>
                </a>
                <!-- Hamburger menu for large devices -->
                <button id="sidebarToggle" class="hidden lg:block p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Real-time Date/Time Display -->
              <a class="nav-link" href="#" style="color:white;text-align: center;margin: 5px;">
                    <span class="menu-title" id="clock" class="clock"></span>
                </a>

            <!-- <div class="datetime-display">
                <div class="date" id="current-date"></div>
                <div class="time" id="current-time"></div>
            </div> -->

            <!-- Navigation -->
            <nav class="flex-1 px-2 py-2 space-y-1 overflow-y-auto">
                <?php 
                try {
                    require_once __DIR__ . '/dynamic_sidebar.php';
                    renderDynamicSidebar($currentUser); 
                } catch (Exception $e) {
                    echo '<div class="p-4 text-red-500 text-sm">';
                    echo 'Menu system error: ' . htmlspecialchars($e->getMessage());
                    echo '<br><small>Please contact administrator to setup menu permissions.</small>';
                    echo '</div>';
                }
                ?>
            </nav>


        </div>
    </div>

        <!-- Main Content -->
        <div class="main-content flex-1 flex flex-col overflow-hidden bg-gray-50">
            <!-- Top Header -->
            <header class="admin-header">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center">
                        <button id="toggleSidebar" class="lg:hidden p-2 rounded-md text-gray-600 hover:text-gray-800 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 mr-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <div class="header-page-title">
                            <h1 class="text-xl font-semibold text-gray-900"><?php echo $title ?? 'Admin Panel'; ?></h1>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Environment Indicator -->
                        <?php 
                        // Ensure constants are loaded
                        if (!function_exists('getEnvironment')) {
                            require_once __DIR__ . '/../config/constants.php';
                        }
                        
                        $env =  function_exists('getEnvironment') ? getEnvironment() : (defined('APP_ENV') ? APP_ENV : 'unknown');
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
                                <div class="admin-badge w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-xs">
                                    <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                                </div>
                                <span class="ml-2 text-gray-700 font-medium"><?php echo htmlspecialchars(ucwords($currentUser['username'])); ?></span>
                                <svg class="ml-2 w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                            <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-2 border border-gray-200" style="z-index: 9999;">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($currentUser['username']); ?></p>
                                    <p class="text-xs text-gray-500">Administrator</p>
                                </div>
                                <a href="<?php echo url('/admin/profile.php'); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                    My Profile
                                </a>
                                <a href="<?php echo url('/admin/users/'); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                                    </svg>
                                    User Management
                                </a>
                                <a href="<?php echo url('/admin/reports/'); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                                    </svg>
                                    Reports & Analytics
                                </a>
                                <a href="<?php echo url('/admin/help.php'); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4 mr-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                                    </svg>
                                    Help & Documentation
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
                    <?php if (isset($content)) echo $content; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-40 lg:hidden hidden"></div>

    <!-- Scripts -->
    <script src="<?php echo url('/assets/js/app.js'); ?>"></script>
    <script src="<?php echo url('/assets/js/admin.js'); ?>"></script>
    <script src="<?php echo url('/assets/js/masters-api.js'); ?>"></script>
    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.admin-sidebar');
            const mainContent = document.querySelector('.main-content');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileSidebarToggle = document.getElementById('toggleSidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            console.log('Sidebar elements found:', {
                sidebar: !!sidebar,
                mobileSidebarToggle: !!mobileSidebarToggle,
                overlay: !!overlay
            });
            
            // Desktop sidebar toggle (collapse/expand)
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    console.log('Desktop sidebar toggle clicked');
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('sidebar-collapsed');
                    
                    // Store preference in localStorage
                    const isCollapsed = sidebar.classList.contains('collapsed');
                    localStorage.setItem('sidebarCollapsed', isCollapsed);
                });
            }
            
            // Mobile sidebar toggle is handled by admin.js
            // Removed duplicate event listener to prevent conflicts
            
            // Restore sidebar state from localStorage
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === 'true') {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('sidebar-collapsed');
            }
            
            // Enhanced tooltip system for collapsed sidebar
            function initTooltips() {
                const sidebarItems = document.querySelectorAll('.admin-sidebar .sidebar-item[data-tooltip]');
                
                sidebarItems.forEach(item => {
                    let tooltip = null;
                    
                    item.addEventListener('mouseenter', function() {
                        if (!sidebar.classList.contains('collapsed')) return;
                        
                        const tooltipText = this.getAttribute('data-tooltip');
                        if (!tooltipText) return;
                        
                        console.log('Showing tooltip:', tooltipText);
                        
                        // Create tooltip element
                        tooltip = document.createElement('div');
                        tooltip.className = 'sidebar-tooltip';
                        tooltip.textContent = tooltipText;
                        tooltip.style.cssText = `
                            position: fixed;
                            background: #1f2937;
                            color: white;
                            padding: 8px 12px;
                            border-radius: 6px;
                            font-size: 0.875rem;
                            white-space: nowrap;
                            z-index: 9999;
                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
                            border: 1px solid #374151;
                            pointer-events: none;
                            opacity: 0;
                            transition: opacity 0.2s ease;
                        `;
                        
                        document.body.appendChild(tooltip);
                        
                        // Position tooltip
                        const rect = this.getBoundingClientRect();
                        tooltip.style.left = (rect.right + 15) + 'px';
                        tooltip.style.top = (rect.top + rect.height / 2 - tooltip.offsetHeight / 2) + 'px';
                        
                        // Show tooltip
                        setTimeout(() => {
                            if (tooltip) tooltip.style.opacity = '1';
                        }, 100);
                    });
                    
                    item.addEventListener('mouseleave', function() {
                        if (tooltip) {
                            tooltip.style.opacity = '0';
                            setTimeout(() => {
                                if (tooltip && tooltip.parentNode) {
                                    tooltip.parentNode.removeChild(tooltip);
                                }
                                tooltip = null;
                            }, 200);
                        }
                    });
                });
            }
            
            // Initialize tooltips
            initTooltips();
            
            // Overlay click handler is managed by admin.js
        });

        // User dropdown toggle function
        function toggleUserDropdown() {
            const dropdown = document.getElementById('user-dropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
                console.log('Admin dropdown toggled, hidden class:', dropdown.classList.contains('hidden'));
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
        
        // Toggle inventory submenu - allow multiple submenus to stay open
        function toggleInventoryMenu() {
            const submenu = document.getElementById('inventory-submenu');
            const arrow = document.getElementById('inventory-arrow');
            
            if (!submenu) return;
            
            const isCurrentlyHidden = submenu.classList.contains('hidden');
            
            // Simple toggle - no accordion behavior
            if (isCurrentlyHidden) {
                submenu.classList.remove('hidden');
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            } else {
                submenu.classList.add('hidden');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        }
        
        // Toggle admin submenu - allow multiple submenus to stay open
        function toggleAdminMenu() {
            const submenu = document.getElementById('admin-submenu');
            const arrow = document.getElementById('admin-arrow');
            
            if (!submenu) return;
            
            const isCurrentlyHidden = submenu.classList.contains('hidden');
            
            // Simple toggle - no accordion behavior
            if (isCurrentlyHidden) {
                submenu.classList.remove('hidden');
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            } else {
                submenu.classList.add('hidden');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        }
        
        // Auto-expand menus based on current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            
            // Auto-expand inventory menu if on inventory page
            const inventoryPaths = ['/admin/inventory/', '/admin/requests/', '/admin/inventory/inwards/', '/admin/inventory/dispatches/'];
            if (inventoryPaths.some(path => currentPath.includes(path))) {
                const submenu = document.getElementById('inventory-submenu');
                const arrow = document.getElementById('inventory-arrow');
                if (submenu && arrow) {
                    submenu.classList.remove('hidden');
                    arrow.style.transform = 'rotate(180deg)';
                }
            }
            
            // Auto-expand admin menu if on admin pages
            const adminPaths = ['/admin/users/', '/admin/vendors/', '/admin/masters/', '/admin/boq/'];
            if (adminPaths.some(path => currentPath.includes(path))) {
                const submenu = document.getElementById('admin-submenu');
                const arrow = document.getElementById('admin-arrow');
                if (submenu && arrow) {
                    submenu.classList.remove('hidden');
                    arrow.style.transform = 'rotate(180deg)';
                }
            }
            
            // For dynamic database-driven menus - auto-expand parent menus with active children
            const activeLinks = document.querySelectorAll('.sidebar-item.active, .sidebar-subitem.active');
            activeLinks.forEach(link => {
                // Find parent submenu containers
                let parent = link.closest('[id^="submenu-"]');
                while (parent) {
                    const menuId = parent.id.replace('submenu-', '');
                    const arrow = document.getElementById('arrow-' + menuId);
                    
                    parent.classList.remove('hidden');
                    if (arrow) arrow.style.transform = 'rotate(180deg)';
                    
                    // Look for parent of parent
                    parent = parent.parentElement.closest('[id^="submenu-"]');
                }
            });
        });
        
        // Dynamic menu functionality for database-driven menus - allow multiple open submenus
        function toggleSubmenu(submenuId) {
            const submenu = document.getElementById(submenuId);
            if (!submenu) return;
            
            // Extract the menu ID from submenuId (format: 'submenu-X')
            const menuId = submenuId.replace('submenu-', '');
            const arrow = document.getElementById('arrow-' + menuId);
            
            const isCurrentlyHidden = submenu.classList.contains('hidden');
            
            // Simple toggle - no accordion behavior, allow multiple submenus to be open
            if (isCurrentlyHidden) {
                submenu.classList.remove('hidden');
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            } else {
                submenu.classList.add('hidden');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        }

        // Mobile navigation handling is in admin.js
        
        // Real-time date/time update
        function updateDateTime() {
            const now = new Date();
            
            // Format date as DD MMM YYYY (more compact)
            const dateOptions = { 
                day: '2-digit', 
                month: 'short', 
                year: 'numeric' 
            };
            const formattedDate = now.toLocaleDateString('en-US', dateOptions);
            
            // Format time as HH:MM:SS (24-hour format with seconds)
            const timeOptions = { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            };
            const formattedTime = now.toLocaleTimeString('en-US', timeOptions);
            
            // Update the display
            const dateElement = document.getElementById('current-date');
            const timeElement = document.getElementById('current-time');
            
            if (dateElement) dateElement.textContent = formattedDate;
            if (timeElement) timeElement.textContent = formattedTime;
        }
        
        // Update datetime immediately and then every second
        document.addEventListener('DOMContentLoaded', function() {
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });
    </script>
    
    <!-- Common JavaScript Functions -->
    <script>
        // Modal functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                // Remove hidden class if present
                modal.classList.remove('hidden');
                // Add show class
                modal.classList.add('show');
                // IMPORTANT: Remove inline style to let CSS class take over
                modal.style.removeProperty('display');
                modal.style.removeProperty('align-items');
                modal.style.removeProperty('justify-content');
                document.body.style.overflow = 'hidden';
            }
        }
        
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                // Remove show class
                modal.classList.remove('show');
                // Add hidden class
                modal.classList.add('hidden');
                // Don't set inline style - let CSS handle it
                document.body.style.overflow = 'auto';
            }
        }
        
        // Alert function
        function showAlert(message, type = 'info') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 0.5rem;
                color: white;
                font-weight: 500;
                z-index: 9999;
                max-width: 400px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            `;
            
            switch(type) {
                case 'success':
                    alertDiv.style.backgroundColor = '#059669';
                    break;
                case 'error':
                    alertDiv.style.backgroundColor = '#dc2626';
                    break;
                case 'warning':
                    alertDiv.style.backgroundColor = '#d97706';
                    break;
                default:
                    alertDiv.style.backgroundColor = '#2563eb';
            }
            
            alertDiv.textContent = message;
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 5000);
        }
        
        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('show');
                e.target.classList.add('hidden');
                e.target.style.removeProperty('display');
                document.body.style.overflow = 'auto';
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    if (modal.classList.contains('show')) {
                        modal.classList.remove('show');
                        modal.classList.add('hidden');
                        modal.style.removeProperty('display');
                        document.body.style.overflow = 'auto';
                    }
                });
            }
        });
    function updateClock() {
    var now = new Date();
    var date = now.toDateString();
    var time = now.toLocaleTimeString();

    var clockElement = document.getElementById('clock');
    clockElement.textContent = date + ' ' + time;
  }

  // Update the clock every second
  setInterval(updateClock, 1000);

  // Initial call to display the clock immediately
  updateClock();

    </script>
    
</body>
</html>
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

// Require authentication (but allow granular module permissions to control specific page access)
Auth::requireAuth();
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
        
        /* ── Premium Command Center Sidebar ── */
        .admin-sidebar {
            background: linear-gradient(165deg, #1e1b4b 0%, #0f172a 45%, #020617 100%);
            border-right: 1px solid rgba(99, 102, 241, 0.1);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.4), inset -1px 0 0 rgba(255, 255, 255, 0.02);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 50;
            height: 100vh;
            width: 270px;
            transform: translateX(-100%);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }
        .admin-sidebar::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.015'%3E%3Cpath d='M0 0h1v1H0z'/%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }
        .admin-sidebar > * { position: relative; z-index: 1; }
        
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
        
        .admin-sidebar.collapsed .sidebar-item span,
        .admin-sidebar.collapsed .menu-section-header span,
        .admin-sidebar.collapsed .karvy-brand-container {
            opacity: 0;
            visibility: hidden;
            width: 0;
            height: 0;
            overflow: hidden;
            display: none;
            transition: all 0.3s ease;
        }
        
        .sidebar-item span, .menu-section-header span {
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
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }
        
        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            margin: 2px 10px;
            border-radius: 10px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            min-height: 42px;
            color: #7c8db5 !important;
            font-weight: 500;
            font-size: 0.875rem;
            letter-spacing: 0.01em;
            position: relative;
            border: 1px solid transparent;
        }
        
        .sidebar-item:hover {
            background: rgba(99, 102, 241, 0.08);
            color: #e2e8f0 !important;
            border-color: rgba(99, 102, 241, 0.12);
        }
        
        .sidebar-item.active {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.14) 0%, rgba(59, 130, 246, 0.08) 100%);
            color: #818cf8 !important;
            font-weight: 600;
            border-color: rgba(99, 102, 241, 0.18);
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.06);
        }

        .sidebar-item.active::before {
            content: '';
            position: absolute;
            left: -10px;
            top: 20%;
            height: 60%;
            width: 3px;
            background: linear-gradient(180deg, #818cf8, #6366f1);
            border-radius: 0 3px 3px 0;
            box-shadow: 0 0 12px rgba(129, 140, 248, 0.5), 0 0 4px rgba(129, 140, 248, 0.3);
        }
        
        .sidebar-item svg {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
            margin-right: 14px;
            color: inherit;
            transition: all 0.3s ease;
        }
        
        .sidebar-subitem {
            display: flex;
            align-items: center;
            padding: 8px 14px;
            margin: 1px 12px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.8rem;
            transition: all 0.2s ease;
            min-height: 36px;
            color: #5a6a8a !important;
            font-weight: 500;
            position: relative;
            letter-spacing: 0.01em;
        }
        
        .sidebar-subitem:hover {
            color: #c7d2fe !important;
            background: rgba(99, 102, 241, 0.05);
        }
        
        .sidebar-subitem.active {
            color: #a5b4fc !important;
            font-weight: 600;
        }

        .sidebar-subitem.active::after {
            content: '';
            position: absolute;
            left: -14px;
            top: 50%;
            width: 5px;
            height: 5px;
            background: #818cf8;
            border-radius: 50%;
            transform: translateY(-50%);
            box-shadow: 0 0 8px rgba(129, 140, 248, 0.5);
        }
        
        .sidebar-subitem svg {
            flex-shrink: 0;
            width: 16px;
            height: 16px;
            margin-right: 12px;
            color: inherit;
            opacity: 0.7;
        }

        /* Hierarchy Indentation & Lines */
        .submenu-wrapper {
            position: relative;
            margin-left: 28px !important;
            padding-left: 0;
            border-left: 1px solid rgba(99, 102, 241, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .submenu-wrapper[data-level="2"] {
            margin-left: 24px !important;
        }

        .sidebar-subitem[data-level="1"] { padding-left: 16px; }
        .sidebar-subitem[data-level="2"] { padding-left: 20px; }
        .sidebar-subitem[data-level="3"] { padding-left: 24px; }

        .submenu-arrow {
            color: #4a5578;
            transition: transform 0.3s ease;
        }

        .sidebar-item:hover .submenu-arrow {
            color: #818cf8;
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
                margin: 1rem auto;
                max-height: 95vh;
            }
            
            .modal-content-large {
                width: 98%;
                margin: 1rem auto;
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
            align-items: center;
            justify-content: center;
        }
        .modal.show {
            display: flex;
        }
        .modal-content {
            position: relative;
            margin: 2rem auto;
            padding: 1.25rem;
            border: 1px solid #e5e7eb;
            width: 91.666667%;
            max-width: 42rem;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-radius: 0.375rem;
            background-color: white;
        }
        .modal-content-large {
            position: relative;
            margin: 2rem auto;
            border: 1px solid #e5e7eb;
            width: 95%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
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
            color: #475569;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
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
            max-height: 2000px; /* Increased to fix cutoff issue */
        }
        
        .submenu-closed {
            max-height: 0;
        }
        
        .submenu-arrow {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .submenu-open .submenu-arrow {
            transform: rotate(90deg);
        }
        
        /* Improved submenu transitions */
        [id^="submenu-"], #inventory-submenu, #admin-submenu {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
            max-height: 2000px; /* Increased to fix cutoff issue */
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
            /* Now handled by .submenu-wrapper */
            max-height: 1000px;
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
            color: #475569 !important;
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            padding: 16px 18px 8px 18px;
            margin-top: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .menu-section-header::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, rgba(99,102,241,0.12), transparent);
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
            background: linear-gradient(135deg, rgba(255,255,255,0.92) 0%, rgba(248,250,252,0.95) 100%);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226,232,240,0.6);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 4px 12px rgba(0, 0, 0, 0.02);
            position: relative;
        }
        .admin-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #6366f1, #8b5cf6, #06b6d4, transparent 80%);
            opacity: 0.4;
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
            scrollbar-color: rgba(99, 102, 241, 0.15) transparent;
        }
        
        .admin-sidebar nav::-webkit-scrollbar {
            width: 4px;
        }
        
        .admin-sidebar nav::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .admin-sidebar nav::-webkit-scrollbar-thumb {
            background-color: rgba(99, 102, 241, 0.18);
            border-radius: 20px;
        }
        
        .admin-sidebar nav::-webkit-scrollbar-thumb:hover {
            background-color: rgba(99, 102, 241, 0.35);
        }
        
        /* Karvy Brand Styling */
        .karvy-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.15rem;
            color: #ffffff;
            background: linear-gradient(135deg, #ffffff 0%, #c7d2fe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 0.2px;
            line-height: 1.2;
            margin-bottom: 2px;
        }
        
        @supports not (-webkit-background-clip: text) {
            .karvy-brand {
                color: #ffffff;
                text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            }
        }
        
        .karvy-subtitle {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-size: 0.55rem;
            color: #6366f1;
        }
        
        .admin-sidebar.collapsed .karvy-brand,
        .admin-sidebar.collapsed .karvy-subtitle {
            display: none;
        }

        /* Real-time datetime display */
        .datetime-display {
            background: rgba(99, 102, 241, 0.06);
            border: 1px solid rgba(99, 102, 241, 0.1);
            border-radius: 10px;
            padding: 8px;
            margin: 8px 10px;
            text-align: center;
        }
        
        .datetime-display .date {
            font-size: 0.7rem;
            font-weight: 600;
            color: #e2e8f0;
            margin-bottom: 2px;
        }
        
        .datetime-display .time {
            font-size: 0.65rem;
            color: #818cf8;
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
            font-weight: 600;
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

        /* ── Premium Layout Background ── */
        body {
            background-color: #f1f5f9 !important;
            background-image:
                radial-gradient(ellipse at 0% 0%, rgba(99,102,241,0.07) 0%, transparent 50%),
                radial-gradient(ellipse at 100% 0%, rgba(139,92,246,0.06) 0%, transparent 50%),
                radial-gradient(ellipse at 60% 100%, rgba(6,182,212,0.06) 0%, transparent 50%) !important;
            background-attachment: fixed !important;
        }
        .main-content-area {
            background-image:
                radial-gradient(at 20% 10%, rgba(99,102,241,0.04) 0%, transparent 40%),
                radial-gradient(at 80% 90%, rgba(6,182,212,0.04) 0%, transparent 40%);
            min-height: 100%;
        }
    </style>
</head>
<body>
    <div class="flex h-screen">

    <!-- Sidebar -->
    <div class="admin-sidebar w-64 shadow-lg">
        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div style="padding: 20px 20px 16px; border-bottom: 1px solid rgba(99,102,241,0.08); background: linear-gradient(180deg, rgba(99,102,241,0.04) 0%, transparent 100%);">
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <a href="<?php echo url('/admin/dashboard.php'); ?>" class="flex items-center gap-3 hover:opacity-90 transition-all active:scale-95" style="text-decoration:none;">
                        <div style="width:40px; height:40px; border-radius:12px; background:linear-gradient(135deg,#6366f1,#4f46e5); display:flex; align-items:center; justify-content:center; box-shadow: 0 4px 15px rgba(99,102,241,0.35);">
                            <svg style="width:22px; height:22px; color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="karvy-brand-container">
                            <h1 class="karvy-brand" style="margin:0;">Karvy</h1>
                            <p class="karvy-subtitle" style="margin:0;">Technologies</p>
                        </div>
                    </a>
                    <button id="sidebarToggle" class="hidden lg:flex" style="padding:8px; border-radius:10px; color:#475569; background:transparent; border:1px solid transparent; transition:all 0.2s; cursor:pointer;" onmouseover="this.style.background='rgba(99,102,241,0.08)';this.style.borderColor='rgba(99,102,241,0.12)';this.style.color='#818cf8';" onmouseout="this.style.background='transparent';this.style.borderColor='transparent';this.style.color='#475569';">
                        <svg style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
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
        <div class="main-content flex-1 flex flex-col overflow-hidden">
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
                            <!-- Session Timer Removed -->
                            
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
                                    <!-- Session Extension Removed -->
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
            <main class="flex-1 overflow-y-auto p-6">
                <div class="w-full main-content-area">
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
        let confirmPromise = null;

        function showConfirm(title, message, options = {}) {
            const {
                confirmText = 'Yes, Proceed',
                cancelText = 'Cancel',
                confirmType = 'primary', // primary, danger, success
                hideCancel = false
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

            // Handle alert-only mode (hide cancel button)
            cancelBtn.classList.toggle('hidden', hideCancel);

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
        
        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                closeModal(e.target.id);
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modals = document.querySelectorAll('.modal, #global-confirm-modal');
                modals.forEach(modal => {
                    if (!modal.classList.contains('hidden')) {
                        if (modal.id === 'global-confirm-modal') {
                            modal.querySelector('#confirm-cancel').click();
                        } else {
                            closeModal(modal.id);
                        }
                    }
                });
            }
        });

        function updateClock() {
            var now = new Date();
            var date = now.toDateString();
            var time = now.toLocaleTimeString();

            var clockElement = document.getElementById('clock');
            if (clockElement) clockElement.textContent = date + ' ' + time;
        }

        setInterval(updateClock, 1000);
        updateClock();

        // Session Management System
        class SessionManager {
            constructor() {
                this.sessionTimeout = <?php echo SESSION_TIMEOUT; ?> * 1000; // Convert to milliseconds
                this.warningTime = 5 * 60 * 1000; // 5 minutes warning
                this.renewalInterval = 10 * 60 * 1000; // Renew every 10 minutes
                this.lastActivity = Date.now();
                this.sessionStart = Date.now();
                this.warningShown = false;
                this.timerElement = document.getElementById('session-time');
                this.timerContainer = document.getElementById('session-timer');
                
                this.init();
            }
            
            init() {
                // Show timer
                if (this.timerContainer) {
                    this.timerContainer.classList.remove('hidden');
                }
                
                // Track user activity
                this.trackActivity();
                
                // Start timer update
                this.updateTimer();
                setInterval(() => this.updateTimer(), 1000);
                
                // Auto-renewal for active users
                setInterval(() => this.autoRenew(), this.renewalInterval);
                
                // Check session status periodically
                setInterval(() => this.checkSession(), 30000); // Every 30 seconds
            }
            
            trackActivity() {
                const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
                
                events.forEach(event => {
                    document.addEventListener(event, () => {
                        this.lastActivity = Date.now();
                        this.warningShown = false;
                    }, true);
                });
            }
            
            updateTimer() {
                const now = Date.now();
                const elapsed = now - this.sessionStart;
                const remaining = this.sessionTimeout - elapsed;
                
                if (remaining <= 0) {
                    this.handleExpiration();
                    return;
                }
                
                // Show warning if less than warning time remaining
                if (remaining <= this.warningTime && !this.warningShown) {
                    this.showWarning(remaining);
                }
                
                // Update timer display
                const minutes = Math.floor(remaining / 60000);
                const seconds = Math.floor((remaining % 60000) / 1000);
                
                if (this.timerElement) {
                    this.timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                    
                    // Change color based on time remaining
                    const container = this.timerContainer;
                    if (remaining <= this.warningTime) {
                        container.className = container.className.replace(/bg-\w+-\d+/, 'bg-red-100');
                        container.className = container.className.replace(/text-\w+-\d+/, 'text-red-600');
                    } else if (remaining <= this.warningTime * 2) {
                        container.className = container.className.replace(/bg-\w+-\d+/, 'bg-yellow-100');
                        container.className = container.className.replace(/text-\w+-\d+/, 'text-yellow-600');
                    } else {
                        container.className = container.className.replace(/bg-\w+-\d+/, 'bg-gray-100');
                        container.className = container.className.replace(/text-\w+-\d+/, 'text-gray-600');
                    }
                }
            }
            
            showWarning(remaining) {
                this.warningShown = true;
                const minutes = Math.floor(remaining / 60000);
                
                showToast(
                    `Your session will expire in ${minutes} minute(s). Click anywhere to extend your session.`,
                    'warning',
                    10000
                );
            }
            
            autoRenew() {
                const timeSinceActivity = Date.now() - this.lastActivity;
                
                // Only renew if user has been active in the last 10 minutes
                if (timeSinceActivity < this.renewalInterval) {
                    this.renewSession();
                }
            }
            
            renewSession() {
                fetch('<?php echo url("/api/renew-session.php"); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.sessionStart = Date.now();
                        this.warningShown = false;
                        console.log('Session renewed successfully');
                    } else {
                        console.warn('Session renewal failed:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Session renewal error:', error);
                });
            }
            
            checkSession() {
                fetch('<?php echo url("/api/check-session.php"); ?>', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.valid) {
                        this.handleExpiration();
                    }
                })
                .catch(error => {
                    console.error('Session check error:', error);
                });
            }
            
            handleExpiration() {
                showToast('Your session has expired. You will be redirected to login.', 'error', 5000);
                
                setTimeout(() => {
                    window.location.href = '<?php echo url("/auth/login.php"); ?>';
                }, 2000);
            }
        }
        
        // Initialize session manager
        const sessionManager = new SessionManager();
        
        // Manual session extension button (optional)
        function extendSession() {
            sessionManager.renewSession();
            showToast('Session extended successfully!', 'success');
        }
    </script>
    
</body>
</html>
<?php
require_once __DIR__ . '/../models/Menu.php';

// Ensure url() function is available (fallback)
if (!function_exists('url')) {
    function url($path = '') {
        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        $path = ltrim($path, '/');
        return $path ? $baseUrl . '/' . $path : $baseUrl;
    }
}

function renderDynamicSidebar($currentUser) {
    $menuModel = new Menu();
    $menuItems = $menuModel->getMenuForUser($currentUser['id'], $currentUser['role']);
    
    $currentUrl = $_SERVER['REQUEST_URI'];
    
    // Check if user has any menu permissions
    if (empty($menuItems)) {
        echo '<div class="p-4 text-center text-gray-500">';
        echo '<svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">';
        echo '<path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>';
        echo '</svg>';
        echo '<p class="text-sm">No menu access granted</p>';
        echo '<p class="text-xs mt-1">Contact administrator for permissions</p>';
        echo '</div>';
        return;
    }
    
    // Render all root items directly
    foreach ($menuItems as $item) {
        renderMenuItem($item, $currentUrl, 0);
    }
}

function renderMenuItem($item, $currentUrl, $level = 0) {
    // Additional role check for Superadmin Actions menu
    if ($item['title'] === 'Superadmin Actions' && !Auth::isSuperAdmin()) {
        return; // Don't render this menu item for non-superadmin users
    }
    
    $hasChildren = !empty($item['children']);
    $isActive = $item['url'] && strpos($currentUrl, $item['url']) !== false;
    $hasActiveChild = $hasChildren && hasActiveChild($item['children'], $currentUrl);
    
    $activeClass = ($isActive || $hasActiveChild) ? 'active' : '';
    
    if ($hasChildren) {
        // Parent menu item with children
        echo '<div class="relative menu-item-container" data-level="' . $level . '">';
        echo '<button onclick="toggleSubmenu(\'submenu-' . $item['id'] . '\')" class="sidebar-item w-full flex items-center justify-between ' . $activeClass . '" data-tooltip="' . htmlspecialchars($item['title']) . '" data-level="' . $level . '">';
        echo '<div class="flex items-center">';
        echo renderMenuIcon($item['icon'], $level > 0);
        echo '<span>' . htmlspecialchars($item['title']) . '</span>';
        echo '</div>';
        echo '<svg id="arrow-' . $item['id'] . '" class="w-4 h-4 transition-transform duration-200 submenu-arrow" fill="currentColor" viewBox="0 0 20 20" style="' . (($isActive || $hasActiveChild) ? 'transform: rotate(180deg);' : '') . '">';
        echo '<path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>';
        echo '</svg>';
        echo '</button>';
        
        // Submenu container
        $submenuClass = ($isActive || $hasActiveChild) ? '' : 'hidden';
        echo '<div id="submenu-' . $item['id'] . '" class="' . $submenuClass . ' submenu-wrapper space-y-1" data-level="' . ($level + 1) . '">';
        
        foreach ($item['children'] as $child) {
            renderMenuItem($child, $currentUrl, $level + 1);
        }
        
        echo '</div>';
        echo '</div>';
    } else {
        // Leaf menu item
        $itemClass = $level > 0 ? 'sidebar-subitem' : 'sidebar-item';
        
        if ($item['url']) {
            echo '<a href="' . url($item['url']) . '" class="' . $itemClass . ' ' . $activeClass . '" data-tooltip="' . htmlspecialchars($item['title']) . '" data-level="' . $level . '">';
        } else {
            echo '<div class="' . $itemClass . ' ' . $activeClass . '" data-tooltip="' . htmlspecialchars($item['title']) . '" data-level="' . $level . '">';
        }
        
        echo renderMenuIcon($item['icon'], $level > 0);
        echo '<span>' . htmlspecialchars($item['title']) . '</span>';
        
        if ($item['url']) {
            echo '</a>';
        } else {
            echo '</div>';
        }
    }
}

function renderMenuIcon($iconName, $isSubitem = false) {
    $icons = [
        'dashboard' => '<path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>',
        'location' => '<path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>',
        'settings' => '<path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>',
        'shield' => '<path fill-rule="evenodd" d="M2.166 4.9L10 1.55l7.834 3.35a1 1 0 01.666.945V10c0 5.825-3.824 10.29-9 11.622C4.324 20.29.5 15.825.5 10V5.845a1 1 0 01.666-.945zM10 8a1 1 0 00-1 1v5a1 1 0 102 0V9a1 1 0 00-1-1z" clip-rule="evenodd"></path>',
        'inventory' => '<path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path>',
        'requests' => '<path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h.01a1 1 0 100-2H10zm3 0a1 1 0 000 2h.01a1 1 0 100-2H13zM7 13a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h.01a1 1 0 100-2H10zm3 0a1 1 0 000 2h.01a1 1 0 100-2H13z" clip-rule="evenodd"></path>',
        'reports' => '<path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path><path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>',
        'users' => '<path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3.005 3.005 0 013.75-2.906z"></path>',
        'masters' => '<path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>',
        'location-sub' => '<path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>',
        'business' => '<path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1.5 1.5 0 11-3 0V9.658A1.5 1.5 0 1010.5 7h-1A1.5 1.5 0 008 8.5V17a1.5 1.5 0 11-3 0V4z" clip-rule="evenodd"></path>',
        'boq' => '<path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>',
        'country' => '<path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a3 3 0 01-3-3V6z" clip-rule="evenodd"></path>',
        'zone' => '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C7.659 4.253 9.424 4 11.5 4c.487 0 .937.042 1.348.125a.999.999 0 01.782 1.144l-.195.975a.999.999 0 01-1.168.784A3.992 3.992 0 0011.5 7c-1.574 0-2.887.203-3.844.89-.315.225-.562.497-.735.803l-.001.002c-.086.151-.15.311-.19.479-.041.168-.06.338-.059.508 0 .584.225 1.132.618 1.554L7.3 11.23l.006.006c.311.312.622.625.933.937l1.042 1.042L9.223 15.2a1 1 0 11-1.414-1.414l.758-.758-1-1-.005-.005a3.995 3.995 0 01-1.124-2.812 4.02 4.02 0 01.109-1.02l-.001-.002a3.999 3.999 0 01.306-1.162z" clip-rule="evenodd"></path>',
        'state' => '<path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a3 3 0 01-3-3V6z" clip-rule="evenodd"></path>',
        'city' => '<path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h2v2H7V5zm0 4h2v2H7V9zm0 4h2v2H7v-2zm4-8h2v2h-2V5zm0 4h2v2h-2V9z" clip-rule="evenodd"></path>',
        'bank' => '<path fill-rule="evenodd" d="M10.496 2.132a1 1 0 00-.992 0l-7 4A1 1 0 003 8v.143c0 .351.05.694.148 1.019A4.47 4.47 0 003 10v6a1 1 0 102 0v-6a2.5 2.5 0 014.164-1.857 2.5 2.5 0 014.164 1.857v6a1 1 0 102 0v-6c0-.353-.05-.693-.148-1.013A4.47 4.47 0 0017 8.143V8a1 1 0 00-.504-.868l-6-1zM11 10h4v6h-4v-6zm-6 0h4v6H5v-6z" clip-rule="evenodd"></path>',
        'customer' => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>',
        'vendor' => '<path d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"></path>',
        'survey' => '<path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path>',
        'installation' => '<path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>',
        'courier' => '<path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path><path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"></path>',
        'audit' => '<path fill-rule="evenodd" d="M2.166 4.9L10 1.55l7.834 3.35a1 1 0 01.666.945V10c0 5.825-3.824 10.29-9 11.622C4.324 20.29.5 15.825.5 10V5.845a1 1 0 01.666-.945zM10 8a1 1 0 00-1 1v5a1 1 0 102 0V9a1 1 0 00-1-1z" clip-rule="evenodd"></path>',
        'bulk' => '<path fill-rule="evenodd" d="M2 4a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V4zm3 2a1 1 0 000 2h10a1 1 0 100-2H5zm0 4a1 1 0 000 2h10a1 1 0 100-2H5zm0 4a1 1 0 000 2h10a1 1 0 100-2H5z" clip-rule="evenodd"></path>'
    ];
    
    $iconPath = $icons[$iconName] ?? $icons['dashboard'];
    $iconSize = $isSubitem ? 'w-4 h-4 mr-2' : 'w-5 h-5 mr-3';
    
    return '<svg class="' . $iconSize . '" fill="currentColor" viewBox="0 0 20 20">' . $iconPath . '</svg>';
}

function hasActiveChild($children, $currentUrl) {
    foreach ($children as $child) {
        if ($child['url'] && strpos($currentUrl, $child['url']) !== false) {
            return true;
        }
        if (!empty($child['children']) && hasActiveChild($child['children'], $currentUrl)) {
            return true;
        }
    }
    return false;
}
?>
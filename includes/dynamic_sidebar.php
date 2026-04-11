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
    
    // Group menu items into sequential sections
    $renderSections = [];
    $currentStandaloneItems = [];
    
    foreach ($menuItems as $item) {
        $isSectionHeader = (!$item['url'] && !empty($item['children']));
        
        if ($isSectionHeader) {
            // Save any pending standalone items before starting a new section
            if (!empty($currentStandaloneItems)) {
                $renderSections[] = ['title' => '', 'items' => $currentStandaloneItems];
                $currentStandaloneItems = [];
            }
            // Add the section with its children
            $renderSections[] = ['title' => $item['title'], 'items' => $item['children']];
        } else {
            // Regular menu item or standalone root item
            $currentStandaloneItems[] = $item;
        }
    }
    
    // Add any remaining standalone items
    if (!empty($currentStandaloneItems)) {
        $renderSections[] = ['title' => '', 'items' => $currentStandaloneItems];
    }
    
    // Render sections
    foreach ($renderSections as $section) {
        if (!empty($section['items'])) {
            if ($section['title'] !== '') {
                echo '<div class="menu-section-header"><span class="sidebar-text">' . htmlspecialchars($section['title']) . '</span></div>';
            }
            foreach ($section['items'] as $item) {
                renderMenuItem($item, $currentUrl, 0);
            }
        }
    }
}

function renderMenuItem($item, $currentUrl, $level = 0) {
    $hasChildren = !empty($item['children']);
    $isActive = $item['url'] && strpos($currentUrl, $item['url']) !== false;
    $hasActiveChild = $hasChildren && hasActiveChild($item['children'], $currentUrl);
    
    $activeClass = ($isActive || $hasActiveChild) ? 'active' : '';
    
    if ($hasChildren) {
        // Parent menu item with children - use clean modern dropdown
        echo '<div class="relative">';
        echo '<button onclick="toggleSubmenu(\'submenu-' . $item['id'] . '\')" class="sidebar-item w-full flex items-center justify-between ' . $activeClass . '" data-tooltip="' . htmlspecialchars($item['title']) . '">';
        echo '<div class="flex items-center">';
        echo renderMenuIcon($item['icon'], false);
        echo '<span class="sidebar-text">' . htmlspecialchars($item['title']) . '</span>';
        echo '</div>';
        echo '<svg id="arrow-' . $item['id'] . '" class="w-4 h-4 transition-transform duration-200" fill="currentColor" viewBox="0 0 20 20">';
        echo '<path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>';
        echo '</svg>';
        echo '</button>';
        
        // Submenu container - clean modern style
        $submenuClass = ($isActive || $hasActiveChild) ? '' : 'hidden';
        echo '<div id="submenu-' . $item['id'] . '" class="' . $submenuClass . ' mt-2 space-y-1">';
        
        foreach ($item['children'] as $child) {
            renderMenuItem($child, $currentUrl, $level + 1);
        }
        
        echo '</div>';
        echo '</div>';
    } else {
        // Leaf menu item
        $itemClass = $level > 0 ? 'sidebar-subitem' : 'sidebar-item';
        
        if ($item['url']) {
            echo '<a href="' . url($item['url']) . '" class="' . $itemClass . ' ' . $activeClass . '" data-tooltip="' . htmlspecialchars($item['title']) . '">';
        } else {
            echo '<div class="' . $itemClass . ' ' . $activeClass . '" data-tooltip="' . htmlspecialchars($item['title']) . '">';
        }
        
        echo renderMenuIcon($item['icon'], $level > 0);
        echo '<span class="sidebar-text">' . htmlspecialchars($item['title']) . '</span>';
        
        if ($item['url']) {
            echo '</a>';
        } else {
            echo '</div>';
        }
    }
}

function renderMenuIcon($iconName, $isSubitem = false) {
    $icons = [
        'dashboard' => '<path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>',
        'location' => '<path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>',
        'settings' => '<path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>',
        'inventory' => '<path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM9 9a1 1 0 012 0v4a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>',
        'requests' => '<path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path>',
        'reports' => '<path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>',
        'users' => '<path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>',
        'location-sub' => '<path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V4H6z" clip-rule="evenodd"></path>',
        'business' => '<path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v11a3 3 0 106 0V4a2 2 0 00-2-2H4zM3 15a1 1 0 011-1h1a1 1 0 011 1v1a1 1 0 01-1 1H4a1 1 0 01-1-1v-1z" clip-rule="evenodd"></path>',
        'boq' => '<path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>',
        'country' => '<path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a3 3 0 01-3-3V6z" clip-rule="evenodd"></path>',
        'zone' => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>',
        'state' => '<path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>',
        'city' => '<path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h2v2H7V5zm0 4h2v2H7V9zm0 4h2v2H7v-2zm4-8h2v2h-2V5zm0 4h2v2h-2V9z" clip-rule="evenodd"></path>',
        'bank' => '<path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"></path>',
        'customer' => '<path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>',
        'vendor' => '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>',
        'installation' => '<path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>',
        'courier' => '<path fill-rule="evenodd" d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" clip-rule="evenodd"></path><path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"></path>',
        'audit' => '<path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>'
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
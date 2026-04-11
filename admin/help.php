<?php
$title = 'Help & Documentation';
ob_start();
?>

<div class="help-container">
<div class="flex bg-gray-50">
    <!-- Left Sidebar - Table of Contents -->
    <div class="w-80 bg-white border-r border-gray-200 flex-shrink-0 flex flex-col">
        <div class="p-4 border-b border-gray-200 flex-shrink-0 lg:p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-lg font-bold text-gray-900 truncate lg:text-xl">Help & Documentation</h1>
                    <p class="text-xs text-gray-600 mt-1 hidden sm:block lg:text-sm">Site Installation Management System</p>
                </div>
            </div>
        </div>
        
        <!-- Mobile Navigation Toggle -->
        <button class="mobile-nav-toggle" onclick="toggleMobileNav()">
            <span class="flex items-center justify-between w-full">
                <span class="text-sm font-medium">Table of Contents</span>
                <svg id="mobile-nav-arrow" class="w-4 h-4 transition-transform flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </span>
        </button>
        
        <nav class="p-4 overflow-y-auto flex-1 mobile-nav-content">
            <ul class="space-y-1">
                <li>
                    <a href="#getting-started" onclick="scrollToSection('#getting-started'); return false;" class="nav-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors">
                        <svg class="w-4 h-4 mr-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                        </svg>
                        Getting Started
                    </a>
                    <ul class="ml-7 mt-1 space-y-1">
                        <li><a href="#dashboard-overview" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Dashboard Overview</a></li>
                        <li><a href="#navigation" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Navigation</a></li>
                    </ul>
                </li>
                
                <li>
                    <a href="#user-management" onclick="scrollToSection('#user-management'); return false;" class="nav-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors">
                        <svg class="w-4 h-4 mr-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                        </svg>
                        User Management
                    </a>
                    <ul class="ml-7 mt-1 space-y-1">
                        <li><a href="#managing-users" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Managing Users</a></li>
                        <li><a href="#vendor-management" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Vendor Management</a></li>
                        <li><a href="#permission-system" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Permission System</a></li>
                    </ul>
                </li>
                
                <li>
                    <a href="#inventory-management" onclick="scrollToSection('#inventory-management'); return false;" class="nav-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors">
                        <svg class="w-4 h-4 mr-3 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM8 15a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Inventory Management
                    </a>
                    <ul class="ml-7 mt-1 space-y-1">
                        <li><a href="#stock-management" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Stock Management</a></li>
                        <li><a href="#material-requests" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Material Requests</a></li>
                        <li><a href="#dispatch-management" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Dispatch Management</a></li>
                    </ul>
                </li>
                
                <li>
                    <a href="#site-management" onclick="scrollToSection('#site-management'); return false;" class="nav-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors">
                        <svg class="w-4 h-4 mr-3 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"></path>
                        </svg>
                        Site Management
                    </a>
                    <ul class="ml-7 mt-1 space-y-1">
                        <li><a href="#creating-sites" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Creating New Sites</a></li>
                        <li><a href="#site-monitoring" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Site Monitoring</a></li>
                    </ul>
                </li>
                
                <li>
                    <a href="#site-surveys" onclick="scrollToSection('#site-surveys'); return false;" class="nav-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors">
                        <svg class="w-4 h-4 mr-3 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Site Surveys
                    </a>
                    <ul class="ml-7 mt-1 space-y-1">
                        <li><a href="#survey-management" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Survey Management</a></li>
                        <li><a href="#survey-types" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Survey Types</a></li>
                        <li><a href="#survey-data" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Survey Data Management</a></li>
                    </ul>
                </li>
                
                <li>
                    <a href="#installations" onclick="scrollToSection('#installations'); return false;" class="nav-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors">
                        <svg class="w-4 h-4 mr-3 text-teal-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9.504 1.132a1 1 0 01.992 0l1.75 1a1 1 0 11-.992 1.736L10 3.152l-1.254.716a1 1 0 11-.992-1.736l1.75-1z" clip-rule="evenodd"></path>
                        </svg>
                        Installation Management
                    </a>
                    <ul class="ml-7 mt-1 space-y-1">
                        <li><a href="#installation-process" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Installation Process</a></li>
                        <li><a href="#installation-tracking" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Installation Tracking</a></li>
                        <li><a href="#quality-assurance" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Quality Assurance</a></li>
                    </ul>
                </li>
                
                <li>
                    <a href="#material-usage" class="nav-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors">
                        <svg class="w-4 h-4 mr-3 text-cyan-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                        </svg>
                        Material Usage Tracking
                    </a>
                    <ul class="ml-7 mt-1 space-y-1">
                        <li><a href="#usage-recording" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Usage Recording</a></li>
                        <li><a href="#usage-categories" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Usage Categories</a></li>
                        <li><a href="#usage-analytics" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Usage Analytics</a></li>
                    </ul>
                </li>
                
                <li>
                    <a href="#reports" class="nav-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors">
                        <svg class="w-4 h-4 mr-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                        </svg>
                        Reports & Analytics
                    </a>
                    <ul class="ml-7 mt-1 space-y-1">
                        <li><a href="#inventory-reports" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Inventory Reports</a></li>
                        <li><a href="#site-reports" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Site Reports</a></li>
                        <li><a href="#advanced-analytics" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Advanced Analytics</a></li>
                    </ul>
                </li>
                
                <li>
                    <a href="#troubleshooting" class="nav-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors">
                        <svg class="w-4 h-4 mr-3 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Troubleshooting
                    </a>
                    <ul class="ml-7 mt-1 space-y-1">
                        <li><a href="#common-issues" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">Common Issues</a></li>
                        <li><a href="#system-maintenance" class="nav-sublink block px-3 py-1 text-xs text-gray-600 hover:text-gray-900">System Maintenance</a></li>
                    </ul>
                </li>
                
                <li>
                    <a href="#contact-support" class="nav-link flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors">
                        <svg class="w-4 h-4 mr-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-2 0c0 .993-.241 1.929-.668 2.754l-1.524-1.525a3.997 3.997 0 00.078-2.183l1.562-1.562C17.802 8.249 18 9.1 18 10z" clip-rule="evenodd"></path>
                        </svg>
                        Contact Support
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    
    <!-- Right Content Area -->
    <div class="flex-1 overflow-y-auto bg-gray-50 content-scroll">
        <div class="p-8 max-w-4xl">

            <!-- Getting Started Section -->
            <section id="getting-started" class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-8 h-8 text-blue-600 mr-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                    </svg>
                    Getting Started
                </h2>
                
                <div class="prose prose-lg max-w-none">
                    <div id="dashboard-overview" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Dashboard Overview</h3>
                        <p class="text-gray-600 mb-4">The admin dashboard provides a comprehensive overview of your site installation management system:</p>
                        <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                            <li><strong>System Statistics:</strong> View total sites, active projects, pending requests, and inventory levels</li>
                            <li><strong>Recent Activity:</strong> Monitor latest material requests, dispatches, and site updates</li>
                            <li><strong>Quick Actions:</strong> Access frequently used functions like creating new sites or processing requests</li>
                            <li><strong>Alerts & Notifications:</strong> Stay informed about urgent items requiring attention</li>
                        </ul>
                    </div>

                    <div id="navigation" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Navigation</h3>
                        <p class="text-gray-600 mb-4">The sidebar provides organized access to all system functions:</p>
                        <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                            <li><strong>Dashboard:</strong> Main overview and statistics</li>
                            <li><strong>Sites Management:</strong> Create, edit, and monitor installation sites</li>
                            <li><strong>Inventory:</strong> Manage stock, inwards, dispatches, and material requests</li>
                            <li><strong>User Management:</strong> Control user accounts, vendors, and permissions</li>
                            <li><strong>Master Data:</strong> Configure BOQ items, categories, and system settings</li>
                            <li><strong>Reports:</strong> Generate comprehensive reports and analytics</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- User Management Section -->
            <section id="user-management" class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-8 h-8 text-green-600 mr-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                    </svg>
                    User Management
                </h2>
                
                <div class="prose prose-lg max-w-none">
                    <div id="managing-users" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Managing Users</h3>
                        <div class="bg-gray-50 rounded-lg p-6 mb-4">
                            <h4 class="font-medium text-gray-900 mb-3">Creating New Users:</h4>
                            <ol class="list-decimal list-inside text-gray-600 space-y-2 ml-4">
                                <li>Navigate to <strong>Users → All Users</strong></li>
                                <li>Click <strong>"Add New User"</strong> button</li>
                                <li>Fill in required information (username, email, password)</li>
                                <li>Assign appropriate role (Admin, Vendor, etc.)</li>
                                <li>Set permissions and access levels</li>
                                <li>Save the user account</li>
                            </ol>
                        </div>
                    </div>

                    <div id="vendor-management" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Vendor Management</h3>
                        <div class="bg-gray-50 rounded-lg p-6 mb-4">
                            <h4 class="font-medium text-gray-900 mb-3">Setting up Vendors:</h4>
                            <ol class="list-decimal list-inside text-gray-600 space-y-2 ml-4">
                                <li>Go to <strong>Users → Vendors</strong></li>
                                <li>Click <strong>"Add New Vendor"</strong></li>
                                <li>Enter vendor company details and contact information</li>
                                <li>Create vendor user account with appropriate permissions</li>
                                <li>Assign sites and projects to the vendor</li>
                                <li>Configure material request and inventory access</li>
                            </ol>
                        </div>
                    </div>

                    <div id="permission-system" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Permission System</h3>
                        <p class="text-gray-600 mb-4">The system uses role-based permissions:</p>
                        <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                            <li><strong>Admin:</strong> Full system access and management capabilities</li>
                            <li><strong>Vendor:</strong> Limited access to assigned sites and inventory functions</li>
                            <li><strong>Site Manager:</strong> Site-specific access and reporting</li>
                            <li><strong>Viewer:</strong> Read-only access to reports and data</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Inventory Management Section -->
            <section id="inventory-management" class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-8 h-8 text-purple-600 mr-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM8 15a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    Inventory Management
                </h2>
                
                <div class="prose prose-lg max-w-none">
                    <div id="stock-management" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Stock Management</h3>
                        <div class="bg-gray-50 rounded-lg p-6 mb-4">
                            <h4 class="font-medium text-gray-900 mb-3">Adding New Stock:</h4>
                            <ol class="list-decimal list-inside text-gray-600 space-y-2 ml-4">
                                <li>Navigate to <strong>Inventory → Inwards</strong></li>
                                <li>Click <strong>"Create Inward Receipt"</strong></li>
                                <li>Enter supplier details and purchase information</li>
                                <li>Add items with quantities, serial numbers, and batch details</li>
                                <li>Verify and save the inward receipt</li>
                                <li>Stock will be automatically added to available inventory</li>
                            </ol>
                        </div>
                    </div>

                    <div id="material-requests" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Processing Material Requests</h3>
                        <div class="bg-gray-50 rounded-lg p-6 mb-4">
                            <h4 class="font-medium text-gray-900 mb-3">Request Workflow:</h4>
                            <ol class="list-decimal list-inside text-gray-600 space-y-2 ml-4">
                                <li>Review pending requests in <strong>Requests → Material Requests</strong></li>
                                <li>Verify request details and site requirements</li>
                                <li>Check stock availability for requested items</li>
                                <li>Approve or reject requests with comments</li>
                                <li>For approved requests, create dispatch orders</li>
                                <li>Track dispatch status and delivery confirmation</li>
                            </ol>
                        </div>
                    </div>

                    <div id="dispatch-management" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Dispatch Management</h3>
                        <p class="text-gray-600 mb-4">Efficiently manage material dispatches:</p>
                        <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                            <li><strong>Create Dispatches:</strong> Generate dispatch orders from approved requests</li>
                            <li><strong>Track Shipments:</strong> Monitor dispatch status and delivery progress</li>
                            <li><strong>Update Status:</strong> Mark dispatches as delivered and confirmed</li>
                            <li><strong>Print Documents:</strong> Generate dispatch notes and delivery receipts</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Site Management Section -->
            <section id="site-management" class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-8 h-8 text-indigo-600 mr-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z" clip-rule="evenodd"></path>
                    </svg>
                    Site Management
                </h2>
                
                <div class="prose prose-lg max-w-none">
                    <div id="creating-sites" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Creating New Sites</h3>
                        <div class="bg-gray-50 rounded-lg p-6 mb-4">
                            <h4 class="font-medium text-gray-900 mb-3">Site Setup Process:</h4>
                            <ol class="list-decimal list-inside text-gray-600 space-y-2 ml-4">
                                <li>Go to <strong>Sites → All Sites</strong></li>
                                <li>Click <strong>"Add New Site"</strong></li>
                                <li>Enter site details (ID, name, location, contact info)</li>
                                <li>Assign vendor and project manager</li>
                                <li>Set project timeline and milestones</li>
                                <li>Configure BOQ requirements and material specifications</li>
                            </ol>
                        </div>
                    </div>

                    <div id="site-monitoring" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Site Monitoring</h3>
                        <p class="text-gray-600 mb-4">Track site progress and activities:</p>
                        <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                            <li><strong>Installation Progress:</strong> Monitor completion status and milestones</li>
                            <li><strong>Material Usage:</strong> Track material consumption and waste</li>
                            <li><strong>Survey Reports:</strong> Review site surveys and technical assessments</li>
                            <li><strong>Issue Tracking:</strong> Manage site-specific problems and resolutions</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Site Surveys Section -->
            <section id="site-surveys" class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-8 h-8 text-orange-600 mr-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Site Surveys
                </h2>
                
                <div class="prose prose-lg max-w-none">
                    <div id="survey-management" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Survey Management</h3>
                        <p class="text-gray-600 mb-4">Site surveys are critical for project planning and execution. The system supports comprehensive survey management:</p>
                        <div class="bg-gray-50 rounded-lg p-6 mb-4">
                            <h4 class="font-medium text-gray-900 mb-3">Survey Workflow:</h4>
                            <ol class="list-decimal list-inside text-gray-600 space-y-2 ml-4">
                                <li>Navigate to <strong>Sites → Site Surveys</strong></li>
                                <li>Select site and create new survey request</li>
                                <li>Assign survey team and set schedule</li>
                                <li>Vendors conduct on-site technical assessment</li>
                                <li>Upload survey reports, photos, and measurements</li>
                                <li>Review and approve survey findings</li>
                                <li>Generate material requirements based on survey</li>
                            </ol>
                        </div>
                    </div>

                    <div id="survey-types" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Survey Types & Components</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-orange-50 rounded-lg p-6">
                                <h4 class="font-medium text-orange-900 mb-3">Pre-Installation Survey</h4>
                                <ul class="text-sm text-orange-800 space-y-1">
                                    <li>• Site accessibility assessment</li>
                                    <li>• Infrastructure evaluation</li>
                                    <li>• Power and connectivity checks</li>
                                    <li>• Environmental conditions</li>
                                    <li>• Safety and compliance review</li>
                                </ul>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-6">
                                <h4 class="font-medium text-blue-900 mb-3">Technical Survey</h4>
                                <ul class="text-sm text-blue-800 space-y-1">
                                    <li>• Equipment placement planning</li>
                                    <li>• Cable routing and measurements</li>
                                    <li>• Structural requirements</li>
                                    <li>• Integration points identification</li>
                                    <li>• Custom installation needs</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div id="survey-data" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Survey Data Management</h3>
                        <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                            <li><strong>Photo Documentation:</strong> Capture site conditions with geo-tagged images</li>
                            <li><strong>Measurement Records:</strong> Store precise measurements and dimensions</li>
                            <li><strong>Technical Drawings:</strong> Upload site layouts and installation plans</li>
                            <li><strong>Compliance Checklists:</strong> Ensure regulatory and safety compliance</li>
                            <li><strong>Material Calculations:</strong> Auto-generate BOQ based on survey data</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Installations Section -->
            <section id="installations" class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-8 h-8 text-teal-600 mr-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.504 1.132a1 1 0 01.992 0l1.75 1a1 1 0 11-.992 1.736L10 3.152l-1.254.716a1 1 0 11-.992-1.736l1.75-1z" clip-rule="evenodd"></path>
                    </svg>
                    Installation Management
                </h2>
                
                <div class="prose prose-lg max-w-none">
                    <div id="installation-process" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Installation Process</h3>
                        <p class="text-gray-600 mb-4">Manage the complete installation lifecycle from planning to completion:</p>
                        <div class="bg-gray-50 rounded-lg p-6 mb-4">
                            <h4 class="font-medium text-gray-900 mb-3">Installation Workflow:</h4>
                            <ol class="list-decimal list-inside text-gray-600 space-y-2 ml-4">
                                <li>Review approved survey and material requirements</li>
                                <li>Schedule installation team and resources</li>
                                <li>Verify material availability and dispatch</li>
                                <li>Conduct pre-installation safety briefing</li>
                                <li>Execute installation according to technical specifications</li>
                                <li>Record material usage and progress updates</li>
                                <li>Perform quality checks and testing</li>
                                <li>Complete installation documentation and handover</li>
                            </ol>
                        </div>
                    </div>

                    <div id="installation-tracking" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Installation Tracking</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-teal-50 rounded-lg p-6">
                                <h4 class="font-medium text-teal-900 mb-3">Progress Monitoring</h4>
                                <ul class="text-sm text-teal-800 space-y-1">
                                    <li>• Real-time installation status</li>
                                    <li>• Milestone completion tracking</li>
                                    <li>• Team productivity metrics</li>
                                    <li>• Quality checkpoint records</li>
                                    <li>• Issue and resolution logging</li>
                                </ul>
                            </div>
                            <div class="bg-green-50 rounded-lg p-6">
                                <h4 class="font-medium text-green-900 mb-3">Documentation</h4>
                                <ul class="text-sm text-green-800 space-y-1">
                                    <li>• Installation photos and videos</li>
                                    <li>• Technical configuration records</li>
                                    <li>• Test results and certifications</li>
                                    <li>• Handover documentation</li>
                                    <li>• Warranty and maintenance info</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div id="quality-assurance" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Quality Assurance</h3>
                        <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                            <li><strong>Pre-Installation Checks:</strong> Verify site readiness and material availability</li>
                            <li><strong>Installation Standards:</strong> Ensure compliance with technical specifications</li>
                            <li><strong>Testing Protocols:</strong> Conduct comprehensive system testing</li>
                            <li><strong>Sign-off Process:</strong> Obtain customer approval and acceptance</li>
                            <li><strong>Post-Installation Support:</strong> Provide maintenance and warranty services</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Material Usage Section -->
            <section id="material-usage" class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-8 h-8 text-cyan-600 mr-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                    </svg>
                    Material Usage Tracking
                </h2>
                
                <div class="prose prose-lg max-w-none">
                    <div id="usage-recording" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Usage Recording Process</h3>
                        <p class="text-gray-600 mb-4">Accurate material usage tracking is essential for cost control and inventory management:</p>
                        <div class="bg-gray-50 rounded-lg p-6 mb-4">
                            <h4 class="font-medium text-gray-900 mb-3">Recording Material Usage:</h4>
                            <ol class="list-decimal list-inside text-gray-600 space-y-2 ml-4">
                                <li>Access <strong>Installations → Material Usage</strong></li>
                                <li>Select the installation site and project</li>
                                <li>Record materials used with quantities and serial numbers</li>
                                <li>Document any material wastage or damage</li>
                                <li>Upload photos of installation progress</li>
                                <li>Submit usage report for approval</li>
                                <li>System automatically updates inventory levels</li>
                            </ol>
                        </div>
                    </div>

                    <div id="usage-categories" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Usage Categories</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-cyan-50 rounded-lg p-6">
                                <h4 class="font-medium text-cyan-900 mb-3">Installed Materials</h4>
                                <ul class="text-sm text-cyan-800 space-y-1">
                                    <li>• Successfully installed items</li>
                                    <li>• Serial number tracking</li>
                                    <li>• Location mapping</li>
                                    <li>• Warranty activation</li>
                                </ul>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-6">
                                <h4 class="font-medium text-yellow-900 mb-3">Wastage & Damage</h4>
                                <ul class="text-sm text-yellow-800 space-y-1">
                                    <li>• Damaged during transport</li>
                                    <li>• Installation errors</li>
                                    <li>• Defective materials</li>
                                    <li>• Excess cutting waste</li>
                                </ul>
                            </div>
                            <div class="bg-green-50 rounded-lg p-6">
                                <h4 class="font-medium text-green-900 mb-3">Returned Materials</h4>
                                <ul class="text-sm text-green-800 space-y-1">
                                    <li>• Unused materials</li>
                                    <li>• Excess inventory</li>
                                    <li>• Wrong specifications</li>
                                    <li>• Return to warehouse</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div id="usage-analytics" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Usage Analytics</h3>
                        <ul class="list-disc list-inside text-gray-600 space-y-2 ml-4">
                            <li><strong>Efficiency Metrics:</strong> Track material utilization rates and waste percentages</li>
                            <li><strong>Cost Analysis:</strong> Monitor actual vs. planned material costs</li>
                            <li><strong>Vendor Performance:</strong> Evaluate installation team efficiency</li>
                            <li><strong>Trend Analysis:</strong> Identify patterns in material usage across projects</li>
                            <li><strong>Inventory Impact:</strong> Real-time updates to stock levels and availability</li>
                        </ul>
                        
                        <div class="bg-blue-50 rounded-lg p-6 mt-6">
                            <h4 class="font-medium text-blue-900 mb-3">Automated Inventory Updates:</h4>
                            <ul class="text-sm text-blue-800 space-y-1 ml-4">
                                <li>• Material usage automatically reduces available stock</li>
                                <li>• Serial numbers are marked as "installed" in inventory</li>
                                <li>• Wastage is recorded and removed from active inventory</li>
                                <li>• Returned materials are added back to available stock</li>
                                <li>• Real-time inventory levels for accurate planning</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Reports Section -->
            <section id="reports" class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-8 h-8 text-yellow-600 mr-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                    </svg>
                    Reports & Analytics
                </h2>
                
                <div class="prose prose-lg max-w-none">
                    <p class="text-gray-600 mb-8">Generate comprehensive reports for business insights and decision making:</p>
                    
                    <div id="inventory-reports" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Inventory Reports</h3>
                        <div class="bg-gray-50 rounded-lg p-6">
                            <ul class="text-gray-600 space-y-2">
                                <li>• <strong>Stock levels and valuation:</strong> Current inventory status with financial values</li>
                                <li>• <strong>Material consumption analysis:</strong> Usage patterns and trends over time</li>
                                <li>• <strong>Supplier performance metrics:</strong> Delivery times, quality ratings, and costs</li>
                                <li>• <strong>Dispatch and delivery tracking:</strong> Shipment status and delivery performance</li>
                                <li>• <strong>Wastage and damage reports:</strong> Loss analysis and cost impact</li>
                            </ul>
                        </div>
                    </div>

                    <div id="site-reports" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Site Reports</h3>
                        <div class="bg-gray-50 rounded-lg p-6">
                            <ul class="text-gray-600 space-y-2">
                                <li>• <strong>Project progress and timelines:</strong> Milestone tracking and schedule adherence</li>
                                <li>• <strong>Site-wise material usage:</strong> Consumption patterns by location</li>
                                <li>• <strong>Installation completion status:</strong> Progress tracking and quality metrics</li>
                                <li>• <strong>Vendor performance analysis:</strong> Efficiency and quality assessments</li>
                                <li>• <strong>Cost analysis and budgeting:</strong> Actual vs. planned expenditure</li>
                            </ul>
                        </div>
                    </div>

                    <div id="advanced-analytics" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Advanced Analytics</h3>
                        <div class="bg-yellow-50 rounded-lg p-6">
                            <h4 class="font-medium text-yellow-900 mb-3">Business Intelligence Features:</h4>
                            <ul class="text-yellow-800 space-y-2 ml-4">
                                <li>• <strong>Interactive dashboards:</strong> Real-time data visualization and KPIs</li>
                                <li>• <strong>Trend analysis and forecasting:</strong> Predictive analytics for planning</li>
                                <li>• <strong>Cost optimization:</strong> Recommendations for efficiency improvements</li>
                                <li>• <strong>Performance benchmarking:</strong> Cross-site and vendor comparisons</li>
                                <li>• <strong>Custom report builder:</strong> Flexible reporting with advanced filters</li>
                                <li>• <strong>Automated scheduling:</strong> Regular report generation and distribution</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Troubleshooting Section -->
            <section id="troubleshooting" class="mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-8 h-8 text-red-600 mr-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    Troubleshooting
                </h2>
                
                <div class="prose prose-lg max-w-none">
                    <div id="common-issues" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Common Issues</h3>
                        <div class="space-y-6">
                            <div class="border-l-4 border-yellow-400 bg-yellow-50 p-6">
                                <h4 class="font-medium text-yellow-800 mb-3">Stock Discrepancies</h4>
                                <p class="text-yellow-700 mb-2"><strong>Issue:</strong> Physical stock doesn't match system records</p>
                                <p class="text-yellow-700"><strong>Solution:</strong> Perform stock reconciliation, check for unreported dispatches or damages, update records accordingly</p>
                            </div>
                            
                            <div class="border-l-4 border-blue-400 bg-blue-50 p-6">
                                <h4 class="font-medium text-blue-800 mb-3">User Access Issues</h4>
                                <p class="text-blue-700 mb-2"><strong>Issue:</strong> Users cannot access certain features</p>
                                <p class="text-blue-700"><strong>Solution:</strong> Check user roles and permissions, verify account status, reset passwords if needed</p>
                            </div>
                            
                            <div class="border-l-4 border-green-400 bg-green-50 p-6">
                                <h4 class="font-medium text-green-800 mb-3">Dispatch Delays</h4>
                                <p class="text-green-700 mb-2"><strong>Issue:</strong> Materials not reaching sites on time</p>
                                <p class="text-green-700"><strong>Solution:</strong> Review dispatch workflow, check courier performance, implement tracking alerts</p>
                            </div>
                        </div>
                    </div>

                    <div id="system-maintenance" class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">System Maintenance</h3>
                        <div class="bg-gray-50 rounded-lg p-6">
                            <ul class="list-disc list-inside text-gray-600 space-y-3 ml-4">
                                <li><strong>Regular Backups:</strong> Ensure daily database backups are running and tested</li>
                                <li><strong>Performance Monitoring:</strong> Check system performance and response times regularly</li>
                                <li><strong>User Activity:</strong> Monitor user login patterns and system usage analytics</li>
                                <li><strong>Data Cleanup:</strong> Archive old records and clean up temporary files periodically</li>
                                <li><strong>Security Updates:</strong> Keep system software and dependencies up to date</li>
                                <li><strong>Log Monitoring:</strong> Review system logs for errors and unusual activity</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contact Support -->
            <section id="contact-support" class="mb-12">
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-sm text-white p-8">
                    <h2 class="text-3xl font-bold mb-6 flex items-center">
                        <svg class="w-8 h-8 mr-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-2 0c0 .993-.241 1.929-.668 2.754l-1.524-1.525a3.997 3.997 0 00.078-2.183l1.562-1.562C17.802 8.249 18 9.1 18 10z" clip-rule="evenodd"></path>
                        </svg>
                        Need Additional Help?
                    </h2>
                    <p class="mb-6 text-lg">If you need further assistance or have specific questions not covered in this documentation:</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white bg-opacity-20 rounded-lg p-6">
                            <h4 class="font-semibold mb-3 text-lg">Technical Support</h4>
                            <p class="text-sm opacity-90 mb-1">Email: support@karvytech.com</p>
                            <p class="text-sm opacity-90">Phone: +91-XXX-XXX-XXXX</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-lg p-6">
                            <h4 class="font-semibold mb-3 text-lg">System Administrator</h4>
                            <p class="text-sm opacity-90 mb-2">Contact your system administrator for:</p>
                            <p class="text-sm opacity-90">• User access issues</p>
                            <p class="text-sm opacity-90">• System configuration</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
</div>

<style>
    /* Ensure full height layout - but don't override admin layout */
    .help-container {
        height: calc(100vh - 120px); /* Account for admin header */
        overflow: hidden;
    }
    
    /* Main container takes available height */
    .help-container .flex {
        height: 100%;
    }
    
    /* Prevent header text overflow */
    .help-container .truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    /* Ensure proper flex behavior */
    .help-container .min-w-0 {
        min-width: 0;
    }
    
    .help-container .flex-shrink-0 {
        flex-shrink: 0;
    }
    
    /* Smooth scrolling for anchor links within content area */
    .content-scroll {
        scroll-behavior: smooth;
        overflow-y: auto !important;
        max-height: 100% !important;
    }
    
    /* Ensure the content area is scrollable */
    .flex-1.overflow-y-auto {
        overflow-y: auto !important;
        height: 100% !important;
    }
    
    /* Highlight target sections */
    :target {
        animation: highlight 2s ease-in-out;
    }
    
    @keyframes highlight {
        0% { background-color: rgba(59, 130, 246, 0.1); }
        100% { background-color: transparent; }
    }
    
    /* Navigation styles */
    .nav-link.active {
        background-color: #f3f4f6;
        color: #1f2937;
        font-weight: 600;
    }
    
    .nav-sublink.active {
        color: #2563eb;
        font-weight: 600;
    }
    
    /* Content area styling */
    .prose h2 {
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
    }
    
    .prose h3 {
        color: #374151;
        margin-top: 2rem;
        margin-bottom: 1rem;
    }
    
    /* Custom scrollbar for both sidebar and content */
    .overflow-y-auto::-webkit-scrollbar {
        width: 8px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    /* Ensure content sections have proper spacing */
    .content-section {
        margin-bottom: 3rem;
        scroll-margin-top: 2rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 1280px) {
        .help-container .w-80 {
            width: 18rem;
        }
    }
    
    @media (max-width: 1024px) {
        .help-container .w-80 {
            width: 16rem;
        }
        
        .help-container .flex-1 .p-8 {
            padding: 1.5rem;
        }
        
        .help-container .max-w-4xl {
            max-width: none;
        }
    }
    
    @media (max-width: 768px) {
        .help-container {
            height: auto;
            min-height: calc(100vh - 120px);
        }
        
        .help-container .flex {
            flex-direction: column;
            height: auto;
        }
        
        .help-container .w-80 {
            width: 100%;
            max-height: none;
            border-right: none;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .help-container .w-80 nav {
            max-height: 50vh;
            overflow-y: auto;
        }
        
        .help-container .flex-1 {
            height: auto;
            min-height: 60vh;
        }
        
        .help-container .flex-1 .p-8 {
            padding: 1rem;
        }
        
        /* Mobile navigation improvements */
        .help-container .nav-link {
            padding: 0.75rem;
            font-size: 0.875rem;
        }
        
        .help-container .nav-sublink {
            padding: 0.5rem 0.75rem;
            margin-left: 1rem;
            font-size: 0.8125rem;
        }
        
        /* Mobile content improvements */
        .help-container h1 {
            font-size: 1.25rem;
        }
        
        .help-container h2 {
            font-size: 1.5rem;
        }
        
        .help-container h3 {
            font-size: 1.25rem;
        }
        
        /* Hide test button on mobile */
        .help-container button {
            display: none;
        }
    }
    
    @media (max-width: 640px) {
        .help-container .w-80 {
            padding: 0;
        }
        
        .help-container .w-80 .p-6 {
            padding: 1rem;
        }
        
        .help-container .w-80 nav {
            padding: 0.75rem;
            max-height: 40vh;
        }
        
        .help-container .flex-1 .p-8 {
            padding: 0.75rem;
        }
        
        /* Compact mobile navigation */
        .help-container .nav-link {
            padding: 0.5rem;
            font-size: 0.8125rem;
        }
        
        .help-container .nav-link svg {
            width: 1rem;
            height: 1rem;
            margin-right: 0.5rem;
        }
        
        .help-container .nav-sublink {
            padding: 0.375rem 0.5rem;
            margin-left: 0.75rem;
            font-size: 0.75rem;
        }
        
        /* Mobile content spacing */
        .help-container .mb-12 {
            margin-bottom: 2rem;
        }
        
        .help-container .mb-8 {
            margin-bottom: 1.5rem;
        }
        
        .help-container .mb-6 {
            margin-bottom: 1rem;
        }
        
        .help-container .mb-4 {
            margin-bottom: 0.75rem;
        }
        
        /* Mobile typography */
        .help-container h1 {
            font-size: 1.125rem;
            line-height: 1.4;
        }
        
        .help-container h2 {
            font-size: 1.25rem;
            line-height: 1.4;
        }
        
        .help-container h3 {
            font-size: 1.125rem;
            line-height: 1.4;
        }
        
        .help-container p, .help-container li {
            font-size: 0.875rem;
            line-height: 1.5;
        }
        
        /* Mobile card improvements */
        .help-container .bg-gray-50,
        .help-container .bg-blue-50,
        .help-container .bg-green-50,
        .help-container .bg-orange-50,
        .help-container .bg-teal-50,
        .help-container .bg-cyan-50,
        .help-container .bg-yellow-50 {
            padding: 0.75rem;
            margin-bottom: 0.75rem;
        }
        
        /* Mobile grid adjustments */
        .help-container .grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
    }
    
    /* Collapsible navigation for mobile */
    @media (max-width: 768px) {
        .mobile-nav-toggle {
            display: block;
            width: 100%;
            text-align: left;
            padding: 0.75rem 1rem;
            background: #f8fafc;
            border: none;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .mobile-nav-toggle:hover {
            background: #f1f5f9;
        }
        
        .mobile-nav-toggle:active {
            background: #e2e8f0;
        }
        
        .mobile-nav-content {
            display: none;
            animation: slideDown 0.2s ease-out;
        }
        
        .mobile-nav-content.show {
            display: block;
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
    }
    
    @media (min-width: 769px) {
        .mobile-nav-toggle {
            display: none;
        }
        
        .mobile-nav-content {
            display: block !important;
        }
    }
    
    /* Header responsive improvements */
    @media (max-width: 640px) {
        .help-container .w-80 .p-4 {
            padding: 0.75rem;
        }
        
        .help-container .w-80 h1 {
            font-size: 1rem;
            line-height: 1.25;
        }
        
        .help-container .w-80 p {
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Try multiple selectors to find the content area
        let contentArea = document.querySelector('.flex-1.overflow-y-auto.bg-gray-50.content-scroll');
        if (!contentArea) {
            contentArea = document.querySelector('.flex-1.overflow-y-auto');
        }
        if (!contentArea) {
            contentArea = document.querySelector('.content-scroll');
        }
        
        console.log('Content area found:', contentArea);
        
        // Mobile navigation toggle
        window.toggleMobileNav = function() {
            const navContent = document.querySelector('.mobile-nav-content');
            const arrow = document.getElementById('mobile-nav-arrow');
            
            if (navContent && arrow) {
                navContent.classList.toggle('show');
                arrow.style.transform = navContent.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        };
        
        // Test scroll function
        window.testScroll = function() {
            console.log('Testing scroll...');
            if (contentArea) {
                contentArea.scrollTo({
                    top: 500,
                    behavior: 'smooth'
                });
                console.log('Scroll command sent, current scrollTop:', contentArea.scrollTop);
            }
        };
        
        // Simple fallback method for navigation
        window.scrollToSection = function(targetId) {
            const target = document.querySelector(targetId);
            console.log('scrollToSection called:', targetId, 'Target:', target, 'ContentArea:', contentArea);
            
            // Close mobile navigation if open
            if (window.innerWidth <= 768) {
                const navContent = document.querySelector('.mobile-nav-content');
                const arrow = document.getElementById('mobile-nav-arrow');
                if (navContent && navContent.classList.contains('show')) {
                    navContent.classList.remove('show');
                    if (arrow) arrow.style.transform = 'rotate(0deg)';
                }
            }
            
            if (target && contentArea) {
                // Try multiple approaches to ensure scrolling works
                const scrollPosition = target.offsetTop - 32;
                
                console.log('Target offsetTop:', target.offsetTop, 'Calculated scroll position:', scrollPosition);
                console.log('ContentArea scrollHeight:', contentArea.scrollHeight, 'clientHeight:', contentArea.clientHeight);
                
                // Method 1: scrollTo with smooth behavior
                contentArea.scrollTo({
                    top: scrollPosition,
                    behavior: 'smooth'
                });
                
                // Method 2: Fallback with scrollTop (in case scrollTo doesn't work)
                setTimeout(() => {
                    if (contentArea.scrollTop === 0 || Math.abs(contentArea.scrollTop - scrollPosition) > 50) {
                        console.log('Fallback scrolling method');
                        contentArea.scrollTop = scrollPosition;
                    }
                }, 100);
                
                updateActiveNavigation(targetId);
            } else {
                console.error('Target or contentArea not found:', { target, contentArea });
            }
        };
        
        // Add smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const target = document.querySelector(targetId);
                
                console.log('Clicked link:', targetId, 'Target found:', !!target);
                
                if (target && contentArea) {
                    // Update active navigation
                    updateActiveNavigation(targetId);
                    
                    // Get the target's position relative to the content area
                    const contentAreaTop = contentArea.offsetTop;
                    const targetTop = target.offsetTop;
                    
                    // Calculate scroll position with offset
                    const scrollPosition = targetTop - 32; // 32px offset from top
                    
                    console.log('Scrolling to position:', scrollPosition);
                    
                    // Scroll to target within content area
                    contentArea.scrollTo({
                        top: scrollPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Update active navigation based on scroll position
        function updateActiveNavigation(targetId) {
            // Remove active class from all nav links
            document.querySelectorAll('.nav-link, .nav-sublink').forEach(link => {
                link.classList.remove('active');
            });
            
            // Add active class to current link
            const activeLink = document.querySelector(`a[href="${targetId}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
                
                // Also activate parent nav link if this is a sublink
                if (activeLink.classList.contains('nav-sublink')) {
                    const parentSection = targetId.split('-')[0];
                    const parentLink = document.querySelector(`a[href="#${parentSection}"]`);
                    if (parentLink && !parentLink.classList.contains('nav-sublink')) {
                        parentLink.classList.add('active');
                    }
                }
            }
        }
        
        // Intersection Observer for automatic navigation highlighting
        const sections = document.querySelectorAll('section[id], div[id]');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute('id');
                    updateActiveNavigation(`#${id}`);
                }
            });
        }, {
            root: contentArea,
            rootMargin: '-20% 0px -60% 0px',
            threshold: 0.1
        });
        
        sections.forEach(section => {
            observer.observe(section);
        });
        
        // Set initial active state
        if (window.location.hash) {
            setTimeout(() => {
                updateActiveNavigation(window.location.hash);
                const target = document.querySelector(window.location.hash);
                if (target && contentArea) {
                    const scrollPosition = target.offsetTop - 32;
                    contentArea.scrollTo({
                        top: scrollPosition,
                        behavior: 'smooth'
                    });
                }
            }, 100);
        } else {
            // Default to first section
            const firstLink = document.querySelector('.nav-link');
            if (firstLink) {
                firstLink.classList.add('active');
            }
        }
        
        // Add scroll event listener to content area for active navigation updates
        if (contentArea) {
            let scrollTimeout;
            contentArea.addEventListener('scroll', function() {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    // Find the section currently in view
                    const scrollTop = contentArea.scrollTop;
                    let activeSection = null;
                    
                    sections.forEach(section => {
                        const sectionTop = section.offsetTop - 100; // 100px offset
                        const sectionBottom = sectionTop + section.offsetHeight;
                        
                        if (scrollTop >= sectionTop && scrollTop < sectionBottom) {
                            activeSection = section;
                        }
                    });
                    
                    if (activeSection && activeSection.id) {
                        updateActiveNavigation(`#${activeSection.id}`);
                    }
                }, 100);
            });
        }
        
        // Handle window resize for responsive behavior
        window.addEventListener('resize', function() {
            const navContent = document.querySelector('.mobile-nav-content');
            const arrow = document.getElementById('mobile-nav-arrow');
            
            // Reset mobile navigation on desktop
            if (window.innerWidth > 768) {
                if (navContent) navContent.classList.remove('show');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/admin_layout.php';
?>
<?php
require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../models/Inventory.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$dispatchId = $_GET['id'] ?? null;

if (!$dispatchId) {
    header('Location: index.php');
    exit;
}

try {
    $inventoryModel = new Inventory();
    $dispatch = $inventoryModel->getDispatchDetails($dispatchId);
    
    // Initialize variables with safe defaults
    $deliveryConfirmation = null;
    $hasDocuments = false;
    
    if ($dispatch) {
        try {
            $deliveryConfirmation = $inventoryModel->getDeliveryConfirmationDetails($dispatchId);
        } catch (Exception $e) {
            error_log("Error getting delivery confirmation: " . $e->getMessage());
            $deliveryConfirmation = null;
        }
        
        try {
            $hasDocuments = $inventoryModel->hasUploadedDocuments($dispatchId);
        } catch (Exception $e) {
            error_log("Error checking documents: " . $e->getMessage());
            $hasDocuments = false;
        }
    }

    if (!$dispatch) {
        header('Location: index.php?error=dispatch_not_found');
        exit;
    }
} catch (Exception $e) {
    error_log("Error in view-dispatch.php: " . $e->getMessage());
    header('Location: index.php?error=database_error');
    exit;
}

$title = 'Dispatch Details - ' . $dispatch['dispatch_number'];
ob_start();

// Debug lines for troubleshooting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug: Check if we have dispatch data
if (empty($dispatch)) {
    echo "<!-- DEBUG: No dispatch data found -->";
} else {
    echo "<!-- DEBUG: Dispatch data found, ID: " . $dispatch['id'] . " -->";
}

// Debug: Check if we have items
if (empty($dispatch['items'])) {
    echo "<!-- DEBUG: No items found -->";
} else {
    echo "<!-- DEBUG: Found " . count($dispatch['items']) . " items -->";
}
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dispatch Details</h1>
            <p class="mt-2 text-sm text-gray-600">Complete dispatch information and delivery tracking</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="window.print()" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd"></path>
                </svg>
                Print
            </button>
            <a href="index.php" class="btn btn-primary">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
                Back to Dispatches
            </a>
        </div>
    </div>

    <!-- Status Overview Card -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a2 2 0 002 2h8a2 2 0 002-2V3a2 2 0 012 2v6h-3a2 2 0 00-2 2v4H6a2 2 0 01-2-2V5zm8 8a2 2 0 012-2h3v4a2 2 0 01-2 2v-1a2 2 0 00-2-2h-1v-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Dispatch Number</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($dispatch['dispatch_number']); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Material Request ID</p>
                    <p class="text-lg font-semibold text-gray-900">#<?php echo htmlspecialchars($dispatch['material_request_id'] ?? 'N/A'); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Status</p>
                    <?php
                    // Determine the actual status based on delivery confirmation
                    $actualStatus = $dispatch['dispatch_status'];
                    
                    // If status is empty but we have delivery confirmation, set it as delivered/confirmed
                    if (empty($actualStatus) && $deliveryConfirmation) {
                        if ($deliveryConfirmation['confirmation_date']) {
                            $actualStatus = 'confirmed';
                        } elseif ($deliveryConfirmation['delivery_date']) {
                            $actualStatus = 'delivered';
                        }
                    }
                    
                    // If still empty, default to 'dispatched'
                    if (empty($actualStatus)) {
                        $actualStatus = 'dispatched';
                    }
                    
                    $statusClasses = [
                        'prepared' => 'bg-blue-100 text-blue-800',
                        'dispatched' => 'bg-yellow-100 text-yellow-800',
                        'in_transit' => 'bg-purple-100 text-purple-800',
                        'delivered' => 'bg-green-100 text-green-800',
                        'confirmed' => 'bg-emerald-100 text-emerald-800',
                        'returned' => 'bg-red-100 text-red-800'
                    ];
                    $statusClass = $statusClasses[$actualStatus] ?? 'bg-gray-100 text-gray-800';
                    ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $statusClass; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $actualStatus)); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Dispatch Date</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo date('d M Y', strtotime($dispatch['dispatch_date'])); ?></p>
                </div>
            </div>
        </div>

        <a href="../../../contractor/inventory/" class="block bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:bg-gray-50 hover:border-gray-300 transition-colors cursor-pointer">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Items</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo count($dispatch['items']); ?></p>
                    <p class="text-xs text-blue-600 mt-1">Click to view contractor inventory →</p>
                </div>
            </div>
        </a>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Dispatch Information Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a2 2 0 002 2h8a2 2 0 002-2V3a2 2 0 012 2v6h-3a2 2 0 00-2 2v4H6a2 2 0 01-2-2V5zm8 8a2 2 0 012-2h3v4a2 2 0 01-2 2v-1a2 2 0 00-2-2h-1v-1z" clip-rule="evenodd"></path>
                    </svg>
                    Dispatch Information
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <?php if ($dispatch['site_code']): ?>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-500">Site Code</span>
                        <span class="text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($dispatch['site_code']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($dispatch['vendor_company_name'] || $dispatch['vendor_name']): ?>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-500">Contractor</span>
                        <span class="text-sm text-gray-900 font-medium">
                            <?php 
                            if ($dispatch['vendor_company_name']) {
                                echo htmlspecialchars($dispatch['vendor_company_name']);
                                if ($dispatch['vendor_name'] && $dispatch['vendor_name'] !== $dispatch['vendor_company_name']) {
                                    echo ' (' . htmlspecialchars($dispatch['vendor_name']) . ')';
                                }
                            } else {
                                echo htmlspecialchars($dispatch['vendor_name']);
                            }
                            ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-500">Expected Delivery</span>
                        <span class="text-sm text-gray-900 font-medium">
                            <?php echo $dispatch['expected_delivery_date'] ? date('d M Y', strtotime($dispatch['expected_delivery_date'])) : 'Not specified'; ?>
                        </span>
                    </div>
                    
                    <?php if ($dispatch['courier_name']): ?>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-500">Courier</span>
                        <span class="text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($dispatch['courier_name']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($dispatch['tracking_number']): ?>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-500">Tracking Number</span>
                        <span class="text-sm text-gray-900 font-medium font-mono"><?php echo htmlspecialchars($dispatch['tracking_number']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex justify-between py-2">
                        <span class="text-sm font-medium text-gray-500">Dispatched By</span>
                        <span class="text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($dispatch['dispatched_by_name'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact & Delivery Information Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                    </svg>
                    Contact & Delivery Details
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-500">Contact Person</span>
                        <span class="text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($dispatch['contact_person_name']); ?></span>
                    </div>
                    
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-500">Contact Phone</span>
                        <span class="text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($dispatch['contact_person_phone'] ?: 'Not provided'); ?></span>
                    </div>
                    
                    <div class="py-2">
                        <span class="text-sm font-medium text-gray-500 block mb-2">Delivery Address</span>
                        <div class="bg-gray-50 p-3 rounded-md">
                            <p class="text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($dispatch['delivery_address'])); ?></p>
                        </div>
                    </div>
                    
                    <?php if ($dispatch['delivery_remarks']): ?>
                    <div class="py-2">
                        <span class="text-sm font-medium text-gray-500 block mb-2">Delivery Remarks</span>
                        <div class="bg-gray-50 p-3 rounded-md">
                            <?php 
                            // Parse delivery remarks if it contains structured data
                            $remarks = $dispatch['delivery_remarks'];
                            
                            // Check if it's JSON-like structured data
                            if (strpos($remarks, 'Notes:') !== false && strpos($remarks, 'Received by:') !== false) {
                                // Parse the structured format
                                $parts = explode(' | ', $remarks);
                                echo '<div class="space-y-2">';
                                foreach ($parts as $part) {
                                    $part = trim($part);
                                    if (strpos($part, ':') !== false) {
                                        list($label, $value) = explode(':', $part, 2);
                                        $label = trim($label);
                                        $value = trim($value);
                                        
                                        // Skip item confirmations as they're shown separately
                                        if ($label === 'Item Confirmations') {
                                            continue;
                                        }
                                        
                                        echo '<div class="flex justify-between">';
                                        echo '<span class="font-medium text-gray-600">' . htmlspecialchars($label) . ':</span>';
                                        echo '<span class="text-gray-900">' . htmlspecialchars($value) . '</span>';
                                        echo '</div>';
                                    }
                                }
                                echo '</div>';
                            } else {
                                // Display as regular text if not structured
                                echo '<p class="text-sm text-gray-900">' . nl2br(htmlspecialchars($remarks)) . '</p>';
                            }
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Confirmation Section -->
    <?php if ($deliveryConfirmation && is_array($deliveryConfirmation) && ($deliveryConfirmation['delivery_date'] || $deliveryConfirmation['received_by'])): ?>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-emerald-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Delivery Confirmation
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (isset($deliveryConfirmation['delivery_date']) && $deliveryConfirmation['delivery_date']): ?>
                <div class="bg-emerald-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-medium text-emerald-800">Delivery Date</span>
                    </div>
                    <p class="mt-2 text-lg font-semibold text-emerald-900">
                        <?php echo date('d M Y', strtotime($deliveryConfirmation['delivery_date'])); ?>
                        <?php if (isset($deliveryConfirmation['delivery_time']) && $deliveryConfirmation['delivery_time']): ?>
                            <span class="text-sm font-normal">at <?php echo date('H:i', strtotime($deliveryConfirmation['delivery_time'])); ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <?php if (isset($deliveryConfirmation['received_by']) && $deliveryConfirmation['received_by']): ?>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-medium text-blue-800">Received By</span>
                    </div>
                    <p class="mt-2 text-lg font-semibold text-blue-900"><?php echo htmlspecialchars($deliveryConfirmation['received_by']); ?></p>
                    <?php if (isset($deliveryConfirmation['received_by_phone']) && $deliveryConfirmation['received_by_phone']): ?>
                        <p class="text-sm text-blue-700"><?php echo htmlspecialchars($deliveryConfirmation['received_by_phone']); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($deliveryConfirmation['confirmation_date']) && $deliveryConfirmation['confirmation_date']): ?>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-purple-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-medium text-purple-800">Confirmed</span>
                    </div>
                    <p class="mt-2 text-lg font-semibold text-purple-900"><?php echo date('d M Y H:i', strtotime($deliveryConfirmation['confirmation_date'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (isset($deliveryConfirmation['actual_delivery_address']) && $deliveryConfirmation['actual_delivery_address'] && $deliveryConfirmation['actual_delivery_address'] != $dispatch['delivery_address']): ?>
            <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
                <h4 class="text-sm font-medium text-yellow-800 mb-2">Actual Delivery Address</h4>
                <p class="text-sm text-yellow-700"><?php echo nl2br(htmlspecialchars($deliveryConfirmation['actual_delivery_address'])); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if (isset($deliveryConfirmation['delivery_notes']) && $deliveryConfirmation['delivery_notes']): ?>
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-800 mb-2">Delivery Notes</h4>
                <p class="text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($deliveryConfirmation['delivery_notes'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <!-- Documents Section -->
    <?php 
    // Debug: Check delivery confirmation data
    if ($deliveryConfirmation) {
        echo "<!-- DEBUG: Delivery confirmation exists -->";
        if (isset($deliveryConfirmation['lr_copy_path'])) {
            echo "<!-- DEBUG: LR copy path: " . htmlspecialchars($deliveryConfirmation['lr_copy_path']) . " -->";
        }
        if (isset($deliveryConfirmation['additional_documents'])) {
            echo "<!-- DEBUG: Additional documents: " . (is_array($deliveryConfirmation['additional_documents']) ? count($deliveryConfirmation['additional_documents']) : 'not array') . " -->";
        }
    } else {
        echo "<!-- DEBUG: No delivery confirmation data -->";
    }
    ?>
    <?php if ($hasDocuments || ($deliveryConfirmation && is_array($deliveryConfirmation) && (isset($deliveryConfirmation['lr_copy_path']) || !empty($deliveryConfirmation['additional_documents'])))): ?>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                </svg>
                Documents & Attachments
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php if ($deliveryConfirmation && isset($deliveryConfirmation['lr_copy_path']) && !empty($deliveryConfirmation['lr_copy_path'])): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">LR Copy</p>
                            <p class="text-xs text-gray-500">Delivery Receipt</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="<?php echo '../../../'.htmlspecialchars($deliveryConfirmation['lr_copy_path']); ?>" 
                           target="_blank" 
                           class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            View Document →
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($deliveryConfirmation && !empty($deliveryConfirmation['additional_documents'])): ?>
                    <?php foreach ($deliveryConfirmation['additional_documents'] as $index => $doc): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 text-blue-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <?php 
                                // Handle string paths (current format)
                                if (is_string($doc)) {
                                    $docPath = $doc;
                                    $docName = "Document " . ($index + 1);
                                    $docType = "Additional Document";
                                } else {
                                    // Handle object format (future compatibility)
                                    $docPath = $doc['path'] ?? '';
                                    $docName = $doc['name'] ?? "Document " . ($index + 1);
                                    $docType = $doc['type'] ?? 'Additional Document';
                                }
                                ?>
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($docName); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($docType); ?></p>
                            </div>
                        </div>

                        <div class="mt-3">
                            <?php if (!empty($docPath)): ?>
                                <a href="<?php echo '../../../'.htmlspecialchars($docPath); ?>" 
                                   target="_blank" 
                                   class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    View Document →
                                </a>
                            <?php else: ?>
                                <span class="text-sm text-gray-400">Document path not available</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if (!$hasDocuments && (!$deliveryConfirmation || (empty($deliveryConfirmation['lr_copy_path']) && empty($deliveryConfirmation['additional_documents'])))): ?>
                <div class="col-span-full text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                    </svg>
                    <p>No documents uploaded for this dispatch</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <!-- Dispatch Items -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-orange-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                </svg>
                Dispatched Items
            </h3>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Details</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent Qty</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received Qty</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch/Serial</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Condition</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Warranty</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($dispatch['items'])): ?>
                            <?php 
                            $totalValue = 0;
                            foreach ($dispatch['items'] as $item): 
                                $totalValue += $item['total_cost'];
                                
                                // Get item confirmations if available
                                $itemConfirmation = null;
                                if ($deliveryConfirmation && !empty($deliveryConfirmation['item_confirmations'])) {
                                    foreach ($deliveryConfirmation['item_confirmations'] as $confirmation) {
                                        if ($confirmation['boq_item_id'] == $item['boq_item_id']) {
                                            $itemConfirmation = $confirmation;
                                            break;
                                        }
                                    }
                                }
                                
                                $receivedQty = $itemConfirmation ? $itemConfirmation['received_quantity'] : null;
                                $receivedCondition = $itemConfirmation ? ($itemConfirmation['condition'] ?? null) : null;
                                $receivedRemarks = $itemConfirmation ? ($itemConfirmation['notes'] ?? null) : null;
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V7l-7-5z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['item_name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($item['item_code']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo number_format($item['quantity_dispatched'], 0); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($item['unit']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($receivedQty !== null): ?>
                                        <div class="text-sm font-medium text-gray-900"><?php echo number_format($receivedQty, 0); ?></div>
                                        <?php if ($receivedQty != $item['quantity_dispatched']): ?>
                                            <div class="text-xs text-red-600">
                                                <?php echo $receivedQty < $item['quantity_dispatched'] ? 'Short' : 'Excess'; ?>: 
                                                <?php echo abs($receivedQty - $item['quantity_dispatched']); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-xs text-green-600">✓ Matched</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-400">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₹<?php echo number_format($item['unit_cost'] ?? 0, 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ₹<?php echo number_format($item['total_cost'] ?? 0, 2); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if (!empty($item['serial_numbers']) && $item['serial_numbers'] !== null): ?>
                                        <div class="text-sm text-gray-900">
                                            <span class="font-medium">Serial:</span> 
                                            <?php 
                                            $serialNumbers = htmlspecialchars($item['serial_numbers']);
                                            $maxLength = 50; // Show first 50 characters
                                            if (strlen($serialNumbers) > $maxLength): 
                                            ?>
                                                <span><?php echo substr($serialNumbers, 0, $maxLength); ?>...</span>
                                                <button type="button" 
                                                        onclick="showSerialModal('<?php echo addslashes($serialNumbers); ?>', '<?php echo addslashes($item['item_name']); ?>')"
                                                        class="ml-2 inline-flex items-center text-blue-600 hover:text-blue-800"
                                                        title="View all serial numbers">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </button>
                                            <?php else: ?>
                                                <span><?php echo $serialNumbers; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['batch_number']) && $item['batch_number'] !== null): ?>
                                        <div class="text-sm text-gray-500">
                                            <span class="font-medium">Batch:</span> <?php echo htmlspecialchars($item['batch_number']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ((empty($item['serial_numbers']) || $item['serial_numbers'] === null) && (empty($item['batch_number']) || $item['batch_number'] === null)): ?>
                                        <span class="text-sm text-gray-400">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Sent: <?php echo ucfirst($item['item_condition'] ?? 'new'); ?>
                                        </span>
                                        <?php if ($receivedCondition): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                <?php echo $receivedCondition === 'good' ? 'bg-green-100 text-green-800' : 
                                                    ($receivedCondition === 'damaged' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                                Received: <?php echo ucfirst($receivedCondition); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($item['warranty_period'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-2">
                                        <?php if (isset($item['remarks']) && $item['remarks']): ?>
                                            <div class="text-sm text-gray-900">
                                                <span class="font-medium text-gray-500">Dispatch:</span><br>
                                                <?php echo nl2br(htmlspecialchars($item['remarks'])); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($receivedRemarks): ?>
                                            <div class="text-sm text-gray-900">
                                                <span class="font-medium text-gray-500">Received:</span><br>
                                                <?php echo nl2br(htmlspecialchars($receivedRemarks)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ((!isset($item['remarks']) || !$item['remarks']) && !$receivedRemarks): ?>
                                            <span class="text-sm text-gray-400">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-gray-500">No items found in this dispatch</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($dispatch['items'])): ?>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-900">Total Dispatch Value:</td>
                            <td class="px-6 py-3 text-sm font-bold text-gray-900">₹<?php echo number_format($totalValue, 2); ?></td>
                            <td colspan="4" class="px-6 py-3"></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>
<style>
@media print {
    .btn, .card-header {
        display: none !important;
    }
    
    .bg-white {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
    
    body {
        background: white !important;
    }
}
</style>

<!-- Serial Numbers Modal -->
<div id="serialModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="serialModalTitle">Serial Numbers</h3>
                <button type="button" onclick="closeSerialModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
            
            <div class="bg-gray-50 border border-gray-200 rounded-md p-4 max-h-96 overflow-y-auto">
                <table class="w-full text-sm" id="serialTable">
                    <thead class="bg-gray-100 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial Number</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="serialModalContent">
                        <!-- Serial numbers will be displayed here -->
                    </tbody>
                </table>
            </div>
            
            <div class="flex justify-end mt-4">
                <button type="button" onclick="copySerialNumbers()" 
                        class="mr-3 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"></path>
                        <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"></path>
                    </svg>
                    Copy
                </button>
                <button type="button" onclick="closeSerialModal()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentSerialNumbers = '';

function showSerialModal(serialNumbers, itemName) {
    currentSerialNumbers = serialNumbers;
    document.getElementById('serialModalTitle').textContent = 'Serial Numbers - ' + itemName + ' (' + serialArray.length + ' items)';
    
    // Parse serial numbers (assuming they are comma, semicolon, or newline separated)
    const serialArray = serialNumbers.split(/[,;\n\r]+/)
        .map(s => s.trim())
        .filter(s => s.length > 0);
    
    // Generate table rows
    let tableHTML = '';
    serialArray.forEach((serial, index) => {
        tableHTML += `
            <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'}">
                <td class="px-3 py-2 text-gray-900 font-medium">${index + 1}</td>
                <td class="px-3 py-2 text-gray-900 font-mono">${serial}</td>
            </tr>
        `;
    });
    
    document.getElementById('serialModalContent').innerHTML = tableHTML;
    document.getElementById('serialModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeSerialModal() {
    document.getElementById('serialModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function copySerialNumbers() {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(currentSerialNumbers).then(function() {
            // Show success feedback
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Copied!';
            setTimeout(() => {
                button.innerHTML = originalText;
            }, 2000);
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
            fallbackCopyTextToClipboard(currentSerialNumbers);
        });
    } else {
        fallbackCopyTextToClipboard(currentSerialNumbers);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Copied!';
            setTimeout(() => {
                button.innerHTML = originalText;
            }, 2000);
        }
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
    }
    
    document.body.removeChild(textArea);
}

// Close modal when clicking outside
document.getElementById('serialModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSerialModal();
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../../includes/admin_layout.php';
?>
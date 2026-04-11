<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../models/BoqItem.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$boqModel = new BoqItem();

// Handle search and filters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$page = (int)($_GET['page'] ?? 1);

$result = $boqModel->getAllWithPagination($page, 20, $search, $category, $status);
$categories = $boqModel->getCategories();

$title = 'BOQ Items Management';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">BOQ Items Management</h1>
        <p class="mt-2 text-sm text-gray-700">Manage Bill of Quantities items and materials</p>
    </div>
    <div class="flex space-x-2">
        <button onclick="exportBOQ()" class="btn btn-secondary" title="Export BOQ">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
        <button onclick="openModal('createBoqModal')" class="btn btn-primary" title="Add New BOQ Item">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
            </svg>
        </button>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-6">
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Search BOQ items..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div>
                <select id="categoryFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select id="statusFilter" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- BOQ Items Table -->
<div class="card">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="data-table" id="boqTable">
                <thead>
                    <tr>
                        <th class="w-12 text-center">#</th>
                        <th>Actions</th>
                        <th>Item Details</th>
                        <th>Code</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Serial Required</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $serialNo = (($result['page'] - 1) * $result['limit']) + 1;
                    foreach ($result['items'] as $item): 
                    ?>
                    <tr>
                        <td class="text-center text-gray-400 font-mono text-xs"><?php echo $serialNo++; ?></td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <button onclick="viewBoqItem(<?php echo $item['id']; ?>)" class="btn btn-sm btn-secondary" title="View">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                                <button onclick="editBoqItem(<?php echo $item['id']; ?>)" class="btn btn-sm btn-primary" title="Edit">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                    </svg>
                                </button>
                                <button onclick="toggleBoqStatus(<?php echo $item['id']; ?>, '<?php echo $item['status']; ?>')" class="btn btn-sm <?php echo $item['status'] === 'active' ? 'btn-warning' : 'btn-success'; ?>" title="<?php echo $item['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                    <?php if ($item['status'] === 'active'): ?>
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    <?php endif; ?>
                                </button>
                                <button onclick="deleteBoqItem(<?php echo $item['id']; ?>)" class="btn btn-sm btn-danger" title="Delete">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 012 0v4a1 1 0 11-2 0V7zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V7a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <i class="<?php echo $item['icon_class'] ?: 'fas fa-cube'; ?> text-blue-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['item_name']); ?></div>
                                    <?php if ($item['description']): ?>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($item['description'], 0, 60)) . (strlen($item['description']) > 60 ? '...' : ''); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="text-sm text-gray-900 font-mono"><?php echo htmlspecialchars($item['item_code'] ?: 'N/A'); ?></div>
                        </td>
                        <td>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <?php echo htmlspecialchars($item['category'] ?: 'Uncategorized'); ?>
                            </span>
                        </td>
                        <td>
                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($item['unit']); ?></div>
                        </td>
                        <td>
                            <?php if ($item['need_serial_number']): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    Required
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Not Required
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $item['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo ucfirst($item['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($result['pages'] > 1): ?>
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-b-lg mt-4">
            <div class="flex-1 flex justify-between sm:hidden">
                <!-- Mobile Pagination -->
                <?php if ($result['page'] > 1): ?>
                    <a href="?page=<?php echo $result['page'] - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($status) ? '&status=' . urlencode($status) : ''; ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous
                    </a>
                <?php endif; ?>
                <?php if ($result['page'] < $result['pages']): ?>
                    <a href="?page=<?php echo $result['page'] + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($status) ? '&status=' . urlencode($status) : ''; ?>" 
                       class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next
                    </a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium"><?php echo (($result['page'] - 1) * $result['limit']) + 1; ?></span>
                        to
                        <span class="font-medium"><?php echo min($result['page'] * $result['limit'], $result['total']); ?></span>
                        of
                        <span class="font-medium"><?php echo $result['total']; ?></span>
                        results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php
                        $current = $result['page'];
                        $total = $result['pages'];
                        $search_param = !empty($search) ? '&search=' . urlencode($search) : '';
                        $category_param = !empty($category) ? '&category=' . urlencode($category) : '';
                        $status_param = !empty($status) ? '&status=' . urlencode($status) : '';
                        $params = $search_param . $category_param . $status_param;
                        
                        // Previous button
                        if ($current > 1): ?>
                            <a href="?page=<?php echo $current - 1 . $params; ?>" 
                               class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                        <?php else: ?>
                            <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        <?php endif;
                        
                        // Page numbers with smart ellipsis
                        $range = 2; // Show 2 pages on each side of current
                        
                        // Always show first page
                        if ($current > $range + 2) {
                            echo '<a href="?page=1' . $params . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>';
                            if ($current > $range + 3) {
                                echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                            }
                        }
                        
                        // Pages around current
                        for ($i = max(1, $current - $range); $i <= min($total, $current + $range); $i++) {
                            if ($i == $current) {
                                echo '<span class="relative inline-flex items-center px-4 py-2 border border-blue-500 bg-blue-50 text-sm font-medium text-blue-600 z-10">' . $i . '</span>';
                            } else {
                                echo '<a href="?page=' . $i . $params . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">' . $i . '</a>';
                            }
                        }
                        
                        // Always show last page
                        if ($current < $total - $range - 1) {
                            if ($current < $total - $range - 2) {
                                echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                            }
                            echo '<a href="?page=' . $total . $params . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">' . $total . '</a>';
                        }
                        
                        // Next button
                        if ($current < $total): ?>
                            <a href="?page=<?php echo $current + 1 . $params; ?>" 
                               class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                        <?php else: ?>
                            <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create BOQ Item Modal -->
<div id="createBoqModal" class="modal">
    <div class="modal-content-large">
        <div class="modal-header-fixed">
            <h3 class="modal-title">Add New BOQ Item</h3>
            <button type="button" class="modal-close" onclick="closeModal('createBoqModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="createBoqForm" action="create.php" method="POST">
            <div class="modal-body-scrollable">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label for="item_code" class="form-label">Item Code *</label>
                        <input type="text" id="item_code" name="item_code" class="form-input" required placeholder="e.g., CAM-JIO-001">
                    </div>
                    <div class="form-group">
                        <label for="item_name" class="form-label">Item Name *</label>
                        <input type="text" id="item_name" name="item_name" class="form-input" required placeholder="e.g., Jio Security Camera">
                    </div>
                    <div class="form-group md:col-span-2">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-textarea" rows="3" placeholder="Detailed description of the item..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="unit" class="form-label">Unit *</label>
                        <select id="unit" name="unit" class="form-select" required>
                            <option value="">Select Unit</option>
                            <option value="Nos">Nos</option>
                            <option value="Meter">Meter</option>
                            <option value="Pcs">Pcs</option>
                            <option value="Set">Set</option>
                            <option value="Box">Box</option>
                            <option value="Roll">Roll</option>
                            <option value="Kg">Kg</option>
                            <option value="Liter">Liter</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" id="category" name="category" class="form-input" placeholder="e.g., Cameras, Network Devices" list="categoryList">
                        <datalist id="categoryList">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label for="icon_class" class="form-label">Icon Class</label>
                        <input type="text" id="icon_class" name="icon_class" class="form-input" placeholder="e.g., fas fa-video">
                        <p class="text-xs text-gray-500 mt-1">FontAwesome icon class</p>
                    </div>
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group md:col-span-2">
                        <div class="flex items-center">
                            <input type="checkbox" id="need_serial_number" name="need_serial_number" class="form-checkbox">
                            <label for="need_serial_number" class="ml-2 text-sm text-gray-700">Requires Serial Number Tracking</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer-fixed">
                <button type="button" onclick="closeModal('createBoqModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Create BOQ Item</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit BOQ Item Modal -->
<div id="editBoqModal" class="modal">
    <div class="modal-content-large">
        <div class="modal-header-fixed">
            <h3 class="modal-title">Edit BOQ Item</h3>
            <button type="button" class="modal-close" onclick="closeModal('editBoqModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="editBoqForm" method="POST">
            <div class="modal-body-scrollable">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label for="edit_item_code" class="form-label">Item Code *</label>
                        <input type="text" id="edit_item_code" name="item_code" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_item_name" class="form-label">Item Name *</label>
                        <input type="text" id="edit_item_name" name="item_name" class="form-input" required>
                    </div>
                    <div class="form-group md:col-span-2">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea id="edit_description" name="description" class="form-textarea" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_unit" class="form-label">Unit *</label>
                        <select id="edit_unit" name="unit" class="form-select" required>
                            <option value="Nos">Nos</option>
                            <option value="Meter">Meter</option>
                            <option value="Pcs">Pcs</option>
                            <option value="Set">Set</option>
                            <option value="Box">Box</option>
                            <option value="Roll">Roll</option>
                            <option value="Kg">Kg</option>
                            <option value="Liter">Liter</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_category" class="form-label">Category</label>
                        <input type="text" id="edit_category" name="category" class="form-input" list="categoryList">
                    </div>
                    <div class="form-group">
                        <label for="edit_icon_class" class="form-label">Icon Class</label>
                        <input type="text" id="edit_icon_class" name="icon_class" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="edit_status" class="form-label">Status</label>
                        <select id="edit_status" name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group md:col-span-2">
                        <div class="flex items-center">
                            <input type="checkbox" id="edit_need_serial_number" name="need_serial_number" class="form-checkbox">
                            <label for="edit_need_serial_number" class="ml-2 text-sm text-gray-700">Requires Serial Number Tracking</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer-fixed">
                <button type="button" onclick="closeModal('editBoqModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Update BOQ Item</button>
            </div>
        </form>
    </div>
</div>

<!-- View BOQ Item Modal -->
<div id="viewBoqModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">BOQ Item Details</h3>
            <button type="button" class="modal-close" onclick="closeModal('viewBoqModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body" id="viewBoqContent">
            <!-- Content will be loaded dynamically -->
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeModal('viewBoqModal')" class="btn btn-secondary">Close</button>
            <button type="button" onclick="editBoqFromView()" class="btn btn-primary">Edit Item</button>
        </div>
    </div>
</div>

<script>
    
// Ye ek hi jagah define kar do, jaise main.js me ya script ke start me


    
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', debounce(function() {
    applyFilters();
}, 500));

// Filter functionality
document.getElementById('categoryFilter').addEventListener('change', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value;
    const category = document.getElementById('categoryFilter').value;
    const status = document.getElementById('statusFilter').value;
    
    const url = new URL(window.location);
    
    if (searchTerm) url.searchParams.set('search', searchTerm);
    else url.searchParams.delete('search');
    
    if (category) url.searchParams.set('category', category);
    else url.searchParams.delete('category');
    
    if (status) url.searchParams.set('status', status);
    else url.searchParams.delete('status');
    
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

// Create BOQ form submission
document.getElementById('createBoqForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm('createBoqForm', function(data) {
        closeModal('createBoqModal');
        showAlert('BOQ item created successfully!', 'success');
        // showAlert('Item created successfully!', 'success');

        setTimeout(() => location.reload(), 1500);
    });
});

// Edit BOQ form submission
document.getElementById('editBoqForm').addEventListener('submit', function(e) {
    e.preventDefault();
    submitForm('editBoqForm', function(data) {
        closeModal('editBoqModal');
        showAlert('BOQ item updated successfully!', 'success');
        setTimeout(() => location.reload(), 1500);
    });
});

// BOQ management functions
function viewBoqItem(id) {
    fetch(`view.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateViewModal(data.item);
                openModal('viewBoqModal');
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to load BOQ item data', 'error');
        });
}

function editBoqItem(id) {
    fetch(`edit.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateEditModal(data.item);
                document.getElementById('editBoqForm').action = `edit.php?id=${id}`;
                openModal('editBoqModal');
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to load BOQ item data', 'error');
        });
}

function toggleBoqStatus(id, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    const action = newStatus === 'active' ? 'activate' : 'deactivate';
    
    showConfirmDialog(
        `${action.charAt(0).toUpperCase() + action.slice(1)} BOQ Item`,
        `Are you sure you want to ${action} this BOQ item?`,
        function() {
            fetch(`toggle-status.php?id=${id}&status=${newStatus}`, { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Failed to update BOQ item status', 'error');
                });
        }
    );
}

function deleteBoqItem(id) {
    showConfirmDialog(
        'Delete BOQ Item',
        'Are you sure you want to delete this BOQ item? This action cannot be undone.',
        function() {
            fetch(`delete.php?id=${id}`, { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Failed to delete BOQ item', 'error');
                });
        }
    );
}

function exportBOQ() {
    window.open('export.php', '_blank');
}

function populateViewModal(item) {
    const content = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Item Code</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded font-mono">${item.item_code || 'N/A'}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">${item.item_name}</p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded min-h-[60px]">${item.description || 'No description provided'}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">${item.unit}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">${item.category || 'Uncategorized'}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${item.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${item.status.charAt(0).toUpperCase() + item.status.slice(1)}</span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Serial Number Required</label>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${item.need_serial_number ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'}">${item.need_serial_number ? 'Yes' : 'No'}</span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                <div class="flex items-center space-x-2">
                    <i class="${item.icon_class || 'fas fa-cube'} text-blue-600"></i>
                    <span class="text-sm text-gray-900 font-mono">${item.icon_class || 'fas fa-cube'}</span>
                </div>
            </div>
        </div>
    `;
    document.getElementById('viewBoqContent').innerHTML = content;
    window.currentViewingItemId = item.id;
}

function populateEditModal(item) {
    document.getElementById('edit_item_code').value = item.item_code || '';
    document.getElementById('edit_item_name').value = item.item_name || '';
    document.getElementById('edit_description').value = item.description || '';
    document.getElementById('edit_unit').value = item.unit || '';
    document.getElementById('edit_category').value = item.category || '';
    document.getElementById('edit_icon_class').value = item.icon_class || '';
    document.getElementById('edit_status').value = item.status || 'active';
    document.getElementById('edit_need_serial_number').checked = item.need_serial_number == 1;
    window.currentEditingItemId = item.id;
}

function editBoqFromView() {
    if (window.currentViewingItemId) {
        closeModal('viewBoqModal');
        editBoqItem(window.currentViewingItemId);
    }
}

function showConfirmDialog(title, message, onConfirm, onCancel) {
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-[9998] flex items-center justify-center';
    overlay.style.animation = 'fadeIn 0.2s ease-out';
    
    // Create dialog
    const dialog = document.createElement('div');
    dialog.className = 'bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all';
    dialog.style.animation = 'slideIn 0.3s ease-out';
    dialog.innerHTML = `
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">${title}</h3>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-6">${message}</p>
            <div class="flex justify-end space-x-3">
                <button id="cancelBtn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    Cancel
                </button>
                <button id="confirmBtn" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                    Confirm
                </button>
            </div>
        </div>
    `;
    
    overlay.appendChild(dialog);
    document.body.appendChild(overlay);
    
    // Add animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: scale(0.95) translateY(-10px); opacity: 0; }
            to { transform: scale(1) translateY(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
    
    // Handle buttons
    const confirmBtn = dialog.querySelector('#confirmBtn');
    const cancelBtn = dialog.querySelector('#cancelBtn');
    
    const closeDialog = () => {
        overlay.style.animation = 'fadeIn 0.2s ease-out reverse';
        setTimeout(() => {
            document.body.removeChild(overlay);
            document.head.removeChild(style);
        }, 200);
    };
    
    confirmBtn.addEventListener('click', () => {
        closeDialog();
        if (onConfirm) onConfirm();
    });
    
    cancelBtn.addEventListener('click', () => {
        closeDialog();
        if (onCancel) onCancel();
    });
    
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeDialog();
            if (onCancel) onCancel();
        }
    });
}

function submitForm(formId, callback) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            callback(data);
        } else {
            showAlert(data.message || 'Operation failed', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'error');
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>.item_code || '';
    document.getElementById('edit_item_name').value = item.item_name || '';
    document.getElementById('edit_description').value = item.description || '';
    document.getElementById('edit_unit').value = item.unit || 'Nos';
    document.getElementById('edit_category').value = item.category || '';
    document.getElementById('edit_icon_class').value = item.icon_class || '';
    document.getElementById('edit_status').value = item.status || 'active';
    document.getElementById('edit_need_serial_number').checked = item.need_serial_number == 1;
}

function editBoqFromView() {
    if (window.currentViewingItemId) {
        closeModal('viewBoqModal');
        editBoqItem(window.currentViewingItemId);
    }
}

// Utility function for debouncing
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>
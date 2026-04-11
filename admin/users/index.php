<?php
require_once __DIR__ . '/../../controllers/UsersController.php';

$controller = new UsersController();
$data = $controller->index();

$title = 'Users Management';
ob_start();
?>

<div class="mb-4">
    <div class="flex justify-between items-center gap-3">
        <div class="flex items-center gap-2">
            <button onclick="exportUsersData()" class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">
                Export
            </button>
            <button onclick="resetCreateUserForm(); openModal('createUserModal')" class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                Add User
            </button>
            <a href="bulk_upload.php" class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                Bulk Upload
            </a>
        </div>
        <p class="text-xs text-gray-500">Manage system users and their permissions</p>
    </div>
</div>



<!--<div class="mb-6">-->
<!--    <h1 class="text-2xl font-semibold text-gray-900 mb-2">Users Management</h1>-->

<!--    <div class="flex justify-between items-center">-->
<!--        <div class="flex gap-3">-->
<!--            <button onclick="exportUsersData()" class="btn btn-secondary">Export</button>-->
<!--            <button onclick="resetCreateUserForm(); openModal('createUserModal')" class="btn btn-primary">Add User</button>-->
<!--        </div>-->
        
        
<!--         <div class="relative inline-block">-->

<!--            <a href="bulk_upload.php" class="btn btn-secondary">-->
<!--                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">-->
<!--                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>-->
<!--                </svg> Upload Sites In Bulk-->
<!--            </a>-->




<!--            <div id="bulkUploadMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">-->
<!--                <div class="py-1">-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
        
<!--        <br>-->

<!--        <p class="text-sm text-gray-600">Manage system users and their permissions</p>-->
<!--    </div>-->
    
   
    
    
    
    
<!--</div>-->

<!-- Search and Filters -->
<div class="card mb-4">
    <div class="card-body p-3">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <input type="text" id="searchInput" placeholder="Search users..." class="block w-full pl-8 pr-3 py-1.5 text-xs border border-gray-300 rounded leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($data['search']); ?>" onkeyup="filterUsersTable()">
                </div>
            </div>
            <div class="flex gap-2">
                <select id="roleFilter" class="text-xs border border-gray-300 rounded py-1.5 px-2 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="filterUsersTable()">
                    <option value="">All Roles</option>
                    <option value="superadmin" <?php echo (isset($data['role_filter']) && $data['role_filter'] === 'superadmin') ? 'selected' : ''; ?>>Superadmin</option>
                    <option value="admin" <?php echo (isset($data['role_filter']) && $data['role_filter'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="manager" <?php echo (isset($data['role_filter']) && $data['role_filter'] === 'manager') ? 'selected' : ''; ?>>Manager</option>
                    <option value="contractor" <?php echo (isset($data['role_filter']) && $data['role_filter'] === 'contractor') ? 'selected' : ''; ?>>Contractor</option>
                </select>
                <select id="statusFilter" class="text-xs border border-gray-300 rounded py-1.5 px-2 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="filterUsersTable()">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body p-4">
        <div class="overflow-x-auto">
            <table class="data-table text-xs" id="usersTable">
                <thead>
                    <tr>
                        <th class="px-3 py-2 text-xs">#</th>
                        <th class="px-3 py-2 text-xs">Actions</th>
                        <th class="px-3 py-2 text-xs">User</th>
                        <th class="px-3 py-2 text-xs">Contact</th>
                        <th class="px-3 py-2 text-xs">Role</th>
                        <th class="px-3 py-2 text-xs">Status</th>
                        <th class="px-3 py-2 text-xs">Created</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <?php 
                    $serialNo = (($data['pagination']['current_page'] - 1) * $data['pagination']['limit']) + 1;
                    foreach ($data['users'] as $user): 
                    ?>
                        <tr>
                            <td class="px-3 py-2 text-xs text-gray-500 font-medium"><?php echo $serialNo++; ?></td>
                             <td class="px-3 py-2">
                                <div class="flex items-center space-x-1">
                                    <button onclick="viewUser(<?php echo $user['id']; ?>)" class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded" title="View">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <button onclick="editUser(<?php echo $user['id']; ?>)" class="p-1.5 text-green-600 hover:text-green-800 hover:bg-green-50 rounded" title="Edit">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                        </svg>
                                    </button>

                                    <a href="<?php echo BASE_URL; ?>/admin/users/assign-role.php?user_id=<?php echo $user['id']; ?>" class="p-1.5 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded" title="Assign Role">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"></path>
                                        </svg>
                                    </a>

                                    <a href="<?php echo BASE_URL; ?>/admin/users/manage-permissions.php?user_id=<?php echo $user['id']; ?>" class="p-1.5 text-purple-600 hover:text-purple-800 hover:bg-purple-50 rounded" title="Manage Permissions">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                                        </svg>
                                    </a>

                                    <button onclick="toggleUserStatus(<?php echo $user['id']; ?>)" class="p-1.5 text-yellow-600 hover:text-yellow-800 hover:bg-yellow-50 rounded" title="<?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                        <?php if ($user['status'] === 'active'): ?>
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        <?php else: ?>
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        <?php endif; ?>
                                    </button>
                                    <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded" title="Delete">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            
                            <td class="px-3 py-2">
                                <div class="flex items-center">
                                    <div class="w-7 h-7 bg-primary-600 rounded-full flex items-center justify-center text-white text-xs font-medium mr-2">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="text-xs font-medium text-gray-900"><?php echo htmlspecialchars($user['username']); ?></div>
                                        <div class="text-xs text-gray-500">ID: <?php echo $user['id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="text-xs text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-3 py-2">
                                <?php if (!empty($user['rbac_role_display'])): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                        <?php 
                                        echo $user['rbac_role'] === 'superadmin' ? 'bg-purple-100 text-purple-800' : 
                                             ($user['rbac_role'] === 'admin' ? 'bg-blue-100 text-blue-800' : 
                                             ($user['rbac_role'] === 'manager' ? 'bg-green-100 text-green-800' : 
                                             'bg-gray-100 text-gray-800')); 
                                        ?>">
                                        <?php echo htmlspecialchars($user['rbac_role_display']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                    <div class="text-xs text-red-500 mt-0.5">
                                        No RBAC role
                                    </div>
                                <?php endif; ?>
                                <?php if ($user['role'] === 'vendor' && !empty($user['vendor_name'])): ?>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        <?php echo htmlspecialchars($user['vendor_name']); ?>
                                    </div>
                                <?php elseif ($user['role'] === 'vendor' && !empty($user['vendor_id'])): ?>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        Vendor ID: <?php echo $user['vendor_id']; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-500">
                                <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($data['pagination']['total_pages'] > 1): ?>
            <div class="flex items-center justify-between border-t border-gray-200 bg-white px-3 py-2 mt-3">
                <div class="text-xs text-gray-700">
                    Showing <?php echo (($data['pagination']['current_page'] - 1) * $data['pagination']['limit']) + 1; ?> to
                    <?php echo min($data['pagination']['current_page'] * $data['pagination']['limit'], $data['pagination']['total_records']); ?> of
                    <?php echo $data['pagination']['total_records']; ?> results
                </div>
                <nav class="flex space-x-1" id="paginationNav">
                    <?php 
                    $roleFilter = isset($_GET['role']) ? $_GET['role'] : '';
                    $statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
                    for ($i = 1; $i <= $data['pagination']['total_pages']; $i++): 
                    ?>
                        <a href="?page=<?php echo $i; ?><?php echo !empty($data['search']) ? '&search=' . urlencode($data['search']) : ''; ?><?php echo !empty($roleFilter) ? '&role=' . urlencode($roleFilter) : ''; ?><?php echo !empty($statusFilter) ? '&status=' . urlencode($statusFilter) : ''; ?>"
                            class="px-2.5 py-1 text-xs font-medium rounded <?php echo $i === $data['pagination']['current_page'] ? 'bg-blue-600 text-white' : 'text-gray-600 bg-white border border-gray-300 hover:bg-gray-50'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create User Modal -->
<div id="createUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New User</h3>
            <button type="button" class="modal-close" onclick="closeModal('createUserModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="createUserForm" action="create.php" method="POST">
            <div class="modal-body">
                <!-- Row 1: Username and Email -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>
                </div>

                <!-- Row 2: Phone and Password -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-input" required>
                    </div>
                </div>

                <!-- Row 3: Role and Status -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="role" class="form-label">Role</label>
                        <select id="role" name="role" class="form-select" required onchange="toggleVendorField(this.value)">
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="vendor">Vendor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Vendor Field (Full Width when visible) -->
                <div class="form-group" id="vendor_field" style="display: none;">
                    <label for="vendor_id" class="form-label">Select Vendor *</label>
                    <select id="vendor_id" name="vendor_id" class="form-select">
                        <option value="">Choose Vendor</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('createUserModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit User</h3>
            <button type="button" class="modal-close" onclick="closeModal('editUserModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="editUserForm" method="POST">
            <div class="modal-body">
                <!-- Row 1: Username and Email -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="edit_username" class="form-label">Username</label>
                        <input type="text" id="edit_username" name="username" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" id="edit_email" name="email" class="form-input" required>
                    </div>
                </div>

                <!-- Row 2: Phone and Password -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="edit_phone" class="form-label">Phone Number</label>
                        <input type="tel" id="edit_phone" name="phone" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_password" class="form-label">Password</label>
                        <input type="password" id="edit_password" name="password" class="form-input">
                    </div>
                </div>

                <!-- Row 3: Role and Status -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="edit_role" class="form-label">Role</label>
                        <select id="edit_role" name="role" class="form-select" required onchange="toggleEditVendorField(this.value)">
                            <option value="admin">Admin</option>
                            <option value="vendor">Vendor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_status" class="form-label">Status</label>
                        <select id="edit_status" name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Vendor Field (Full Width when visible) -->
                <div class="form-group" id="edit_vendor_field" style="display: none;">
                    <label for="edit_vendor_id" class="form-label">Select Vendor *</label>
                    <select id="edit_vendor_id" name="vendor_id" class="form-select">
                        <option value="">Choose Vendor</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('editUserModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>

<!-- View User Modal -->
<div id="viewUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">User Details</h3>
            <button type="button" class="modal-close" onclick="closeModal('viewUserModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <p id="view_username" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <p id="view_email" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <p id="view_phone" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <p id="view_role" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                </div>
                <div id="view_vendor_info" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                    <p id="view_vendor_name" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <p id="view_status" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plain Password</label>
                    <p id="view_plain_password" class="text-sm text-gray-900 bg-gray-50 p-2 rounded font-mono"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Created At</label>
                    <p id="view_created_at" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Updated</label>
                    <p id="view_updated_at" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                </div>
            </div>
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">JWT Token</label>
                <div class="bg-gray-50 p-2 rounded">
                    <p id="view_jwt_token" class="text-xs text-gray-600 font-mono break-all"></p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeModal('viewUserModal')" class="btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<script>
    // Export users data to CSV
    function exportUsersData() {
        const searchInput = document.getElementById('searchInput');
        const roleFilter = document.getElementById('roleFilter');
        const statusFilter = document.getElementById('statusFilter');

        const params = new URLSearchParams();
        if (searchInput && searchInput.value) params.append('search', searchInput.value);
        if (roleFilter && roleFilter.value) params.append('role', roleFilter.value);
        if (statusFilter && statusFilter.value) params.append('status', statusFilter.value);

        const exportUrl = `export-users.php?${params.toString()}`;
        window.open(exportUrl, '_blank');
    }

    // Debounce timer for search
    let searchTimer = null;

    // Server-side search with debounce
    function filterUsersTable() {
        const searchInput = document.getElementById('searchInput');
        const roleFilter = document.getElementById('roleFilter');
        const statusFilter = document.getElementById('statusFilter');

        const searchTerm = searchInput ? searchInput.value : '';
        const roleValue = roleFilter ? roleFilter.value : '';
        const statusValue = statusFilter ? statusFilter.value : '';

        // Clear previous timer
        if (searchTimer) {
            clearTimeout(searchTimer);
        }

        // Debounce search to avoid too many requests
        searchTimer = setTimeout(() => {
            const params = new URLSearchParams();
            params.append('page', '1'); // Reset to page 1 on search
            if (searchTerm) params.append('search', searchTerm);
            if (roleValue) params.append('role', roleValue);
            if (statusValue) params.append('status', statusValue);

            window.location.href = '?' + params.toString();
        }, 500);
    }

    // For dropdown filters, apply immediately
    document.getElementById('roleFilter').addEventListener('change', function() {
        if (searchTimer) clearTimeout(searchTimer);
        applyFilters();
    });

    document.getElementById('statusFilter').addEventListener('change', function() {
        if (searchTimer) clearTimeout(searchTimer);
        applyFilters();
    });

    function applyFilters() {
        const searchInput = document.getElementById('searchInput');
        const roleFilter = document.getElementById('roleFilter');
        const statusFilter = document.getElementById('statusFilter');

        const params = new URLSearchParams();
        params.append('page', '1');
        if (searchInput && searchInput.value) params.append('search', searchInput.value);
        if (roleFilter && roleFilter.value) params.append('role', roleFilter.value);
        if (statusFilter && statusFilter.value) params.append('status', statusFilter.value);

        window.location.href = '?' + params.toString();
    }

    // Set filter values from URL on page load
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const roleFilter = document.getElementById('roleFilter');
        const statusFilter = document.getElementById('statusFilter');
        
        if (urlParams.get('role') && roleFilter) {
            roleFilter.value = urlParams.get('role');
        }
        if (urlParams.get('status') && statusFilter) {
            statusFilter.value = urlParams.get('status');
        }
    });



    // Reset create user form when modal opens
    function resetCreateUserForm() {
        const form = document.getElementById('createUserForm');
        form.reset();

        // Reset vendor field visibility
        const vendorField = document.getElementById('vendor_field');
        const vendorSelect = document.getElementById('vendor_id');
        vendorField.style.display = 'none';
        vendorSelect.required = false;
        vendorSelect.value = '';

        // Reset role selection
        document.getElementById('role').value = '';
    }

    // Create user form submission
    document.getElementById('createUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm('createUserForm', function(data) {
            closeModal('createUserModal');
            location.reload();
        });
    });

    // Edit user form submission
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm('editUserForm', function(data) {
            closeModal('editUserModal');
            location.reload();
        });
    });

    // User management functions
    function viewUser(id) {
        fetch(`view.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const user = data.user;
                    document.getElementById('view_username').textContent = user.username;
                    document.getElementById('view_email').textContent = user.email;
                    document.getElementById('view_phone').textContent = user.phone || 'N/A';
                    document.getElementById('view_role').textContent = user.role.charAt(0).toUpperCase() + user.role.slice(1);
                    document.getElementById('view_status').textContent = user.status.charAt(0).toUpperCase() + user.status.slice(1);
                    document.getElementById('view_plain_password').textContent = user.plain_password || 'N/A';
                    document.getElementById('view_created_at').textContent = formatDate(user.created_at);
                    document.getElementById('view_updated_at').textContent = formatDate(user.updated_at);
                    document.getElementById('view_jwt_token').textContent = user.jwt_token || 'No token generated';

                    // Show vendor information if user is a vendor
                    const vendorInfo = document.getElementById('view_vendor_info');
                    if (user.role === 'vendor' && user.vendor_name) {
                        document.getElementById('view_vendor_name').textContent = user.vendor_name;
                        vendorInfo.style.display = 'block';
                    } else {
                        vendorInfo.style.display = 'none';
                    }

                    openModal('viewUserModal');
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('Failed to load user data', 'error');
            });
    }

    function editUser(id) {
        fetch(`edit.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const user = data.user;
                    document.getElementById('edit_username').value = user.username;
                    document.getElementById('edit_email').value = user.email;
                    document.getElementById('edit_phone').value = user.phone || '';
                    document.getElementById('edit_password').value = '';
                    document.getElementById('edit_role').value = user.role;
                    document.getElementById('edit_status').value = user.status;

                    // Handle vendor field
                    toggleEditVendorField(user.role);
                    if (user.role === 'vendor' && user.vendor_id) {
                        // Wait for vendors to load, then set the value
                        setTimeout(() => {
                            document.getElementById('edit_vendor_id').value = user.vendor_id;
                        }, 500);
                    }

                    document.getElementById('editUserForm').action = `edit.php?id=${id}`;
                    openModal('editUserModal');
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('Failed to load user data', 'error');
            });
    }

    function toggleUserStatus(id) {
        confirmAction('Are you sure you want to change this user\'s status?', function() {
            fetch(`toggle_status.php?id=${id}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        location.reload();
                    } else {
                        showAlert(data.message, 'error');
                    }
                });
        });
    }

    function deleteUser(id) {
        confirmAction('Are you sure you want to delete this user? This action cannot be undone.', function() {
            fetch(`delete.php?id=${id}`, {
                    method: 'POST'
                })
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
                    showAlert('Failed to delete user', 'error');
                });
        });
    }

    // Utility function to format dates
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }

    // Toggle vendor field visibility based on role selection
    function toggleVendorField(role) {
        const vendorField = document.getElementById('vendor_field');
        const vendorSelect = document.getElementById('vendor_id');

        if (role === 'vendor') {
            vendorField.style.display = 'block';
            vendorSelect.required = true;
            loadVendors('vendor_id');
        } else {
            vendorField.style.display = 'none';
            vendorSelect.required = false;
            vendorSelect.value = '';
        }
    }

    function toggleEditVendorField(role) {
        const vendorField = document.getElementById('edit_vendor_field');
        const vendorSelect = document.getElementById('edit_vendor_id');

        if (role === 'vendor') {
            vendorField.style.display = 'block';
            vendorSelect.required = true;
            loadVendors('edit_vendor_id');
        } else {
            vendorField.style.display = 'none';
            vendorSelect.required = false;
            vendorSelect.value = '';
        }
    }

    // Load vendors from API
    function loadVendors(selectId) {
        const vendorSelect = document.getElementById(selectId);
        if (!vendorSelect) return;

        // Show loading state
        vendorSelect.innerHTML = '<option value="">Loading vendors...</option>';

        // Request all vendors with a high limit
        fetch('../../api/masters.php?path=vendors&status=active&limit=1000')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.records) {
                    vendorSelect.innerHTML = '<option value="">Choose Vendor</option>';
                    data.data.records.forEach(vendor => {
                        vendorSelect.innerHTML += `<option value="${vendor.id}">${vendor.name}</option>`;
                    });
                } else {
                    throw new Error('Invalid API response');
                }
            })
            .catch(error => {
                fetch('../vendors/get-vendor.php?action=list&limit=1000')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.vendors) {
                            vendorSelect.innerHTML = '<option value="">Choose Vendor</option>';
                            data.vendors.forEach(vendor => {
                                vendorSelect.innerHTML += `<option value="${vendor.id}">${vendor.name}</option>`;
                            });
                        } else {
                            vendorSelect.innerHTML = '<option value="">No vendors available</option>';
                        }
                    })
                    .catch(err => {
                        vendorSelect.innerHTML = '<option value="">Error loading vendors</option>';
                    });
            });
    }

    // Utility functions for alerts and confirmations
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg ${
        type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' :
        type === 'error' ? 'bg-red-100 border border-red-400 text-red-700' :
        'bg-blue-100 border border-blue-400 text-blue-700'
    }`;
        alertDiv.textContent = message;

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 3000);
    }

    function confirmAction(message, callback) {
        if (confirm(message)) {
            callback();
        }
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
                    showAlert(data.message, 'success');
                    callback(data);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('An error occurred', 'error');
            });
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>
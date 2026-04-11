<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/User.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);
$currentUser = Auth::getCurrentUser();

$userModel = new User();
$user = $userModel->findWithVendor($currentUser['id']);

$title = 'My Profile';
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $updateData = [
                    'username' => trim($_POST['username']),
                    'email' => trim($_POST['email']),
                    'phone' => trim($_POST['phone']),
                    'first_name' => trim($_POST['first_name'] ?? ''),
                    'last_name' => trim($_POST['last_name'] ?? ''),
                    'bio' => trim($_POST['bio'] ?? ''),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Validate data
                $errors = [];
                
                if (empty($updateData['username'])) {
                    $errors[] = 'Username is required';
                } elseif (strlen($updateData['username']) < 3) {
                    $errors[] = 'Username must be at least 3 characters';
                }
                
                if (empty($updateData['email'])) {
                    $errors[] = 'Email is required';
                } elseif (!filter_var($updateData['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Invalid email format';
                }
                
                if (empty($updateData['phone'])) {
                    $errors[] = 'Phone number is required';
                }
                
                // Check for duplicates (excluding current user)
                if (empty($errors)) {
                    $existingUser = $userModel->findByUsername($updateData['username']);
                    if ($existingUser && $existingUser['id'] != $user['id']) {
                        $errors[] = 'Username already exists';
                    }
                    
                    $existingUser = $userModel->findByEmail($updateData['email']);
                    if ($existingUser && $existingUser['id'] != $user['id']) {
                        $errors[] = 'Email already exists';
                    }
                    
                    $existingUser = $userModel->findByPhone($updateData['phone']);
                    if ($existingUser && $existingUser['id'] != $user['id']) {
                        $errors[] = 'Phone number already exists';
                    }
                }
                
                if (empty($errors)) {
                    if ($userModel->update($user['id'], $updateData)) {
                        $message = 'Profile updated successfully!';
                        $messageType = 'success';
                        // Refresh user data
                        $user = $userModel->findWithVendor($currentUser['id']);
                        // Update session
                        Auth::updateSession($user);
                    } else {
                        $message = 'Failed to update profile. Please try again.';
                        $messageType = 'error';
                    }
                } else {
                    $message = implode('<br>', $errors);
                    $messageType = 'error';
                }
                break;
                
            case 'change_password':
                $currentPassword = $_POST['current_password'];
                $newPassword = $_POST['new_password'];
                $confirmPassword = $_POST['confirm_password'];
                
                $errors = [];
                
                if (empty($currentPassword)) {
                    $errors[] = 'Current password is required';
                } elseif (!password_verify($currentPassword, $user['password_hash'])) {
                    $errors[] = 'Current password is incorrect';
                }
                
                if (empty($newPassword)) {
                    $errors[] = 'New password is required';
                } elseif (strlen($newPassword) < 6) {
                    $errors[] = 'New password must be at least 6 characters';
                }
                
                if ($newPassword !== $confirmPassword) {
                    $errors[] = 'Password confirmation does not match';
                }
                
                if (empty($errors)) {
                    $passwordData = [
                        'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                        'plain_password' => $newPassword,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    if ($userModel->update($user['id'], $passwordData)) {
                        $message = 'Password changed successfully!';
                        $messageType = 'success';
                    } else {
                        $message = 'Failed to change password. Please try again.';
                        $messageType = 'error';
                    }
                } else {
                    $message = implode('<br>', $errors);
                    $messageType = 'error';
                }
                break;
        }
    }
}

ob_start();
?>

<div class="max-w-4xl mx-auto">
    <!-- Profile Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg mb-6 overflow-hidden">
        <div class="px-6 py-8">
            <div class="flex items-center space-x-6">
                <div class="relative">
                    <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-lg">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?php echo BASE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                 alt="Profile Picture" class="w-24 h-24 rounded-full object-cover">
                        <?php else: ?>
                            <span class="text-3xl font-bold text-blue-600">
                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <button onclick="openModal('uploadPictureModal')" 
                            class="absolute -bottom-2 -right-2 bg-blue-500 hover:bg-blue-600 text-white rounded-full p-2 shadow-lg transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-white mb-2">
                        <?php echo htmlspecialchars($user['first_name'] ?? $user['username']); ?>
                        <?php echo htmlspecialchars($user['last_name'] ?? ''); ?>
                    </h1>
                    <p class="text-blue-100 text-lg mb-2">@<?php echo htmlspecialchars($user['username']); ?></p>
                    <div class="flex items-center space-x-4 text-blue-100">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                            Joined <?php echo date('M Y', strtotime($user['created_at'])); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Information -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">Profile Information</h2>
                        <button onclick="toggleEdit()" id="editBtn" class="btn btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                            </svg>
                            Edit Profile
                        </button>
                    </div>

                    <form id="profileForm" method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-input" 
                                       value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-input" 
                                       value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" disabled>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-input" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required disabled>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-input" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required disabled>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone *</label>
                                <input type="tel" name="phone" class="form-input" 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>" required disabled>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" rows="4" class="form-input" 
                                      placeholder="Tell us about yourself..." disabled><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>

                        <div id="formActions" class="flex justify-end space-x-3" style="display: none;">
                            <button type="button" onclick="cancelEdit()" class="btn btn-secondary">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Account Details -->
            <div class="card">
                <div class="card-body">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Details</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">Role</span>
                            <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-info' : 'badge-secondary'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">Status</span>
                            <span class="badge <?php echo $user['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-600">Member Since</span>
                            <span class="text-sm text-gray-900"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-sm text-gray-600">Last Updated</span>
                            <span class="text-sm text-gray-900"><?php echo date('M j, Y', strtotime($user['updated_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security -->
            <div class="card">
                <div class="card-body">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Security</h3>
                    <button onclick="openModal('changePasswordModal')" class="btn btn-secondary w-full">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                        Change Password
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div id="changePasswordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Change Password</h3>
            <button type="button" class="modal-close" onclick="closeModal('changePasswordModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="change_password">
            <div class="modal-body">
                <div class="space-y-4">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-input" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-input" required minlength="6">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('changePasswordModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Change Password</button>
            </div>
        </form>
    </div>
</div>

<!-- Upload Picture Modal -->
<div id="uploadPictureModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Upload Profile Picture</h3>
            <button type="button" class="modal-close" onclick="closeModal('uploadPictureModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="text-center">
                    <div class="mb-4">
                        <div class="w-32 h-32 mx-auto bg-gray-100 rounded-full flex items-center justify-center border-2 border-dashed border-gray-300">
                            <div id="preview-container">
                                <?php if (!empty($user['profile_picture'])): ?>
                                    <img id="preview-image" src="<?php echo BASE_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                         alt="Preview" class="w-32 h-32 rounded-full object-cover">
                                <?php else: ?>
                                    <div id="preview-placeholder" class="text-gray-400">
                                        <svg class="w-12 h-12 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                        </svg>
                                        <p class="text-sm">No image</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="hidden" onchange="previewImage(this)">
                        <label for="profile_picture" class="btn btn-primary cursor-pointer">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                            </svg>
                            Choose Image
                        </label>
                    </div>
                    <p class="text-sm text-gray-500">JPG, PNG or GIF. Max size 2MB.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('uploadPictureModal')" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="uploadProfilePicture()" class="btn btn-primary">Upload</button>
            </div>
        </form>
    </div>
</div>

<script>
// Profile editing functionality
function toggleEdit() {
    const form = document.getElementById('profileForm');
    const inputs = form.querySelectorAll('input, textarea');
    const editBtn = document.getElementById('editBtn');
    const formActions = document.getElementById('formActions');
    
    inputs.forEach(input => {
        if (input.name !== 'action') {
            input.disabled = !input.disabled;
        }
    });
    
    if (inputs[0].disabled) {
        editBtn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>Edit Profile';
        formActions.style.display = 'none';
    } else {
        editBtn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>Cancel';
        formActions.style.display = 'flex';
    }
}

function cancelEdit() {
    location.reload();
}

// Image preview functionality
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const container = document.getElementById('preview-container');
            container.innerHTML = `<img id="preview-image" src="${e.target.result}" alt="Preview" class="w-32 h-32 rounded-full object-cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Upload profile picture
function uploadProfilePicture() {
    const formData = new FormData();
    const fileInput = document.getElementById('profile_picture');
    
    if (!fileInput.files[0]) {
        showAlert('Please select an image first', 'error');
        return;
    }
    
    formData.append('profile_picture', fileInput.files[0]);
    formData.append('action', 'upload_picture');
    
    fetch('upload-profile-picture.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Profile picture updated successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message || 'Failed to upload image', 'error');
        }
    })
    .catch(error => {
        showAlert('An error occurred while uploading', 'error');
    });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/admin_layout.php';
?>
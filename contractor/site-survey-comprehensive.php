<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../models/SiteDelegation.php';
require_once __DIR__ . '/../models/SiteSurvey.php';

// Require vendor authentication
Auth::requireVendor();

$siteId = $_GET['id'] ?? null;
$delegationId = $_GET['delegation_id'] ?? null;

if (!$siteId) {
    header('Location: sites/');
    exit;
}

$vendorId = Auth::getVendorId();
$siteModel = new Site();
$delegationModel = new SiteDelegation();
$surveyModel = new SiteSurvey();

// Get site details
$site = $siteModel->findWithRelations($siteId);
if (!$site) {
    header('Location: sites/');
    exit;
}

// Check if vendor has access to this site
if ($delegationId) {
    $delegation = $delegationModel->find($delegationId);
    if (!$delegation || $delegation['vendor_id'] != $vendorId) {
        header('Location: sites/');
        exit;
    }
} else {
    // Find active delegation for this site and vendor
    $delegation = $delegationModel->findBySiteAndVendor($siteId, $vendorId);
    if (!$delegation) {
        header('Location: sites/');
        exit;
    }
    $delegationId = $delegation['id'];
}

// Check if survey already exists
$existingSurvey = $surveyModel->findBySiteAndVendor($siteId, $vendorId);

$title = 'Site Survey - ' . $site['site_id'];
ob_start();
?>

<!-- Header Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">Site Feasibility Survey</h1>
            <p class="mt-2 text-lg text-gray-600">Site: <span class="font-semibold text-blue-600"><?php echo htmlspecialchars($site['site_id']); ?></span></p>
            <p class="text-sm text-gray-500 mt-1">Complete comprehensive site assessment</p>
        </div>
        <div class="mt-6 lg:mt-0 lg:ml-6">
            <a href="sites/" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
                Back to Sites
            </a>
        </div>
    </div>
</div>

<!-- Site Information -->
<div class="professional-table bg-white mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Site Information</h3>
        <p class="text-sm text-gray-500 mt-1">Basic details about the installation site</p>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Site ID</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md"><?php echo htmlspecialchars($site['site_id']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md"><?php echo htmlspecialchars($site['location']); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md"><?php echo htmlspecialchars($site['city_name'] ?: 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md"><?php echo htmlspecialchars($site['customer_name'] ?: 'N/A'); ?></p>
            </div>
        </div>
    </div>
</div>

<?php if ($existingSurvey): ?>
<!-- Existing Survey Status -->
<div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
    <div class="flex items-start">
        <svg class="w-6 h-6 text-blue-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
        </svg>
        <div class="flex-1">
            <h4 class="text-lg font-medium text-blue-800">Survey Already Submitted</h4>
            <p class="text-sm text-blue-600 mt-1">
                You have already submitted a survey for this site on <?php echo date('M d, Y', strtotime($existingSurvey['submitted_date'])); ?>.
                Status: <span class="font-semibold"><?php echo ucfirst($existingSurvey['survey_status']); ?></span>
            </p>
            <div class="mt-4">
                <a href="../shared/view-survey.php?id=<?php echo $existingSurvey['id']; ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200">
                    View Survey Details
                </a>
            </div>
        </div>
    </div>
</div>
<?php else: ?>

<!-- Comprehensive Survey Form -->
<div class="professional-table bg-white">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Site Feasibility Survey</h3>
        <p class="text-sm text-gray-500 mt-1">Complete the comprehensive site assessment form</p>
    </div>
    <div class="p-6">
        <form id="surveyForm" action="process-survey.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="site_id" value="<?php echo $site['id']; ?>">
            <input type="hidden" name="delegation_id" value="<?php echo $delegationId; ?>">
            
            <!-- Check In Section -->
            <div class="mb-8">
                <h4 class="text-lg font-semibold text-blue-600 mb-4">Check In</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label for="checkin_datetime" class="form-label">Date & Time</label>
                        <input type="datetime-local" id="checkin_datetime" name="checkin_datetime" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="working_hours" class="form-label">Working Hours</label>
                        <input type="text" id="working_hours" name="working_hours" class="form-input" placeholder="e.g. 6 hours" required>
                    </div>
                </div>
            </div>

            <!-- Site Selection -->
            <div class="form-group mb-6">
                <label class="form-label">Assigned Site</label>
                <input type="text" class="form-input bg-gray-50" value="<?php echo htmlspecialchars($site['site_id']); ?>" disabled>
            </div>

            <!-- Store Model -->
            <div class="form-group mb-6">
                <label for="store_model" class="form-label">Store Model</label>
                <input type="text" id="store_model" name="store_model" class="form-input" placeholder="Enter store model" required>
            </div>

            <!-- Floor Height -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group">
                    <label for="floor_height" class="form-label">Floor Height (in feet)</label>
                    <input type="number" step="0.1" id="floor_height" name="floor_height" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="floor_height_photos" class="form-label">Floor Height Photo</label>
                    <input type="file" id="floor_height_photos" name="floor_height_photo[]" class="form-input" accept="image/*" multiple>
                    <div class="preview mt-2 flex flex-wrap gap-2"></div>
                </div>
            </div>

            <!-- Ceiling Type -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group">
                    <label for="ceiling_type" class="form-label">Type of Ceiling</label>
                    <select id="ceiling_type" name="ceiling_type" class="form-select" required>
                        <option value="">Select</option>
                        <option value="False">False</option>
                        <option value="Open">Open</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ceiling_photos" class="form-label">Ceiling Photo</label>
                    <input type="file" id="ceiling_photos" name="ceiling_photo[]" class="form-input" multiple accept="image/*">
                    <div class="preview mt-2 flex flex-wrap gap-2"></div>
                </div>
            </div>

            <!-- Camera Counts -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group">
                    <label for="total_cameras" class="form-label">Total Number of Cameras</label>
                    <input type="number" id="total_cameras" name="total_cameras" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="analytic_cameras" class="form-label">Number of Analytic Cameras</label>
                    <input type="number" id="analytic_cameras" name="analytic_cameras" class="form-input">
                </div>
            </div>

            <!-- Analytic Camera Photos -->
            <div class="form-group mb-6">
                <label for="analytic_photos" class="form-label">Analytic Cameras - Snapshots (max 5)</label>
                <input type="file" id="analytic_photos" name="analytic_photos[]" class="form-input" multiple accept="image/*">
                <div class="preview mt-2 flex flex-wrap gap-2"></div>
            </div>

            <!-- Existing POE Rack -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group">
                    <label for="existing_poe_rack" class="form-label">Existing POE Rack (count)</label>
                    <input type="number" id="existing_poe_rack" name="existing_poe_rack" class="form-input">
                </div>
                <div class="form-group">
                    <label for="existing_poe_photos" class="form-label">Existing POE Rack Photos (max 5)</label>
                    <input type="file" id="existing_poe_photos" name="existing_poe_photos[]" class="form-input" multiple accept="image/*">
                    <div class="preview mt-2 flex flex-wrap gap-2"></div>
                </div>
            </div>

            <!-- Space for New Rack -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group">
                    <label for="space_new_rack" class="form-label">Space for New Rack (Server Room)</label>
                    <select id="space_new_rack" name="space_new_rack" class="form-select" required>
                        <option value="">Select</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="space_new_rack_photos" class="form-label">Photos (max 5)</label>
                    <input type="file" id="space_new_rack_photos" name="space_new_rack_photo[]" class="form-input" multiple accept="image/*">
                    <div class="preview mt-2 flex flex-wrap gap-2"></div>
                </div>
            </div>

            <!-- New POE Rack -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group">
                    <label for="new_poe_rack" class="form-label">New POE Rack Recommended (count)</label>
                    <input type="number" id="new_poe_rack" name="new_poe_rack" class="form-input">
                </div>
                <div class="form-group">
                    <label for="new_poe_photos" class="form-label">New POE Rack Photos (max 5)</label>
                    <input type="file" id="new_poe_photos" name="new_poe_photos[]" class="form-input" multiple accept="image/*">
                    <div class="preview mt-2 flex flex-wrap gap-2"></div>
                </div>
            </div>

            <!-- Zones -->
            <div class="form-group mb-6">
                <label for="zones_recommended" class="form-label">Number of Zones Recommended</label>
                <input type="number" id="zones_recommended" name="zones_recommended" class="form-input">
            </div>

            <!-- Material Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group">
                    <label for="rrl_delivery_status" class="form-label">RRL Materials Delivered?</label>
                    <select id="rrl_delivery_status" name="rrl_delivery_status" class="form-select">
                        <option value="">Select</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="rrl_photos" class="form-label">RRL Delivery Photos (max 5)</label>
                    <input type="file" id="rrl_photos" name="rrl_photos[]" class="form-input" multiple accept="image/*">
                    <div class="preview mt-2 flex flex-wrap gap-2"></div>
                </div>
            </div>

            <!-- KPTL Material -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="form-group">
                    <label for="kptl_space" class="form-label">Space for KPTL Materials?</label>
                    <select id="kptl_space" name="kptl_space" class="form-select">
                        <option value="">Select</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="kptl_photos" class="form-label">KPTL Material Photos (max 5)</label>
                    <input type="file" id="kptl_photos" name="kptl_photos[]" class="form-input" multiple accept="image/*">
                    <div class="preview mt-2 flex flex-wrap gap-2"></div>
                </div>
            </div>

            <!-- Technical Remarks -->
            <div class="form-group mb-6">
                <label for="technical_remarks" class="form-label">Technical Remarks</label>
                <textarea id="technical_remarks" name="technical_remarks" class="form-textarea" rows="4" 
                          placeholder="Provide detailed technical observations and findings..." required></textarea>
            </div>

            <!-- Check Out -->
            <div class="mb-8">
                <h4 class="text-lg font-semibold text-blue-600 mb-4">Check Out</h4>
                <div class="form-group">
                    <label for="checkout_datetime" class="form-label">Check Out Date & Time</label>
                    <input type="datetime-local" id="checkout_datetime" name="checkout_datetime" class="form-input" required>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4">
                <a href="sites/" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Submit Feasibility
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Set default check-in time to now
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const checkinInput = document.getElementById('checkin_datetime');
    if (checkinInput) {
        checkinInput.value = now.toISOString().slice(0, 16);
    }
});

// Form submission
document.getElementById('surveyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner mr-2"></span>Submitting...';
    
    fetch('process-survey.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => {
                window.location.href = 'surveys.php';
            }, 2000);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Image preview functionality
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function() {
        const preview = this.parentNode.querySelector('.preview');
        if (preview) {
            preview.innerHTML = '';
            Array.from(this.files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.className = 'w-20 h-20 object-cover rounded border';
                    preview.appendChild(img);
                }
            });
        }
    });
});
</script>

<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>
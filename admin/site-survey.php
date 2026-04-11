<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../models/SiteDelegation.php';
require_once __DIR__ . '/../models/SiteSurvey.php';

// Require vendor authentication
//Auth::requireVendor();

$siteId = $_GET['delegation_id'] ?? null;
if (!$siteId) {
    header('Location: sites/');
    exit;
}

$vendorId = Auth::getVendorId();
$siteModel = new Site();
$delegationModel = new SiteDelegation();
$surveyModel = new SiteSurvey();

// Get delegation details
$delegationId =  $delegationModel->findDelegationId($siteId);
$_REQUEST['delegation_id'];

//echo '<pre>';print_r($delegation);echo '</pre>';die;

// $delegation = $delegationModel->find($delegationId);
// if (!$delegation || $delegation['vendor_id'] != $vendorId) {
//     header('Location: sites/');
//     exit;
// }

// Get site details
$site = $siteModel->findWithRelations($siteId);
//echo '<pre>';print_r($site);echo '</pre>';die;
if (!$site) {
    header('Location: sites/');
    exit;
}

// Check if survey already exists
$existingSurveys = $surveyModel->findByDelegation($delegationId);
$existingSurvey = !empty($existingSurveys) ? $existingSurveys[0] : null;

$title = 'Site Survey - ' . $site['site_id'];
ob_start();
?>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">Site Feasibility Survey</h1>
            <p class="mt-2 text-lg text-gray-600">Site: <span class="font-semibold text-blue-600"><?php echo htmlspecialchars($site['site_id']); ?></span></p>
            <p class="text-sm text-gray-500 mt-1">Complete comprehensive feasibility assessment for installation</p>
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
<div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
    <div class="flex items-start">
        <svg class="w-6 h-6 text-blue-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
        </svg>
        <div class="flex-1">
            <h4 class="text-lg font-medium text-blue-800">Survey Already Submitted</h4>
            <p class="text-sm text-blue-600 mt-1">
                You have already submitted a survey for this site on <?php echo isset($existingSurvey['submitted_date']) ? date('M d, Y', strtotime($existingSurvey['submitted_date'])) : 'Unknown date'; ?>.
                Status: <span class="font-semibold"><?php echo isset($existingSurvey['survey_status']) ? ucfirst($existingSurvey['survey_status']) : 'Unknown'; ?></span>
            </p>
            <div class="mt-4">
                <?php if (isset($existingSurvey['id'])): ?>
                    <a href="../shared/view-survey.php?id=<?php echo $existingSurvey['id']; ?>" class="inline-flex items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100">
                        View Survey Details
                    </a>
                    <?php if (isset($existingSurvey['survey_status']) && ($existingSurvey['survey_status'] === 'pending' || $existingSurvey['survey_status'] === 'rejected')): ?>
                        <a href="edit-survey.php?id=<?php echo $existingSurvey['id']; ?>" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Edit Survey
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php else: ?>

<div class="professional-table bg-white">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Site Feasibility Survey</h3>
        <p class="text-sm text-gray-500 mt-1">Complete the technical assessment for this installation site</p>
    </div>
    <div class="p-6">
        <form id="surveyForm" action="process-survey-comprehensive.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="site_id" value="<?php echo $site['id']; ?>">
            <input type="hidden" name="delegation_id" value="<?php echo $delegationId; ?>">
            
            <div class="form-section bg-blue-50 border-blue-200">
                <h4 class="text-lg font-semibold text-blue-800 mb-4">Check In</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">Date & Time</label>
                        <input type="datetime-local" name="checkin_datetime" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Working Hours</label>
                        <input type="text" name="working_hours" class="form-input" placeholder="e.g. 6 hours" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4>Site Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">Assigned Site</label>
                        <input type="text" class="form-input bg-gray-100" value="<?php echo htmlspecialchars($site['site_id']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Store Model</label>
                        <input type="text" name="store_model" class="form-input" placeholder="Enter store model" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4>Floor & Ceiling Assessment</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">Floor Height (in feet)</label>
                        <input type="number" step="0.1" name="floor_height" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Floor Height Photos</label>
                        <input type="file" name="floor_height_photo[]" class="form-input image-input" accept="image/*" multiple>
                        <div class="preview mt-2 flex flex-wrap gap-2"></div>
                        <p class="text-xs text-gray-500 mt-1">Upload multiple photos showing floor height</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type of Ceiling</label>
                        <select name="ceiling_type" class="form-select" required>
                            <option value="">Select ceiling type</option>
                            <option value="False">False Ceiling</option>
                            <option value="Open">Open Ceiling</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ceiling Photos</label>
                        <input type="file" name="ceiling_photo[]" class="form-input image-input" multiple accept="image/*">
                        <div class="preview mt-2 flex flex-wrap gap-2"></div>
                        <p class="text-xs text-gray-500 mt-1">Upload photos showing ceiling structure</p>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4>Camera Assessment</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">Total Number of Cameras</label>
                        <input type="number" name="total_cameras" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Number of Analytic Cameras</label>
                        <input type="number" name="analytic_cameras" class="form-input">
                    </div>
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Analytic Cameras - Snapshots (max 5)</label>
                        <input type="file" name="analytic_photos[]" class="form-input image-input" multiple accept="image/*">
                        <div class="preview mt-2 flex flex-wrap gap-2"></div>
                        <p class="text-xs text-gray-500 mt-1">Upload snapshots of analytic camera positions</p>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4>POE Rack Assessment</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">Existing POE Rack (count)</label>
                        <input type="number" name="existing_poe_rack" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Existing POE Rack Photos (max 5)</label>
                        <input type="file" name="existing_poe_photos[]" class="form-input image-input" multiple accept="image/*">
                        <div class="preview mt-2 flex flex-wrap gap-2"></div>
                        <p class="text-xs text-gray-500 mt-1">Photos of existing POE rack setup</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Space for New Rack (Server Room)</label>
                        <select name="space_new_rack" class="form-select" required>
                            <option value="">Select availability</option>
                            <option value="Yes">Yes - Space available</option>
                            <option value="No">No - No space available</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Server Room Photos (max 5)</label>
                        <input type="file" name="space_new_rack_photo[]" class="form-input image-input" multiple accept="image/*">
                        <div class="preview mt-2 flex flex-wrap gap-2"></div>
                        <p class="text-xs text-gray-500 mt-1">Photos showing available space for new rack</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">New POE Rack Recommended (count)</label>
                        <input type="number" name="new_poe_rack" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">New POE Rack Photos (max 5)</label>
                        <input type="file" name="new_poe_photos[]" class="form-input image-input" multiple accept="image/*">
                        <div class="preview mt-2 flex flex-wrap gap-2"></div>
                        <p class="text-xs text-gray-500 mt-1">Photos of recommended new POE rack locations</p>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4>Zone Assessment</h4>
                <div class="form-group">
                    <label class="form-label">Number of Zones Recommended</label>
                    <input type="number" name="zones_recommended" class="form-input">
                    <p class="text-xs text-gray-500 mt-1">Recommended number of security zones for this site</p>
                </div>
            </div>

            <div class="form-section">
                <h4>Material Status</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">RRL Materials Delivered?</label>
                        <select name="rrl_delivery_status" class="form-select">
                            <option value="">Select status</option>
                            <option value="Yes">Yes - Materials delivered</option>
                            <option value="No">No - Materials not delivered</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">RRL Delivery Photos (max 5)</label>
                        <input type="file" name="rrl_photos[]" class="form-input image-input" multiple accept="image/*">
                        <div class="preview mt-2 flex flex-wrap gap-2"></div>
                        <p class="text-xs text-gray-500 mt-1">Photos of delivered RRL materials</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Space for KPTL Materials?</label>
                        <select name="kptl_space" class="form-select">
                            <option value="">Select availability</option>
                            <option value="Yes">Yes - Space available</option>
                            <option value="No">No - No space available</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">KPTL Material Photos (max 5)</label>
                        <input type="file" name="kptl_photos[]" class="form-input image-input" multiple accept="image/*">
                        <div class="preview mt-2 flex flex-wrap gap-2"></div>
                        <p class="text-xs text-gray-500 mt-1">Photos showing space for KPTL materials</p>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4>Technical Assessment</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">Site Accessibility</label>
                        <select name="site_accessibility" class="form-select" required>
                            <option value="">Select accessibility level</option>
                            <option value="good">Good - Easy access</option>
                            <option value="moderate">Moderate - Some restrictions</option>
                            <option value="poor">Poor - Difficult access</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Power Availability</label>
                        <select name="power_availability" class="form-select" required>
                            <option value="">Select power status</option>
                            <option value="available">Available - Adequate power supply</option>
                            <option value="partial">Partial - Limited power supply</option>
                            <option value="unavailable">Unavailable - No power supply</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Network Connectivity</label>
                        <select name="network_connectivity" class="form-select" required>
                            <option value="">Select connectivity level</option>
                            <option value="excellent">Excellent - Strong signal</option>
                            <option value="good">Good - Adequate signal</option>
                            <option value="poor">Poor - Weak signal</option>
                            <option value="none">None - No connectivity</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Space Adequacy</label>
                        <select name="space_adequacy" class="form-select" required>
                            <option value="">Select space adequacy</option>
                            <option value="adequate">Adequate - Sufficient space</option>
                            <option value="tight">Tight - Limited space</option>
                            <option value="inadequate">Inadequate - Insufficient space</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4>Survey Findings & Requirements</h4>
                <div class="space-y-6">
                    <div class="form-group">
                        <label class="form-label">Technical Remarks</label>
                        <textarea name="technical_remarks" class="form-textarea" rows="4" placeholder="Detailed technical observations and findings..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Challenges Identified</label>
                        <textarea name="challenges_identified" class="form-textarea" rows="3" placeholder="Any challenges or issues identified during the survey..."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Recommendations</label>
                        <textarea name="recommendations" class="form-textarea" rows="3" placeholder="Your recommendations for successful installation..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Additional Equipment Needed</label>
                        <textarea name="additional_equipment_needed" class="form-textarea" rows="3" placeholder="List any additional equipment or materials needed..."></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label class="form-label">Estimated Completion Days</label>
                            <input type="number" name="estimated_completion_days" class="form-input" min="1" max="365" placeholder="Number of days estimated for completion">
                        </div>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="electrical_work_required" value="1" class="mr-2">
                                <span class="text-sm font-medium text-gray-700">Electrical work required</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="civil_work_required" value="1" class="mr-2">
                                <span class="text-sm font-medium text-gray-700">Civil work required</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="network_work_required" value="1" class="mr-2">
                                <span class="text-sm font-medium text-gray-700">Network work required</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section bg-green-50 border-green-200">
                <h4 class="text-lg font-semibold text-green-800 mb-4">Check Out</h4>
                <div class="form-group">
                    <label class="form-label">Check Out Date & Time</label>
                    <input type="datetime-local" name="checkout_datetime" class="form-input" required>
                </div>
            </div>
            
            <div class="form-section">
                <h4>Site Photos (Optional)</h4>
                <div class="form-group">
                    <label class="form-label">Upload Additional Site Photos</label>
                    <input type="file" name="site_photos[]" class="form-input" multiple accept="image/*">
                    <p class="text-xs text-gray-500 mt-1">You can upload multiple photos. Max size: 5MB per file.</p>
                </div>
            </div>

            <div class="flex justify-between items-center mt-8 p-6 bg-gray-50 rounded-lg border-t">
                <div class="flex space-x-2">
                    <button type="button" id="fillTestData" class="inline-flex items-center px-4 py-2 border border-green-300 text-sm font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" clip-rule="evenodd"></path>
                        </svg>
                        Fill Test Data
                    </button>
                    <button type="button" id="addSampleImages" class="inline-flex items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                        </svg>
                        Add Sample Images
                    </button>
                </div>
                <div class="flex space-x-4">
                    <a href="sites/" class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Submit Feasibility Survey
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Set default date/time values and image preview functionality
document.addEventListener('DOMContentLoaded', function() {
    // Set default check-in time to current date/time
    const checkinInput = document.querySelector('input[name="checkin_datetime"]');
    if (checkinInput && !checkinInput.value) {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        checkinInput.value = now.toISOString().slice(0, 16);
    }
    
    // Image preview functionality
    const imageInputs = document.querySelectorAll('.image-input');
    
    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            const preview = this.parentNode.querySelector('.preview');
            if (preview) {
                preview.innerHTML = '';
                
                if (this.files) {
                    Array.from(this.files).forEach((file, index) => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.className = 'w-20 h-20 object-cover rounded border shadow-sm';
                                img.title = file.name;
                                preview.appendChild(img);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                }
            }
        });
    });
    
    // Test data fill functionality
    document.getElementById('fillTestData').addEventListener('click', function() {
        fillTestData();
    });
    
    // Sample images functionality
    document.getElementById('addSampleImages').addEventListener('click', function() {
        createSampleImages();
        // Assuming showAlert function is defined elsewhere in your vendor_layout.php or another included file.
        if (typeof showAlert === 'function') {
            showAlert('Sample images added to all photo fields!', 'success');
        } else {
            console.warn('showAlert function not found. Sample images added.');
        }
    });
    
    // Form submission
    document.getElementById('surveyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        // FIX: The querySelector is now guaranteed to work because the button is inside the form.
        const submitBtn = this.querySelector('button[type="submit"]'); 
        
        if (!submitBtn) {
            console.error('Submit button not found (Error in JS logic or late DOM modification)');
            return;
        }
        
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.disabled = true;
        // Check if spinner class is defined in your CSS/framework
        submitBtn.innerHTML = '<span class="spinner mr-2"></span>Submitting Survey...';
        
        fetch('process-survey-comprehensive.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Assuming showAlert function is defined elsewhere
            if (typeof showAlert === 'function') {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => window.location.href = 'sites/', 2000);
                } else {
                    showAlert(data.message, 'error');
                }
            } else {
                console.log(data.message);
                if (data.success) {
                    alert(data.message);
                    setTimeout(() => window.location.href = 'sites/', 2000);
                } else {
                    alert('Error: ' + data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Assuming showAlert function is defined elsewhere
            if (typeof showAlert === 'function') {
                showAlert('An error occurred. Please try again.', 'error');
            } else {
                alert('An error occurred. Please try again.');
            }
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
});

// Fill form with test data
function fillTestData() {
    const now = new Date();
    const checkoutTime = new Date(now.getTime() + (6 * 60 * 60 * 1000)); // 6 hours later
    
    // Format datetime for input
    const formatDateTime = (date) => {
        date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
        return date.toISOString().slice(0, 16);
    };
    
    // Fill all form fields with realistic test data
    const testData = {
        // Check-in/Check-out
        'checkin_datetime': formatDateTime(now),
        'checkout_datetime': formatDateTime(checkoutTime),
        'working_hours': '6 hours',
        
        // Site Information
        'store_model': 'Standard Retail Store Model A',
        
        // Floor and Ceiling
        'floor_height': '12.5',
        'ceiling_type': 'False',
        
        // Camera Assessment
        'total_cameras': '8',
        'analytic_cameras': '4',
        
        // POE Rack Assessment
        'existing_poe_rack': '1',
        'space_new_rack': 'Yes',
        'new_poe_rack': '2',
        
        // Zone Assessment
        'zones_recommended': '3',
        
        // Material Status
        'rrl_delivery_status': 'Yes',
        'kptl_space': 'Yes',
        
        // Technical Assessment (Additional Details)
        'site_accessibility': 'good',
        'power_availability': 'available',
        'network_connectivity': 'excellent',
        'space_adequacy': 'adequate',
        
        // Survey Findings (Consolidated section)
        'technical_remarks': 'Site has excellent infrastructure with adequate power supply and network connectivity. Floor height is suitable for camera installation. Existing POE rack can accommodate additional equipment. The location provides good visibility for security cameras with minimal obstructions.',
        'challenges_identified': 'Minor cable management improvements needed in server room. Some areas may require additional lighting for optimal camera performance. Network switch may need upgrade to handle increased bandwidth from additional cameras.',
        'recommendations': 'Recommend installing 2 additional POE racks for optimal coverage. Suggest upgrading network switch to handle increased bandwidth. Consider installing backup power supply for critical equipment. Implement proper cable management system in server room.',
        'estimated_completion_days': '5',
        'additional_equipment_needed': '1x New Network Switch, 1x UPS',
        
        // Checkboxes (Need to set checked property separately if using real submission)
        // For testing purposes, we'll set the values if they were text inputs, but for real form submission they should be checked.
    };
    
    // Fill text inputs, selects, and textareas
    Object.keys(testData).forEach(name => {
        const element = document.querySelector(`[name="${name}"]`);
        if (element) {
            element.value = testData[name];
            
            // Trigger change event for any listeners
            element.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    // Checkboxes need to be explicitly set
    document.querySelector('input[name="electrical_work_required"]').checked = true;
    document.querySelector('input[name="civil_work_required"]').checked = false;
    document.querySelector('input[name="network_work_required"]').checked = true;
    
    // Show success message
    // Assuming showAlert function is defined elsewhere
    if (typeof showAlert === 'function') {
        showAlert('Test data filled successfully! Use "Add Sample Images" button to add photos.', 'success');
    } else {
        console.log('Test data filled successfully!');
    }
}

// Create sample images for testing
function createSampleImages() {
    // Create a simple colored canvas as sample image
    const canvas = document.createElement('canvas');
    canvas.width = 200;
    canvas.height = 150;
    const ctx = canvas.getContext('2d');
    
    // Sample image configurations
    const sampleImages = [
        { name: 'floor_height_photo', color: '#3b82f6', text: 'Floor Height' },
        { name: 'ceiling_photo', color: '#10b981', text: 'Ceiling View' },
        { name: 'analytic_photos', color: '#f59e0b', text: 'Camera Position' },
        { name: 'existing_poe_photos', color: '#ef4444', text: 'Existing POE' },
        { name: 'space_new_rack_photo', color: '#8b5cf6', text: 'Server Room' },
        { name: 'new_poe_photos', color: '#06b6d4', text: 'New POE Location' },
        { name: 'rrl_photos', color: '#84cc16', text: 'RRL Materials' },
        { name: 'kptl_photos', color: '#f97316', text: 'KPTL Space' }
        // Note: 'site_photos[]' is optional, not added to sample images by default unless necessary
    ];
    
    sampleImages.forEach(config => {
        // Create sample image
        ctx.fillStyle = config.color;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        // Add text
        ctx.fillStyle = 'white';
        ctx.font = '16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(config.text, canvas.width/2, canvas.height/2 - 10);
        ctx.fillText('Sample Image', canvas.width/2, canvas.height/2 + 10);
        ctx.fillText(new Date().toLocaleDateString(), canvas.width/2, canvas.height/2 + 30);
        
        // Convert to blob and create file
        canvas.toBlob(blob => {
            const file = new File([blob], `${config.text.replace(' ', '_').toLowerCase()}_sample.png`, { type: 'image/png' });
            const input = document.querySelector(`input[name="${config.name}[]"]`);
            
            if (input) {
                // Create a new FileList with our sample file
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                
                // Trigger change event to show preview
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }, 'image/png');
    });
}


</script>

<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/admin_layout.php';
?>
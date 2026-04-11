<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../models/SiteDelegation.php';
require_once __DIR__ . '/../models/SiteSurvey.php';
require_once __DIR__ . '/../config/database.php';

$siteId = $_GET['delegation_id'] ?? null;

if (!$siteId) {
    header('Location: sites/');
    exit;
}

$siteModel = new Site();
$delegationModel = new SiteDelegation();
$surveyModel = new SiteSurvey();

// Get delegation details
$delegationId = $delegationModel->findDelegationId($siteId);

// Get site details
$site = $siteModel->findWithRelations($siteId);
if (!$site) {
    header('Location: sites/');
    exit;
}

// Get the customer ID from the site
$customerId = $site['customer_id'] ?? null;

// Find the survey form for this customer
$db = Database::getInstance()->getConnection();
$surveyFormId = null;

if ($customerId) {
    // Try to find a survey form for this specific customer
    $stmt = $db->prepare("SELECT id FROM dynamic_surveys WHERE customer_id = ? AND form_type = 'survey' AND status = 'active' ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$customerId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $surveyFormId = $result['id'];
    }
}

// If no customer-specific form found, try to find a global form (customer_id = NULL)
if (!$surveyFormId) {
    $stmt = $db->prepare("SELECT id FROM dynamic_surveys WHERE customer_id IS NULL AND form_type = 'survey' AND status = 'active' ORDER BY created_at DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $surveyFormId = $result['id'];
    }
}

// Check if survey already exists
$stmt = $db->prepare("SELECT id, survey_status, submitted_date FROM dynamic_survey_responses WHERE delegation_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$delegationId]);
$existingSurvey = $stmt->fetch(PDO::FETCH_ASSOC);

// If survey exists, redirect to view page
if ($existingSurvey) {
    header('Location: ../shared/view-survey2.php?id=' . $existingSurvey['id']);
    exit;
}

$title = 'Site Survey - ' . $site['site_id'];
ob_start();

// var_dump($_SESSION);

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
        <p class="text-sm text-gray-500 mt-1">Complete site master data</p>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Site ID</label>
                <p class="text-sm text-gray-900 font-semibold"><?php echo htmlspecialchars($site['site_id'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Store ID</label>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($site['store_id'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Site Ticket ID</label>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($site['site_ticket_id'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Branch</label>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($site['branch'] ?? 'N/A'); ?></p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1">Location</label>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($site['location'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">City</label>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($site['city_name'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Pincode</label>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($site['pincode'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">State</label>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($site['state_name'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Country</label>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($site['country_name'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Zone</label>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($site['zone_name'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Customer</label>
                <p class="text-sm text-gray-900 font-semibold"><?php echo htmlspecialchars($site['customer_name'] ?? 'N/A'); ?></p>
            </div>
        </div>
        
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Contact Person</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Name</label>
                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($site['contact_person_name'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Phone Number</label>
                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($site['contact_person_number'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Email</label>
                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($site['contact_person_email'] ?? 'N/A'); ?></p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Vendor</label>
                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($site['vendor_name'] ?? 'N/A'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Form Container -->
<div id="dynamicFormApp" class="professional-table bg-white">
    <div v-if="loading" class="p-12 text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-500">Loading survey form...</p>
    </div>

    <div v-if="error" class="p-6">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-red-600">{{ error }}</p>
            <p class="text-sm text-gray-600 mt-2">Customer: <?php echo htmlspecialchars($site['customer_name'] ?? 'N/A'); ?></p>
            <p class="text-sm text-gray-600">Please create a survey form for this customer or a global survey form.</p>
        </div>
    </div>

    <template v-if="!loading && !error">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">{{ survey.title }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ survey.description }}</p>
        </div>
        
        <div class="p-6">
            <form @submit.prevent="submitSurvey" enctype="multipart/form-data">
                <input type="hidden" name="site_id" value="<?php echo $site['id']; ?>">
                <input type="hidden" name="delegation_id" value="<?php echo $delegationId; ?>">
                <input type="hidden" name="survey_form_id" :value="survey.id">
                
                <!-- Site Master Data (hidden fields for submission) -->
                <input type="hidden" name="site_master[site_id]" value="<?php echo htmlspecialchars($site['site_id'] ?? ''); ?>">
                <input type="hidden" name="site_master[store_id]" value="<?php echo htmlspecialchars($site['store_id'] ?? ''); ?>">
                <input type="hidden" name="site_master[location]" value="<?php echo htmlspecialchars($site['location'] ?? ''); ?>">
                <input type="hidden" name="site_master[city]" value="<?php echo htmlspecialchars($site['city_name'] ?? ''); ?>">
                <input type="hidden" name="site_master[state]" value="<?php echo htmlspecialchars($site['state_name'] ?? ''); ?>">
                <input type="hidden" name="site_master[country]" value="<?php echo htmlspecialchars($site['country_name'] ?? ''); ?>">
                <input type="hidden" name="site_master[zone]" value="<?php echo htmlspecialchars($site['zone_name'] ?? ''); ?>">
                <input type="hidden" name="site_master[pincode]" value="<?php echo htmlspecialchars($site['pincode'] ?? ''); ?>">
                <input type="hidden" name="site_master[branch]" value="<?php echo htmlspecialchars($site['branch'] ?? ''); ?>">
                <input type="hidden" name="site_master[customer]" value="<?php echo htmlspecialchars($site['customer_name'] ?? ''); ?>">
                <input type="hidden" name="site_master[contact_person_name]" value="<?php echo htmlspecialchars($site['contact_person_name'] ?? ''); ?>">
                <input type="hidden" name="site_master[contact_person_number]" value="<?php echo htmlspecialchars($site['contact_person_number'] ?? ''); ?>">
                <input type="hidden" name="site_master[contact_person_email]" value="<?php echo htmlspecialchars($site['contact_person_email'] ?? ''); ?>">
                <input type="hidden" name="site_master[vendor]" value="<?php echo htmlspecialchars($site['vendor_name'] ?? ''); ?>">
                <input type="hidden" name="site_master[site_ticket_id]" value="<?php echo htmlspecialchars($site['site_ticket_id'] ?? ''); ?>">
                
                <!-- Dynamic Sections -->
                <div v-for="(section, sIndex) in sections" :key="section.id" class="form-section mb-8">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">{{ section.title }}</h4>
                    <p v-if="section.description" class="text-sm text-gray-500 mb-4">{{ section.description }}</p>
                    
                    <!-- Main Section Fields -->
                    <div v-if="section.fields && section.fields.length > 0" class="flex flex-wrap gap-6 mb-6">
                        <div v-for="field in section.fields" :key="field.id"
                             :class="{
                                 'w-full': field.field_width === 'full' || !field.field_width,
                                 'w-full md:w-[calc(50%-0.75rem)]': field.field_width === 'half',
                                 'w-full md:w-[calc(33.333%-1rem)]': field.field_width === 'third',
                                 'w-full md:w-[calc(25%-1.125rem)]': field.field_width === 'quarter'
                             }"
                             class="form-group">
                            <label class="form-label">
                                {{ field.label }}
                                <span v-if="field.is_required" class="text-red-500">*</span>
                            </label>

                            <!-- Text, Email, Password, Number -->
                            <input 
                                v-if="['text', 'email', 'password', 'number'].includes(field.field_type)"
                                :type="field.field_type"
                                v-model="formData[field.id]"
                                :placeholder="field.placeholder"
                                :required="field.is_required"
                                :min="field.field_type === 'number' && !field.allow_negative ? '0' : null"
                                class="form-input">

                            <!-- Textarea -->
                            <textarea 
                                v-if="field.field_type === 'textarea'"
                                v-model="formData[field.id]"
                                :placeholder="field.placeholder"
                                :required="field.is_required"
                                class="form-textarea"
                                rows="3"></textarea>

                            <!-- Date, Time -->
                            <input 
                                v-if="['date', 'time'].includes(field.field_type)"
                                :type="field.field_type"
                                v-model="formData[field.id]"
                                :required="field.is_required"
                                class="form-input">

                            <!-- DateTime -->
                            <input 
                                v-if="['datetime', 'datetime-local'].includes(field.field_type)"
                                type="datetime-local"
                                v-model="formData[field.id]"
                                :required="field.is_required"
                                class="form-input">

                            <!-- Select Dropdown -->
                            <select 
                                v-if="field.field_type === 'select'"
                                v-model="formData[field.id]"
                                :required="field.is_required"
                                class="form-select">
                                <option value="">Select an option...</option>
                                <option v-for="opt in getOptions(field.options)" :key="opt" :value="opt">{{ opt }}</option>
                            </select>

                            <!-- Customer Dropdown -->
                            <select 
                                v-if="field.field_type === 'customer'"
                                v-model="formData[field.id]"
                                :required="field.is_required"
                                class="form-select">
                                <option value="">Select customer...</option>
                                <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</option>
                            </select>

                            <!-- Radio Buttons -->
                            <div v-if="field.field_type === 'radio'" class="space-y-2">
                                <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center">
                                    <input 
                                        type="radio" 
                                        :name="'field_' + field.id"
                                        :value="opt"
                                        v-model="formData[field.id]"
                                        :required="field.is_required"
                                        class="mr-2">
                                    <span class="text-sm">{{ opt }}</span>
                                </label>
                            </div>

                            <!-- Checkboxes -->
                            <div v-if="field.field_type === 'checkbox'" class="space-y-2">
                                <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        :value="opt"
                                        @change="updateCheckbox(field.id, opt, $event)"
                                        class="mr-2">
                                    <span class="text-sm">{{ opt }}</span>
                                </label>
                            </div>

                            <!-- File Upload -->
                            <input 
                                v-if="field.field_type === 'file'"
                                type="file"
                                :multiple="field.allow_multiple"
                                :accept="getFileAccept(field)"
                                @change="handleFileUpload(field.id, $event, field)"
                                :required="field.is_required"
                                class="form-input">
                            
                            <!-- File Preview -->
                            <div v-if="field.field_type === 'file' && field.show_preview && filePreviews[field.id] && filePreviews[field.id].length > 0" class="mt-3 grid grid-cols-2 md:grid-cols-3 gap-3">
                                <div v-for="(preview, index) in filePreviews[field.id]" :key="index" class="relative group">
                                    <div class="border-2 border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                                        <img v-if="preview.type === 'image'" :src="preview.url" class="w-full h-32 object-cover">
                                        <div v-else class="w-full h-32 flex flex-col items-center justify-center p-3">
                                            <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                            <p class="text-xs text-gray-600 font-medium text-center truncate w-full">{{ preview.name }}</p>
                                        </div>
                                    </div>
                                    <button @click="removeFile(field.id, index)" type="button" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                    <p class="text-xs text-gray-500 mt-1 truncate">{{ formatFileSize(preview.size) }}</p>
                                </div>
                            </div>
                            
                            <p v-if="field.field_type === 'file' && field.max_files && field.allow_multiple" class="text-xs text-gray-400 mt-1">
                                Max {{ field.max_files }} files, {{ field.max_file_size }}MB each
                            </p>
                            
                            <p v-if="field.help_text" class="text-xs text-gray-500 mt-1">{{ field.help_text }}</p>
                        </div>
                    </div>

                    <!-- Subsections -->
                    <div v-if="section.subsections && section.subsections.length > 0" class="space-y-6">
                        <div v-for="subsection in section.subsections" :key="subsection.id" class="bg-purple-50/50 rounded-xl p-6 border-2 border-purple-200">
                            <div class="mb-4">
                                <h5 class="text-md font-bold text-purple-900 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    {{ subsection.title }}
                                </h5>
                                <p v-if="subsection.description" class="text-sm text-purple-700 mt-1">{{ subsection.description }}</p>
                            </div>
                            
                            <div class="flex flex-wrap gap-6">
                                <div v-for="field in subsection.fields" :key="field.id"
                                     :class="{
                                         'w-full': field.field_width === 'full' || !field.field_width,
                                         'w-full md:w-[calc(50%-0.75rem)]': field.field_width === 'half',
                                         'w-full md:w-[calc(33.333%-1rem)]': field.field_width === 'third',
                                         'w-full md:w-[calc(25%-1.125rem)]': field.field_width === 'quarter'
                                     }"
                                     class="form-group">
                                    <label class="form-label">
                                        {{ field.label }}
                                        <span v-if="field.is_required" class="text-red-500">*</span>
                                    </label>

                                    <!-- Text, Email, Password, Number -->
                                    <input 
                                        v-if="['text', 'email', 'password', 'number'].includes(field.field_type)"
                                        :type="field.field_type"
                                        v-model="formData[field.id]"
                                        :placeholder="field.placeholder"
                                        :required="field.is_required"
                                        :min="field.field_type === 'number' && !field.allow_negative ? '0' : null"
                                        class="form-input">

                                    <!-- Textarea -->
                                    <textarea 
                                        v-if="field.field_type === 'textarea'"
                                        v-model="formData[field.id]"
                                        :placeholder="field.placeholder"
                                        :required="field.is_required"
                                        class="form-textarea"
                                        rows="3"></textarea>

                                    <!-- Date, Time -->
                                    <input 
                                        v-if="['date', 'time'].includes(field.field_type)"
                                        :type="field.field_type"
                                        v-model="formData[field.id]"
                                        :required="field.is_required"
                                        class="form-input">

                                    <!-- DateTime -->
                                    <input 
                                        v-if="['datetime', 'datetime-local'].includes(field.field_type)"
                                        type="datetime-local"
                                        v-model="formData[field.id]"
                                        :required="field.is_required"
                                        class="form-input">

                                    <!-- Select Dropdown -->
                                    <select 
                                        v-if="field.field_type === 'select'"
                                        v-model="formData[field.id]"
                                        :required="field.is_required"
                                        class="form-select">
                                        <option value="">Select an option...</option>
                                        <option v-for="opt in getOptions(field.options)" :key="opt" :value="opt">{{ opt }}</option>
                                    </select>

                                    <!-- Customer Dropdown -->
                                    <select 
                                        v-if="field.field_type === 'customer'"
                                        v-model="formData[field.id]"
                                        :required="field.is_required"
                                        class="form-select">
                                        <option value="">Select customer...</option>
                                        <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</option>
                                    </select>

                                    <!-- Radio Buttons -->
                                    <div v-if="field.field_type === 'radio'" class="space-y-2">
                                        <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center">
                                            <input 
                                                type="radio" 
                                                :name="'field_' + field.id"
                                                :value="opt"
                                                v-model="formData[field.id]"
                                                :required="field.is_required"
                                                class="mr-2">
                                            <span class="text-sm">{{ opt }}</span>
                                        </label>
                                    </div>

                                    <!-- Checkboxes -->
                                    <div v-if="field.field_type === 'checkbox'" class="space-y-2">
                                        <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                :value="opt"
                                                @change="updateCheckbox(field.id, opt, $event)"
                                                class="mr-2">
                                            <span class="text-sm">{{ opt }}</span>
                                        </label>
                                    </div>

                                    <!-- File Upload -->
                                    <input 
                                        v-if="field.field_type === 'file'"
                                        type="file"
                                        :multiple="field.allow_multiple"
                                        :accept="getFileAccept(field)"
                                        @change="handleFileUpload(field.id, $event, field)"
                                        :required="field.is_required"
                                        class="form-input">
                                    
                                    <!-- File Preview -->
                                    <div v-if="field.field_type === 'file' && field.show_preview && filePreviews[field.id] && filePreviews[field.id].length > 0" class="mt-3 grid grid-cols-2 md:grid-cols-3 gap-3">
                                        <div v-for="(preview, index) in filePreviews[field.id]" :key="index" class="relative group">
                                            <div class="border-2 border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                                                <img v-if="preview.type === 'image'" :src="preview.url" class="w-full h-32 object-cover">
                                                <div v-else class="w-full h-32 flex flex-col items-center justify-center p-3">
                                                    <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <p class="text-xs text-gray-600 font-medium text-center truncate w-full">{{ preview.name }}</p>
                                                </div>
                                            </div>
                                            <button @click="removeFile(field.id, index)" type="button" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                            <p class="text-xs text-gray-500 mt-1 truncate">{{ formatFileSize(preview.size) }}</p>
                                        </div>
                                    </div>
                                    
                                    <p v-if="field.field_type === 'file' && field.max_files && field.allow_multiple" class="text-xs text-gray-400 mt-1">
                                        Max {{ field.max_files }} files, {{ field.max_file_size }}MB each
                                    </p>
                                    
                                    <p v-if="field.help_text" class="text-xs text-gray-500 mt-1">{{ field.help_text }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-8 p-6 bg-gray-50 rounded-lg border-t">
                    <a href="sites/" class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" :disabled="submitting" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span v-if="submitting">Submitting...</span>
                        <span v-else>Submit Survey</span>
                    </button>
                </div>
            </form>
        </div>
    </template>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            surveyFormId: <?php echo json_encode($surveyFormId); ?>,
            loading: true,
            error: null,
            survey: {},
            sections: [],
            customers: [],
            formData: {},
            files: {},
            filePreviews: {},
            submitting: false
        };
    },
    methods: {
        async loadSurvey() {
            if (!this.surveyFormId) {
                this.error = 'No survey form found for this customer. Please create a survey form for "<?php echo htmlspecialchars($site['customer_name'] ?? 'this customer'); ?>" or a global survey form.';
                this.loading = false;
                return;
            }

            try {
                const response = await fetch(`../api/surveys_v2.php?action=load&id=${this.surveyFormId}`);
                const data = await response.json();
                
                if (data.success) {
                    this.survey = data.survey;
                    this.sections = data.sections;
                    
                    // Initialize formData
                    this.sections.forEach(section => {
                        // Initialize main section fields
                        if (section.fields) {
                            section.fields.forEach(field => {
                                if (field.field_type === 'checkbox') {
                                    this.formData[field.id] = [];
                                } else {
                                    this.formData[field.id] = '';
                                }
                            });
                        }
                        
                        // Initialize subsection fields
                        if (section.subsections) {
                            section.subsections.forEach(subsection => {
                                if (subsection.fields) {
                                    subsection.fields.forEach(field => {
                                        if (field.field_type === 'checkbox') {
                                            this.formData[field.id] = [];
                                        } else {
                                            this.formData[field.id] = '';
                                        }
                                    });
                                }
                            });
                        }
                    });
                } else {
                    this.error = data.message || 'Failed to load survey';
                }
            } catch (err) {
                this.error = 'An error occurred while loading the survey';
                console.error(err);
            } finally {
                this.loading = false;
            }
        },
        
        async loadCustomers() {
            try {
                const response = await fetch('../api/surveys_v2.php?action=get_customers');
                const data = await response.json();
                
                if (data.success) {
                    this.customers = data.customers;
                }
            } catch (err) {
                console.error('Failed to load customers', err);
            }
        },
        
        getOptions(optionsString) {
            if (!optionsString) return [];
            return optionsString.split(',').map(opt => opt.trim()).filter(opt => opt);
        },
        
        updateCheckbox(fieldId, value, event) {
            if (!this.formData[fieldId]) {
                this.formData[fieldId] = [];
            }
            
            if (event.target.checked) {
                this.formData[fieldId].push(value);
            } else {
                const index = this.formData[fieldId].indexOf(value);
                if (index > -1) {
                    this.formData[fieldId].splice(index, 1);
                }
            }
        },
        
        handleFileUpload(fieldId, event, field) {
            const selectedFiles = Array.from(event.target.files);
            const maxFiles = field.allow_multiple ? (field.max_files || 5) : 1;
            const maxSize = (field.max_file_size || 5) * 1024 * 1024;
            
            if (selectedFiles.length > maxFiles) {
                alert(`You can only upload up to ${maxFiles} file(s)`);
                event.target.value = '';
                return;
            }
            
            const validFiles = [];
            const acceptedTypes = this.getFileAcceptArray(field);
            
            for (const file of selectedFiles) {
                if (file.size > maxSize) {
                    alert(`File "${file.name}" exceeds the maximum size of ${field.max_file_size}MB`);
                    continue;
                }
                
                if (acceptedTypes.length > 0) {
                    const fileExt = '.' + file.name.split('.').pop().toLowerCase();
                    const mimeType = file.type.toLowerCase();
                    
                    const isValid = acceptedTypes.some(type => {
                        if (type.startsWith('.')) {
                            return fileExt === type;
                        }
                        return mimeType.match(type.replace('*', '.*'));
                    });
                    
                    if (!isValid) {
                        alert(`File "${file.name}" is not an allowed file type`);
                        continue;
                    }
                }
                
                validFiles.push(file);
            }
            
            if (validFiles.length === 0) {
                event.target.value = '';
                return;
            }
            
            if (!this.files[fieldId]) {
                this.files[fieldId] = [];
            }
            this.files[fieldId] = field.allow_multiple ? validFiles : [validFiles[0]];
            
            if (field.show_preview) {
                this.filePreviews[fieldId] = [];
                
                validFiles.forEach(file => {
                    const preview = {
                        name: file.name,
                        size: file.size,
                        type: file.type.startsWith('image/') ? 'image' : 'document',
                        url: null
                    };
                    
                    if (preview.type === 'image') {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            preview.url = e.target.result;
                            this.filePreviews[fieldId].push(preview);
                        };
                        reader.readAsDataURL(file);
                    } else {
                        this.filePreviews[fieldId].push(preview);
                    }
                });
            }
        },
        
        removeFile(fieldId, index) {
            if (this.files[fieldId]) {
                this.files[fieldId].splice(index, 1);
            }
            if (this.filePreviews[fieldId]) {
                this.filePreviews[fieldId].splice(index, 1);
            }
        },
        
        getFileAccept(field) {
            const types = this.getFileAcceptArray(field);
            return types.join(',');
        },
        
        getFileAcceptArray(field) {
            if (!field.file_type_restriction) return [];
            
            const typeMap = {
                'image': ['image/*'],
                'document': ['.pdf', '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx'],
                'image_document': ['image/*', '.pdf', '.doc', '.docx', '.xls', '.xlsx']
            };
            
            if (field.file_type_restriction === 'custom' && field.custom_file_types) {
                return field.custom_file_types.split(',').map(t => t.trim()).filter(t => t);
            }
            
            return typeMap[field.file_type_restriction] || [];
        },
        
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        },
        
        async submitSurvey() {
            this.submitting = true;
            
            try {
                const formData = new FormData();
                formData.append('site_id', '<?php echo $site['id']; ?>');
                formData.append('delegation_id', '<?php echo $delegationId; ?>');
                formData.append('survey_form_id', this.survey.id);
                formData.append('form_data', JSON.stringify(this.formData));
                
                // Add site master data
                const siteMaster = {
                    site_id: '<?php echo htmlspecialchars($site['site_id'] ?? '', ENT_QUOTES); ?>',
                    store_id: '<?php echo htmlspecialchars($site['store_id'] ?? '', ENT_QUOTES); ?>',
                    location: '<?php echo htmlspecialchars($site['location'] ?? '', ENT_QUOTES); ?>',
                    city: '<?php echo htmlspecialchars($site['city_name'] ?? '', ENT_QUOTES); ?>',
                    state: '<?php echo htmlspecialchars($site['state_name'] ?? '', ENT_QUOTES); ?>',
                    country: '<?php echo htmlspecialchars($site['country_name'] ?? '', ENT_QUOTES); ?>',
                    zone: '<?php echo htmlspecialchars($site['zone_name'] ?? '', ENT_QUOTES); ?>',
                    pincode: '<?php echo htmlspecialchars($site['pincode'] ?? '', ENT_QUOTES); ?>',
                    branch: '<?php echo htmlspecialchars($site['branch'] ?? '', ENT_QUOTES); ?>',
                    customer: '<?php echo htmlspecialchars($site['customer_name'] ?? '', ENT_QUOTES); ?>',
                    contact_person_name: '<?php echo htmlspecialchars($site['contact_person_name'] ?? '', ENT_QUOTES); ?>',
                    contact_person_number: '<?php echo htmlspecialchars($site['contact_person_number'] ?? '', ENT_QUOTES); ?>',
                    contact_person_email: '<?php echo htmlspecialchars($site['contact_person_email'] ?? '', ENT_QUOTES); ?>',
                    vendor: '<?php echo htmlspecialchars($site['vendor_name'] ?? '', ENT_QUOTES); ?>',
                    site_ticket_id: '<?php echo htmlspecialchars($site['site_ticket_id'] ?? '', ENT_QUOTES); ?>'
                };
                
                for (const key in siteMaster) {
                    formData.append(`site_master[${key}]`, siteMaster[key]);
                }
                
                // Add files
                for (const fieldId in this.files) {
                    const fileArray = this.files[fieldId];
                    if (Array.isArray(fileArray)) {
                        fileArray.forEach((file, index) => {
                            formData.append(`file_${fieldId}[]`, file);
                        });
                    } else {
                        formData.append(`file_${fieldId}`, fileArray);
                    }
                }
                
                const response = await fetch('process-survey-dynamic.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Survey submitted successfully!');
                    window.location.href = 'sites/';
                } else {
                    alert('Error: ' + (data.message || 'Failed to submit survey'));
                }
            } catch (err) {
                alert('An error occurred while submitting the survey');
                console.error(err);
            } finally {
                this.submitting = false;
            }
        }
    },
    
    async mounted() {
        await Promise.all([
            this.loadSurvey(),
            this.loadCustomers()
        ]);
    }
}).mount('#dynamicFormApp');
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/admin_layout.php';
?>

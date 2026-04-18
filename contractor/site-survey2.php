<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Site.php';
require_once __DIR__ . '/../models/SiteDelegation.php';
require_once __DIR__ . '/../models/SiteSurvey.php';
require_once __DIR__ . '/../config/database.php';

// Auth check for contractor/vendor
if (Auth::getRole() !== 'contractor' && Auth::getRole() !== 'vendor' && Auth::getRole() !== 'superadmin') {
    header('Location: ' . url('/auth/login.php'));
    exit;
}

$delegationId = $_GET['delegation_id'] ?? null;

if (!$delegationId) {
    header('Location: sites/');
    exit;
}

$siteModel = new Site();
$delegationModel = new SiteDelegation();
$surveyModel = new SiteSurvey();

// Get delegation details
$delegation = $delegationModel->find($delegationId);
if (!$delegation) {
    header('Location: sites/');
    exit;
}

$siteId = $delegation['site_id'];

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
$stmt = $db->prepare("SELECT id, survey_status, submitted_date, is_draft, approval_status, form_data FROM dynamic_survey_responses WHERE delegation_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$delegationId]);
$existingSurvey = $stmt->fetch(PDO::FETCH_ASSOC);

// If survey exists and is submitted (not draft) and not rejected, redirect to view page
if ($existingSurvey && !$existingSurvey['is_draft'] && ($existingSurvey['approval_status'] !== 'rejected' && $existingSurvey['approval_status'] !== 'needs_revision')) {
    header('Location: ../shared/view-survey2.php?id=' . $existingSurvey['id']);
    exit;
}

$title = 'Site Survey - ' . ($site['site_id'] ?? 'New');
ob_start();
?>
<style>
    .form-group { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .slide-up { animation: slideUp 0.4s ease-out; }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .form-label { display: block; font-size: 0.75rem; font-weight: 700; color: #4b5563; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
    .form-input, .form-select, .form-textarea { width: 100%; padding: 0.75rem 1rem; border: 1px solid #e5e7eb; border-radius: 0.75rem; background-color: #f9fafb; font-size: 0.875rem; transition: all 0.2s; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #3b82f6; ring: 3px; ring-color: rgba(59, 130, 246, 0.1); background-color: #fff; }

    /* Fix for bottom action bar overlap */
    .bottom-action-bar {
        position: fixed;
        bottom: 0;
        right: 0;
        left: 256px;
        z-index: 50;
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .sidebar-collapsed .bottom-action-bar { left: 80px; }
    @media (max-width: 1023px) { .bottom-action-bar { left: 0; } }
</style>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">Site Feasibility Survey</h1>
            <p class="mt-2 text-lg text-gray-600">Site: <span class="font-semibold text-blue-600"><?php echo htmlspecialchars($site['site_id'] ?? 'N/A'); ?></span></p>
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

<div class="professional-table bg-white mb-8 shadow-sm rounded-xl overflow-hidden border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
        <h3 class="text-lg font-semibold text-gray-900">Site Information</h3>
        <p class="text-sm text-gray-500 mt-1">Basic site master data</p>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Site ID</label>
                <p class="text-sm text-gray-900 font-bold"><?php echo htmlspecialchars($site['site_id'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Store ID</label>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($site['store_id'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Location</label>
                <p class="text-sm text-gray-900 truncate" title="<?php echo htmlspecialchars($site['location'] ?? 'N/A'); ?>"><?php echo htmlspecialchars($site['location'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Customer</label>
                <p class="text-sm text-gray-900 font-bold text-blue-600"><?php echo htmlspecialchars($site['customer_name'] ?? 'N/A'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Form Container -->
<div id="dynamicFormApp">
    <div v-if="loading" class="p-12 text-center bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-500 font-medium">Loading survey form...</p>
    </div>

    <div v-if="error" class="p-8 bg-white rounded-xl border border-red-200 shadow-sm">
        <div class="flex items-center text-red-600 mb-4">
            <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <h3 class="text-lg font-bold">Form Configuration Error</h3>
        </div>
        <p class="text-gray-700 leading-relaxed">{{ error }}</p>
        <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <p class="text-sm text-gray-600">Target Customer: <span class="font-bold text-gray-900"><?php echo htmlspecialchars($site['customer_name'] ?? 'N/A'); ?></span></p>
            <p class="text-xs text-gray-500 mt-2 italic">Please contact the administrator to assign a survey form to this customer.</p>
        </div>
    </div>

    <template v-if="!loading && !error">
        <!-- Start Survey Banner (shown when not started) -->
        <div v-if="surveyStatus === 'not_started'" class="bg-blue-50 border-l-4 border-blue-500 p-6 m-6 rounded-r-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-8 h-8 text-blue-500 mr-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="text-xl font-bold text-blue-900">Ready to Begin Survey</h3>
                        <p class="text-sm text-blue-700 mt-1">Click "Start Survey" to begin. Your progress will be automatically saved.</p>
                    </div>
                </div>
                <button @click="startSurvey" 
                        type="button"
                        class="inline-flex items-center px-8 py-4 border border-transparent text-base font-bold rounded-lg text-white bg-green-600 hover:bg-green-700 transition-colors shadow-lg hover:shadow-xl transform hover:scale-105">
                    Start Survey
                </button>
            </div>
        </div>
        
        <!-- Preview Mode Banner -->
        <div v-if="isPreviewMode && !isLocked" class="bg-yellow-50 border-l-4 border-yellow-400 p-6 m-6 rounded-r-lg">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-yellow-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-yellow-800">Preview Mode</h3>
                    <p class="text-sm text-yellow-700">Review your answers. Click "Edit" to change or "Submit" to finalize.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-900">{{ survey.title }}</h3>
                <p v-if="survey.description" class="text-sm text-gray-500 mt-1">{{ survey.description }}</p>
            </div>
            
            <div class="p-6" :class="{'pb-32': surveyStatus !== 'not_started'}">
                <form @submit.prevent="submitSurvey" enctype="multipart/form-data" :class="{'pointer-events-none opacity-60': surveyStatus === 'not_started'}">
                    <input type="hidden" name="site_id" value="<?php echo $site['id']; ?>">
                    <input type="hidden" name="delegation_id" value="<?php echo $delegationId; ?>">
                    <input type="hidden" name="survey_form_id" :value="survey.id">
                    
                    <!-- Dynamic Sections -->
                    <template v-for="(section, sIndex) in sections" :key="section.id">
                    <div v-for="rIndex in getRepeatCount(section)" :key="section.id + '_' + rIndex" class="mb-10 last:mb-0" :class="{'border-l-4 border-blue-500 pl-6': section.is_repeatable}">
                        <div class="flex items-center mb-6">
                            <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                            <div>
                                <h4 class="text-xl font-bold text-gray-900">
                                    {{ section.title }}
                                    <span v-if="section.is_repeatable" class="text-blue-500 ml-2">( #{{ rIndex }} )</span>
                                </h4>
                                <p v-if="section.description" class="text-sm text-gray-500 mt-0.5">{{ section.description }}</p>
                            </div>
                        </div>
                        
                        <!-- Main Section Fields -->
                        <div v-if="section.fields && section.fields.length > 0" class="flex flex-wrap gap-x-6 gap-y-6 mb-8">
                            <div v-for="field in section.fields" :key="field.id"
                                 :class="{
                                     'w-full': field.field_width === 'full' || !field.field_width,
                                     'w-full md:w-[calc(50%-0.75rem)]': field.field_width === 'half',
                                     'w-full md:w-[calc(33.333%-1rem)]': field.field_width === 'third',
                                     'w-full md:w-[calc(25%-1.125rem)]': field.field_width === 'quarter'
                                 }"
                                 class="form-group slide-up">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ field.label }}
                                    <span v-if="field.is_required" class="text-rose-500">*</span>
                                </label>

                                <!-- Text, Email, Password, Number -->
                                <input 
                                    v-if="['text', 'email', 'password', 'number'].includes(field.field_type)"
                                    :type="field.field_type"
                                    v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                    :placeholder="field.placeholder"
                                    :required="field.is_required"
                                    :min="field.field_type === 'number' && !field.allow_negative ? '0' : null"
                                    class="form-input w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all">

                                <!-- Textarea -->
                                <textarea 
                                    v-if="field.field_type === 'textarea'"
                                    v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                    :placeholder="field.placeholder"
                                    :required="field.is_required"
                                    class="form-textarea w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all"
                                    rows="4"></textarea>

                                <!-- Date, Time -->
                                <input 
                                    v-if="['date', 'time'].includes(field.field_type)"
                                    :type="field.field_type"
                                    v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                    :required="field.is_required"
                                    class="form-input w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all">

                                <!-- DateTime -->
                                <input 
                                    v-if="['datetime', 'datetime-local'].includes(field.field_type)"
                                    type="datetime-local"
                                    v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                    :required="field.is_required"
                                    class="form-input w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all">

                                <!-- Select Dropdown -->
                                <select 
                                    v-if="field.field_type === 'select'"
                                    v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                    :required="field.is_required"
                                    class="form-select w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all">
                                    <option value="">Select an option...</option>
                                    <option v-for="opt in getOptions(field.options)" :key="opt" :value="opt">{{ opt }}</option>
                                </select>

                                <!-- Radio Buttons -->
                                <div v-if="field.field_type === 'radio'" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1">
                                    <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors" :class="{'bg-blue-50 border-blue-200': formData[getFieldKey(field.id, rIndex, section)] === opt}">
                                        <input 
                                            type="radio" 
                                            :name="'field_' + getFieldKey(field.id, rIndex, section)"
                                            :value="opt"
                                            v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                            :required="field.is_required"
                                            class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                        <span class="ml-3 text-sm font-medium text-gray-700">{{ opt }}</span>
                                    </label>
                                </div>

                                <!-- Checkboxes -->
                                <div v-if="field.field_type === 'checkbox'" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1">
                                    <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors" :class="{'bg-blue-50 border-blue-200': formData[getFieldKey(field.id, rIndex, section)] && formData[getFieldKey(field.id, rIndex, section)].includes(opt)}">
                                        <input 
                                            type="checkbox" 
                                            :value="opt"
                                            @change="updateCheckbox(getFieldKey(field.id, rIndex, section), opt, $event)"
                                            class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                        <span class="ml-3 text-sm font-medium text-gray-700">{{ opt }}</span>
                                    </label>
                                </div>

                                <!-- File Upload -->
                                <div v-if="field.field_type === 'file'" class="mt-1">
                                    <div class="relative group">
                                        <div class="flex items-center justify-center w-full px-4 py-6 border-2 border-dashed border-gray-300 rounded-2xl hover:border-blue-400 hover:bg-blue-50 transition-all cursor-pointer group-hover:bg-blue-50/50">
                                            <div class="text-center">
                                                <svg class="mx-auto h-10 w-10 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                                </svg>
                                                <div class="mt-2 flex text-sm text-gray-600">
                                                    <span class="font-semibold text-blue-600 hover:text-blue-500">Click to upload</span>
                                                    <p class="pl-1">or drag and drop</p>
                                                </div>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    {{ field.allow_multiple ? `Up to ${field.max_files} files` : 'Single file only' }}
                                                </p>
                                            </div>
                                            <input 
                                                type="file"
                                                :multiple="field.allow_multiple"
                                                :accept="getFileAccept(field)"
                                                @change="handleFileUpload(getFieldKey(field.id, rIndex, section), $event, field)"
                                                :required="field.is_required && (!files[getFieldKey(field.id, rIndex, section)] || files[getFieldKey(field.id, rIndex, section)].length === 0)"
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                        </div>
                                    </div>
                                    
                                    <!-- File Preview -->
                                    <div v-if="filePreviews[getFieldKey(field.id, rIndex, section)] && filePreviews[getFieldKey(field.id, rIndex, section)].length > 0" class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                        <div v-for="(preview, index) in filePreviews[getFieldKey(field.id, rIndex, section)]" :key="index" class="relative group rounded-xl overflow-hidden shadow-sm border border-gray-200 bg-gray-50 aspect-video">
                                            <img v-if="preview.type === 'image'" :src="preview.url" class="w-full h-full object-cover">
                                            <div v-else class="w-full h-full flex flex-col items-center justify-center p-3">
                                                <svg class="w-8 h-8 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                </svg>
                                                <p class="text-[10px] text-gray-600 font-bold text-center truncate w-full px-1">{{ preview.name }}</p>
                                            </div>
                                            <button @click="removeFile(getFieldKey(field.id, rIndex, section), index)" type="button" class="absolute top-1 right-1 w-6 h-6 bg-red-500/90 text-white rounded-lg opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <p v-if="field.help_text" class="text-xs text-gray-400 mt-2 flex items-center italic">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ field.help_text }}
                                </p>
                            </div>
                        </div>

                        <!-- Subsections -->
                        <div v-if="section.subsections && section.subsections.length > 0" class="space-y-6">
                            <div v-for="subsection in section.subsections" :key="subsection.id" class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100">
                                <div class="flex items-center mb-6">
                                    <h5 class="text-lg font-bold text-gray-800 flex items-center">
                                        <div class="w-1.5 h-1.5 bg-blue-400 rounded-full mr-3"></div>
                                        {{ subsection.title }}
                                    </h5>
                                </div>
                                
                                <div class="flex flex-wrap gap-x-6 gap-y-6">
                                    <div v-for="field in subsection.fields" :key="field.id"
                                         :class="{
                                             'w-full': field.field_width === 'full' || !field.field_width,
                                             'w-full md:w-[calc(50%-0.75rem)]': field.field_width === 'half',
                                             'w-full md:w-[calc(33.333%-1rem)]': field.field_width === 'third',
                                             'w-full md:w-[calc(25%-1.125rem)]': field.field_width === 'quarter'
                                         }"
                                         class="form-group">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            {{ field.label }}
                                            <span v-if="field.is_required" class="text-rose-500">*</span>
                                        </label>

                                        <!-- Text, Email, Password, Number -->
                                        <input 
                                            v-if="['text', 'email', 'password', 'number', 'date', 'time', 'datetime-local'].includes(field.field_type)"
                                            :type="field.field_type"
                                            v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                            :placeholder="field.placeholder"
                                            :required="field.is_required"
                                            class="form-input w-full px-4 py-3 rounded-xl border-gray-200">

                                        <!-- Select -->
                                        <select v-if="field.field_type === 'select'" v-model="formData[getFieldKey(field.id, rIndex, section)]" class="form-select w-full px-4 py-3 rounded-xl border-gray-200">
                                            <option value="">Select...</option>
                                            <option v-for="opt in getOptions(field.options)" :key="opt" :value="opt">{{ opt }}</option>
                                        </select>

                                        <!-- Radio -->
                                        <div v-if="field.field_type === 'radio'" class="space-y-2">
                                            <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center">
                                                <input type="radio" :value="opt" v-model="formData[getFieldKey(field.id, rIndex, section)]" class="mr-2">
                                                <span class="text-sm">{{ opt }}</span>
                                            </label>
                                        </div>

                                        <!-- File -->
                                        <input v-if="field.field_type === 'file'" type="file" @change="handleFileUpload(getFieldKey(field.id, rIndex, section), $event, field)" class="form-input w-full border-dashed">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Consolidated Summary (Repositioned after Camera Section) -->
                    <template v-if="isCameraSection(section)">
                    <div v-if="Object.keys(totals).length > 1" class="mt-8 mb-12 bg-gradient-to-br from-blue-700 to-blue-900 rounded-3xl shadow-2xl overflow-hidden ring-1 ring-white/20">
                        <div class="px-8 py-6 border-b border-white/10 flex items-center justify-between bg-white/5 backdrop-blur-sm">
                            <div>
                                <h4 class="text-2xl font-black text-white flex items-center gap-3 tracking-tight">
                                    <span class="p-2 bg-blue-500/30 rounded-xl">
                                        <svg class="w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    </span>
                                    Camera Deployment Summary
                                </h4>
                                <p class="text-blue-200 text-sm mt-1 font-medium italic opacity-80">Real-time hardware consolidation across all floors</p>
                            </div>
                            <div class="flex flex-col items-end">
                                <div class="px-4 py-1.5 bg-green-500/20 rounded-full border border-green-500/30 text-green-300 text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                                    Live calculation
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-8">
                            <!-- Overall Hardware Total Card -->
                            <div class="mb-8 p-6 bg-gradient-to-r from-green-500/20 to-blue-500/20 border-2 border-green-500/40 rounded-2xl flex items-center justify-between">
                                <div>
                                    <h5 class="text-green-300 text-xs font-black uppercase tracking-[0.2em] mb-1">TOTAL CAMERA HARDWARE</h5>
                                    <p class="text-blue-100 text-[10px] font-medium opacity-70">(SLP + Analytical + Blind Spot)</p>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="text-5xl font-black text-white tracking-tighter drop-shadow-lg leading-none">{{ totals._overallTotal || 0 }}</span>
                                    <span class="text-green-400 text-[10px] font-bold mt-1">Units Consolidated</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <template v-for="field in (section.fields || [])" :key="'total_field_' + field.id">
                                    <div v-if="field.field_type === 'number'" 
                                         class="bg-white/5 backdrop-blur-md border border-white/10 p-6 rounded-2xl hover:bg-white/10 hover:border-white/20 transition-all duration-300 group">
                                        <div class="flex flex-col">
                                            <span class="text-blue-200 text-[10px] font-black uppercase tracking-widest mb-2 opacity-60">{{ field.label }}</span>
                                            <div class="flex items-baseline justify-between">
                                                <span class="text-3xl font-extrabold text-white tracking-tight group-hover:scale-110 transition-transform origin-left">{{ totals[field.id] || 0 }}</span>
                                                <div class="text-[10px] bg-white/10 px-2 py-1 rounded text-white/50 font-bold">qty</div>
                                            </div>
                                            <div class="mt-4 pt-4 border-t border-white/5 flex items-center justify-between text-[10px]">
                                                <span class="text-blue-300/50 font-medium">Aggregated Count</span>
                                                <span class="text-blue-300 font-black tracking-wider uppercase">{{ getRepeatCount(section) }} FLR</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    </template>
                    </template>


                    <!-- Survey Control Buttons - Fixed at Bottom -->
                    <div class="bottom-action-bar bg-white border-t border-gray-200 shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
                        <div class="px-6 py-4">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-4">
                                    <a href="sites/" class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                        Cancel
                                    </a>
                                    <span v-if="lastSavedAt" class="text-sm text-gray-500 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Saved: {{ formatTime(lastSavedAt) }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    <button v-if="surveyStatus === 'not_started'" @click="startSurvey" type="button" class="px-8 py-3 bg-green-600 text-white font-bold rounded-xl shadow-lg hover:bg-green-700 transition-all">
                                        Start Survey
                                    </button>
                                    
                                    <button v-if="surveyStatus === 'in_progress'" @click="saveDraft" type="button" :disabled="saving" class="px-6 py-3 border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 disabled:opacity-50 transition-all">
                                        {{ saving ? 'Saving...' : 'Save Draft' }}
                                    </button>
                                    
                                    <button v-if="surveyStatus === 'in_progress'" @click="endSurvey" type="button" class="px-8 py-3 bg-blue-600 text-white font-bold rounded-xl shadow-lg hover:bg-blue-700 transition-all">
                                        End & Preview
                                    </button>
                                    
                                    <button v-if="surveyStatus === 'ended' && !isLocked" @click="backToEdit" type="button" class="px-6 py-3 border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-all">
                                        Edit Survey
                                    </button>
                                    
                                    <button v-if="surveyStatus === 'ended' && !isLocked" @click="finalSubmit" type="button" :disabled="submitting" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50">
                                        Submit Assessment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="h-24"></div>
                </form>
            </div>
        </div>
    </template>
</div>

<style>
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
    }
    
    .slide-up {
        animation: slideUp 0.4s ease-out;
    }
    
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
const { createApp } = Vue;
const BASE_URL = '<?php echo url(); ?>';

createApp({
    data() {
        return {
            surveyFormId: <?php echo json_encode($surveyFormId); ?>,
            loading: true,
            error: null,
            survey: {},
            sections: [],
            formData: {},
            files: {},
            filePreviews: {},
            submitting: false,
            // State management
            surveyResponseId: <?php echo json_encode($existingSurvey['id'] ?? null); ?>,
            surveyStatus: <?php echo json_encode($existingSurvey ? ($existingSurvey['is_draft'] ? 'in_progress' : 'submitted') : 'not_started'); ?>,
            isPreviewMode: false,
            isLocked: <?php echo json_encode($existingSurvey && !$existingSurvey['is_draft'] && $existingSurvey['approval_status'] === 'approved'); ?>,
            lastSavedAt: null,
            saving: false
        };
    },
    computed: {
        totals() {
            const res = { _overallTotal: 0 };
            this.sections.forEach(section => {
                if (section.is_repeatable) {
                    const count = this.getRepeatCount(section);
                    if (section.fields) {
                        section.fields.forEach(field => {
                            if (field.field_type === 'number') {
                                let sum = 0;
                                for (let i = 1; i <= count; i++) {
                                    sum += parseFloat(this.formData[this.getFieldKey(field.id, i, section)]) || 0;
                                }
                                res[field.id] = sum;
                                
                                const label = (field.label || '').toLowerCase();
                                if (label.includes('slp camera') || label.includes('analytical camera') || label.includes('blind spot')) {
                                    res._overallTotal += sum;
                                }
                            }
                        });
                    }
                }
            });
            return res;
        }
    },
    methods: {
        isCameraSection(section) {
            return (section.title || '').toLowerCase().includes('camera details');
        },
        getFieldKey(fieldId, rIndex, section) {
            if (!section || !section.is_repeatable) return fieldId;
            return `${fieldId}_${rIndex}`;
        },
        getRepeatCount(section) {
            const sectionTitle = (section.title || '').trim().toLowerCase();
            if (sectionTitle.includes('camera details') || section.is_repeatable) {
                // Find "No of Floors" field
                let floorCount = 1;
                this.sections.forEach(s => {
                    if (s.fields) {
                        s.fields.forEach(f => {
                            if (f && f.label && f.label.toLowerCase().includes('no of floor')) {
                                floorCount = parseInt(this.formData[f.id]) || 1;
                            }
                        });
                    }
                });
                return floorCount;
            }
            return 1;
        },
        formatTime(date) {
            if (!date) return '';
            return new Intl.DateTimeFormat('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).format(date);
        },
        startSurvey() {
            this.surveyStatus = 'in_progress';
            this.saveDraft();
        },
        endSurvey() {
            this.isPreviewMode = true;
            this.surveyStatus = 'ended';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        backToEdit() {
            this.isPreviewMode = false;
            this.surveyStatus = 'in_progress';
        },
        async saveDraft() {
            this.saving = true;
            try {
                const formData = new FormData();
                formData.append('site_id', '<?php echo $site['id']; ?>');
                formData.append('delegation_id', '<?php echo $delegationId; ?>');
                formData.append('survey_form_id', this.survey.id);
                formData.append('form_data', JSON.stringify(this.formData));
                formData.append('is_draft', '1');
                if (this.surveyResponseId) formData.append('response_id', this.surveyResponseId);
                
                const response = await fetch('process-survey-dynamic.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    this.surveyResponseId = data.response_id;
                    this.lastSavedAt = new Date();
                }
            } catch (err) { console.error('Auto-save failed', err); }
            finally { this.saving = false; }
        },
        submitSurvey() {
            if (this.surveyStatus === 'not_started') {
                this.startSurvey();
            } else if (this.surveyStatus === 'in_progress') {
                this.endSurvey();
            } else if (this.surveyStatus === 'ended') {
                this.finalSubmit();
            }
        },
        finalSubmit() {
            this.finalSubmitAsync();
        },
        async finalSubmitAsync() {
            const confirmed = await showConfirm(
                'Submit Survey?',
                'Are you sure your assessment is complete? Once submitted, it will be reviewed by the administration.',
                { confirmText: 'Yes, Submit Now', confirmType: 'success' }
            );

            if (!confirmed) return;

            this.submitting = true;
            try {
                const formData = new FormData();
                formData.append('site_id', '<?php echo $site['id']; ?>');
                formData.append('delegation_id', '<?php echo $delegationId; ?>');
                formData.append('survey_form_id', this.survey.id);
                formData.append('form_data', JSON.stringify(this.formData));
                formData.append('is_draft', '0');
                if (this.surveyResponseId) formData.append('response_id', this.surveyResponseId);
                
                // Add site master data for robust tracking
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
                    this.files[fieldId].forEach(file => formData.append(`file_${fieldId}[]`, file));
                }
                
                const response = await fetch('process-survey-dynamic.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                if (data.success) {
                    showToast('Assessment submitted successfully!', 'success');
                    setTimeout(() => window.location.href = 'sites/', 1500);
                } else {
                    showAlert(data.message || 'Error occurred during submission.', 'error');
                }
            } catch (err) {
                showAlert('Network error. Check your connection.', 'error');
            } finally {
                this.submitting = false;
            }
        },
        getOptions(optionsString) {
            if (!optionsString) return [];
            return optionsString.split(',').map(opt => opt.trim()).filter(opt => opt);
        },
        updateCheckbox(key, value, event) {
            if (!this.formData[key]) this.formData[key] = [];
            if (event.target.checked) {
                this.formData[key].push(value);
            } else {
                const index = this.formData[key].indexOf(value);
                if (index > -1) this.formData[key].splice(index, 1);
            }
        },
        handleFileUpload(fieldId, event, field) {
            const selectedFiles = Array.from(event.target.files);
            const maxFiles = field.allow_multiple ? (field.max_files || 5) : 1;
            const maxSize = (field.max_file_size || 5) * 1024 * 1024;
            
            if (selectedFiles.length > maxFiles) {
                showAlert(`Limit exceeded: Max ${maxFiles} files allowed.`, 'error');
                event.target.value = '';
                return;
            }
            
            const validFiles = [];
            const acceptedTypes = this.getFileAcceptArray(field);
            
            for (const file of selectedFiles) {
                if (file.size > maxSize) {
                    showAlert(`File too large: "${file.name}" exceeds ${field.max_file_size}MB.`, 'error');
                    continue;
                }
                validFiles.push(file);
            }
            
            if (validFiles.length > 0) {
                this.files[fieldId] = field.allow_multiple ? validFiles : [validFiles[0]];
                this.generatePreviews(fieldId, validFiles);
            }
        },
        generatePreviews(fieldId, files) {
            this.filePreviews[fieldId] = [];
            files.forEach(file => {
                const preview = {
                    name: file.name,
                    size: file.size,
                    type: file.type.startsWith('image/') ? 'image' : 'document',
                    url: null
                };
                if (preview.type === 'image') {
                    const reader = new FileReader();
                    reader.onload = (e) => { preview.url = e.target.result; this.filePreviews[fieldId].push(preview); };
                    reader.readAsDataURL(file);
                } else {
                    this.filePreviews[fieldId].push(preview);
                }
            });
        },
        removeFile(fieldId, index) {
            this.files[fieldId].splice(index, 1);
            this.filePreviews[fieldId].splice(index, 1);
        },
        getFileAccept(field) {
            return this.getFileAcceptArray(field).join(',');
        },
        getFileAcceptArray(field) {
            if (!field.file_type_restriction) return [];
            const typeMap = {
                'image': ['image/*'],
                'document': ['.pdf', '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx'],
                'image_document': ['image/*', '.pdf', '.doc', '.docx', '.xls', '.xlsx']
            };
            return field.file_type_restriction === 'custom' ? field.custom_file_types.split(',').map(t => t.trim()) : (typeMap[field.file_type_restriction] || []);
        },
        async loadSurvey() {
            if (!this.surveyFormId) {
                this.error = 'No survey form found for this customer. Please contact the administrator.';
                this.loading = false;
                return;
            }

            try {
                const response = await fetch(`${BASE_URL}/api/surveys_v2.php?action=load&id=${this.surveyFormId}`);
                const data = await response.json();
                
                if (data.success) {
                    this.survey = data.survey;
                    this.sections = data.sections;
                    
                    // Initialize formData from existing survey if available
                    if (<?php echo json_encode(!!$existingSurvey); ?>) {
                        try {
                            const rawData = <?php echo $existingSurvey ? json_encode($existingSurvey['form_data']) : '{}'; ?>;
                            this.formData = typeof rawData === 'string' ? JSON.parse(rawData) : rawData;
                            
                            // Set initial status accurately
                            if (this.surveyStatus === 'in_progress') {
                                this.startSurvey();
                            }
                        } catch (e) {
                            console.error('Error parsing data', e);
                            this.initEmptyData();
                        }
                    } else {
                        this.initEmptyData();
                    }
                } else {
                    this.error = data.message || 'Failed to load survey form configuration.';
                }
            } catch (err) {
                this.error = 'Network error occurred.';
            } finally {
                this.loading = false;
            }
        },
        initEmptyData() {
            this.sections.forEach(section => {
                const count = section.is_repeatable ? this.getRepeatCount(section) : 1;
                for (let i = 1; i <= count; i++) {
                    this.initFields(section.fields, i, section);
                    if (section.subsections) {
                        section.subsections.forEach(sub => this.initFields(sub.fields, i, section));
                    }
                }
            });
        },
        initFields(fields, rIndex = 1, section = null) {
            if (!fields) return;
            fields.forEach(field => {
                const key = this.getFieldKey(field.id, rIndex, section);
                if (field.field_type === 'checkbox') {
                    this.formData[key] = [];
                } else {
                    this.formData[key] = field.default_value || '';
                }
            });
        }
    },
    mounted() {
        this.loadSurvey();
        
        // Setup Auto-save every 2 minutes
        this.autoSaveInterval = setInterval(() => {
            if (this.surveyStatus === 'in_progress' && !this.saving && !this.isPreviewMode) {
                this.saveDraft();
            }
        }, 120000);
    },
    beforeUnmount() {
        if (this.autoSaveInterval) clearInterval(this.autoSaveInterval);
    }
}).mount('#dynamicFormApp');
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>

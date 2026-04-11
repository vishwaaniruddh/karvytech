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

// Check if survey already exists for this delegation
$stmt = $db->prepare("SELECT id, survey_status, submitted_date FROM dynamic_survey_responses WHERE delegation_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$delegationId]);
$existingSurvey = $stmt->fetch(PDO::FETCH_ASSOC);

// If survey exists, redirect to view page (if we have a shared view page)
if ($existingSurvey) {
    header('Location: ../shared/view-survey2.php?id=' . $existingSurvey['id']);
    exit;
}

$title = 'Site Survey - ' . ($site['site_id'] ?? 'New');
ob_start();
?>

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
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-900">{{ survey.title }}</h3>
                <p v-if="survey.description" class="text-sm text-gray-500 mt-1">{{ survey.description }}</p>
            </div>
            
            <div class="p-6">
                <form @submit.prevent="submitSurvey" enctype="multipart/form-data">
                    <input type="hidden" name="site_id" value="<?php echo $site['id']; ?>">
                    <input type="hidden" name="delegation_id" value="<?php echo $delegationId; ?>">
                    <input type="hidden" name="survey_form_id" :value="survey.id">
                    
                    <!-- Dynamic Sections -->
                    <div v-for="(section, sIndex) in sections" :key="section.id" class="mb-10 last:mb-0">
                        <div class="flex items-center mb-6">
                            <div class="w-1 h-6 bg-blue-600 rounded-full mr-3"></div>
                            <div>
                                <h4 class="text-xl font-bold text-gray-900">{{ section.title }}</h4>
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
                                    v-model="formData[field.id]"
                                    :placeholder="field.placeholder"
                                    :required="field.is_required"
                                    :min="field.field_type === 'number' && !field.allow_negative ? '0' : null"
                                    class="form-input w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all">

                                <!-- Textarea -->
                                <textarea 
                                    v-if="field.field_type === 'textarea'"
                                    v-model="formData[field.id]"
                                    :placeholder="field.placeholder"
                                    :required="field.is_required"
                                    class="form-textarea w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all"
                                    rows="4"></textarea>

                                <!-- Date, Time -->
                                <input 
                                    v-if="['date', 'time'].includes(field.field_type)"
                                    :type="field.field_type"
                                    v-model="formData[field.id]"
                                    :required="field.is_required"
                                    class="form-input w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all">

                                <!-- DateTime -->
                                <input 
                                    v-if="['datetime', 'datetime-local'].includes(field.field_type)"
                                    type="datetime-local"
                                    v-model="formData[field.id]"
                                    :required="field.is_required"
                                    class="form-input w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all">

                                <!-- Select Dropdown -->
                                <select 
                                    v-if="field.field_type === 'select'"
                                    v-model="formData[field.id]"
                                    :required="field.is_required"
                                    class="form-select w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all">
                                    <option value="">Select an option...</option>
                                    <option v-for="opt in getOptions(field.options)" :key="opt" :value="opt">{{ opt }}</option>
                                </select>

                                <!-- Radio Buttons -->
                                <div v-if="field.field_type === 'radio'" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1">
                                    <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors" :class="{'bg-blue-50 border-blue-200': formData[field.id] === opt}">
                                        <input 
                                            type="radio" 
                                            :name="'field_' + field.id"
                                            :value="opt"
                                            v-model="formData[field.id]"
                                            :required="field.is_required"
                                            class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                        <span class="ml-3 text-sm font-medium text-gray-700">{{ opt }}</span>
                                    </label>
                                </div>

                                <!-- Checkboxes -->
                                <div v-if="field.field_type === 'checkbox'" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1">
                                    <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors" :class="{'bg-blue-50 border-blue-200': formData[field.id] && formData[field.id].includes(opt)}">
                                        <input 
                                            type="checkbox" 
                                            :value="opt"
                                            @change="updateCheckbox(field.id, opt, $event)"
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
                                                @change="handleFileUpload(field.id, $event, field)"
                                                :required="field.is_required && (!files[field.id] || files[field.id].length === 0)"
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                        </div>
                                    </div>
                                    
                                    <!-- File Preview -->
                                    <div v-if="filePreviews[field.id] && filePreviews[field.id].length > 0" class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                        <div v-for="(preview, index) in filePreviews[field.id]" :key="index" class="relative group rounded-xl overflow-hidden shadow-sm border border-gray-200 bg-gray-50 aspect-video">
                                            <img v-if="preview.type === 'image'" :src="preview.url" class="w-full h-full object-cover">
                                            <div v-else class="w-full h-full flex flex-col items-center justify-center p-3">
                                                <svg class="w-8 h-8 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                </svg>
                                                <p class="text-[10px] text-gray-600 font-bold text-center truncate w-full px-1">{{ preview.name }}</p>
                                            </div>
                                            <button @click="removeFile(field.id, index)" type="button" class="absolute top-1 right-1 w-6 h-6 bg-red-500/90 text-white rounded-lg opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
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
                                            v-if="['text', 'email', 'password', 'number'].includes(field.field_type)"
                                            :type="field.field_type"
                                            v-model="formData[field.id]"
                                            :placeholder="field.placeholder"
                                            :required="field.is_required"
                                            :min="field.field_type === 'number' && !field.allow_negative ? '0' : null"
                                            class="form-input w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all">

                                        <!-- Textarea -->
                                        <textarea 
                                            v-if="field.field_type === 'textarea'"
                                            v-model="formData[field.id]"
                                            :placeholder="field.placeholder"
                                            :required="field.is_required"
                                            class="form-textarea w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all"
                                            rows="4"></textarea>

                                        <!-- Date, Time -->
                                        <input 
                                            v-if="['date', 'time'].includes(field.field_type)"
                                            :type="field.field_type"
                                            v-model="formData[field.id]"
                                            :required="field.is_required"
                                            class="form-input w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all">

                                        <!-- DateTime -->
                                        <input 
                                            v-if="['datetime', 'datetime-local'].includes(field.field_type)"
                                            type="datetime-local"
                                            v-model="formData[field.id]"
                                            :required="field.is_required"
                                            class="form-input w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all">

                                        <!-- Select Dropdown -->
                                        <select 
                                            v-if="field.field_type === 'select'"
                                            v-model="formData[field.id]"
                                            :required="field.is_required"
                                            class="form-select w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all">
                                            <option value="">Select an option...</option>
                                            <option v-for="opt in getOptions(field.options)" :key="opt" :value="opt">{{ opt }}</option>
                                        </select>
                                        
                                        <!-- Radio Buttons -->
                                        <div v-if="field.field_type === 'radio'" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1">
                                            <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors" :class="{'bg-blue-50 border-blue-200': formData[field.id] === opt}">
                                                <input 
                                                    type="radio" 
                                                    :name="'field_' + field.id"
                                                    :value="opt"
                                                    v-model="formData[field.id]"
                                                    :required="field.is_required"
                                                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                                <span class="ml-3 text-sm font-medium text-gray-700">{{ opt }}</span>
                                            </label>
                                        </div>

                                        <!-- Checkboxes -->
                                        <div v-if="field.field_type === 'checkbox'" class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1">
                                            <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors" :class="{'bg-blue-50 border-blue-200': formData[field.id] && formData[field.id].includes(opt)}">
                                                <input 
                                                    type="checkbox" 
                                                    :value="opt"
                                                    @change="updateCheckbox(field.id, opt, $event)"
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
                                                        @change="handleFileUpload(field.id, $event, field)"
                                                        :required="field.is_required && (!files[field.id] || files[field.id].length === 0)"
                                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                                </div>
                                            </div>
                                            
                                            <!-- File Preview -->
                                            <div v-if="filePreviews[field.id] && filePreviews[field.id].length > 0" class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                                <div v-for="(preview, index) in filePreviews[field.id]" :key="index" class="relative group rounded-xl overflow-hidden shadow-sm border border-gray-200 bg-gray-50 aspect-video">
                                                    <img v-if="preview.type === 'image'" :src="preview.url" class="w-full h-full object-cover">
                                                    <div v-else class="w-full h-full flex flex-col items-center justify-center p-3">
                                                        <svg class="w-8 h-8 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <p class="text-[10px] text-gray-600 font-bold text-center truncate w-full px-1">{{ preview.name }}</p>
                                                    </div>
                                                    <button @click="removeFile(field.id, index)" type="button" class="absolute top-1 right-1 w-6 h-6 bg-red-500/90 text-white rounded-lg opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="mt-12 pt-8 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center bg-white">
                        <a href="sites/" class="order-2 sm:order-1 mt-4 sm:mt-0 px-8 py-3 text-gray-600 font-bold rounded-xl hover:bg-gray-100 transition-all">
                            Discard Survey
                        </a>
                        <button type="submit" :disabled="submitting" class="order-1 sm:order-2 w-full sm:w-auto px-10 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 hover:shadow-xl hover:scale-[1.02] focus:ring-4 focus:ring-blue-200 active:scale-95 transition-all disabled:opacity-50 disabled:pointer-events-none flex items-center justify-center">
                            <svg v-if="submitting" class="animate-spin h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ submitting ? 'Processing submission...' : 'Finalize & Submit Survey' }}
                        </button>
                    </div>
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
            submitting: false
        };
    },
    methods: {
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
                    
                    // Initialize formData
                    this.sections.forEach(section => {
                        this.initFields(section.fields);
                        if (section.subsections) {
                            section.subsections.forEach(sub => this.initFields(sub.fields));
                        }
                    });
                } else {
                    this.error = data.message || 'Failed to load survey form configuration.';
                }
            } catch (err) {
                this.error = 'Network error occurred while fetching form configuration.';
                console.error(err);
            } finally {
                this.loading = false;
            }
        },
        
        initFields(fields) {
            if (!fields) return;
            fields.forEach(field => {
                if (field.field_type === 'checkbox') {
                    this.formData[field.id] = [];
                } else {
                    this.formData[field.id] = '';
                }
            });
        },
        
        getOptions(optionsString) {
            if (!optionsString) return [];
            return optionsString.split(',').map(opt => opt.trim()).filter(opt => opt);
        },
        
        updateCheckbox(fieldId, value, event) {
            if (!this.formData[fieldId]) this.formData[fieldId] = [];
            if (event.target.checked) {
                this.formData[fieldId].push(value);
            } else {
                const index = this.formData[fieldId].indexOf(value);
                if (index > -1) this.formData[fieldId].splice(index, 1);
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
                
                if (acceptedTypes.length > 0) {
                    const fileExt = '.' + file.name.split('.').pop().toLowerCase();
                    const mimeType = file.type.toLowerCase();
                    const isValid = acceptedTypes.some(type => type.startsWith('.') ? fileExt === type : mimeType.includes(type.replace('*', '')));
                    
                    if (!isValid) {
                        showAlert(`Format error: "${file.name}" is not an allowed type.`, 'error');
                        continue;
                    }
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
        
        async submitSurvey() {
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
                showAlert('Network error. Reference check connection.', 'error');
            } finally {
                this.submitting = false;
            }
        }
    },
    mounted() {
        this.loadSurvey();
    }
}).mount('#dynamicFormApp');
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/vendor_layout.php';
?>

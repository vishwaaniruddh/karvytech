<?php
require_once __DIR__ . '/../config/auth.php';

if (!Auth::isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$responseId = $_GET['id'] ?? null;
if (!$responseId) {
    header('Location: ../admin/surveys/index2.php');
    exit;
}

$title = 'Edit Survey';
ob_start();
?>

<style>
    .form-group { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .form-section { animation: slideIn 0.5s ease-out; }
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(20px); }
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
    
    .sidebar-collapsed .bottom-action-bar {
        left: 80px;
    }
    
    @media (max-width: 1023px) {
        .bottom-action-bar {
            left: 0;
        }
    }
</style>

<div id="editSurveyApp">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900">Edit Survey Response</h1>
                <p class="mt-2 text-lg text-gray-600">
                    <span v-if="surveyResponse">Site: <span class="font-semibold text-blue-600">{{ surveyResponse.site_id || 'Unknown' }}</span></span>
                    <span v-else>Loading...</span>
                </p>
            </div>
            <div class="mt-6 lg:mt-0 lg:ml-6">
                <a href="view-survey2.php?id=<?php echo $responseId; ?>"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                    Cancel
                </a>
            </div>
        </div>
    </div>

    <div v-if="loading" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">Loading survey data...</p>
    </div>

    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6">
        <p class="text-red-800">{{ error }}</p>
    </div>

    <div v-else class="professional-table bg-white">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">{{ surveyResponse?.survey_title || 'Survey' }}</h3>
        <p v-if="surveyResponse?.survey_description" class="text-sm text-gray-500 mt-1">{{ surveyResponse.survey_description }}</p>
    </div>

    <div class="p-6 pb-32">
        <form @submit.prevent="updateSurvey" enctype="multipart/form-data">
            <input type="hidden" name="response_id" value="<?php echo $responseId; ?>">

            <!-- Render form fields based on structure -->
            <template v-for="(section, sIndex) in sections" :key="section.id">
            <div v-for="rIndex in getRepeatCount(section)" :key="section.id + '_' + rIndex" class="form-section mb-8" :class="{'border-l-4 border-blue-500 pl-6': section.is_repeatable || section.title.toLowerCase().includes('floor wise')}">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">
                    {{ section.title }}
                    <span v-if="section.is_repeatable || section.title.toLowerCase().includes('floor wise')" class="text-blue-500 ml-2">( #{{ rIndex }} )</span>
                </h4>
                <p v-if="section.description" class="text-sm text-gray-500 mb-4">{{ section.description }}</p>

                <!-- Section Fields -->
                <div v-if="section.fields && section.fields.length > 0" class="flex flex-wrap gap-6 mb-6">
                    <div v-for="field in section.fields" :key="field.id" :class="{
                             'w-full': field.field_width === 'full' || !field.field_width,
                             'w-full md:w-[calc(50%-0.75rem)]': field.field_width === 'half',
                             'w-full md:w-[calc(33.333%-1rem)]': field.field_width === 'third',
                             'w-full md:w-[calc(25%-1.125rem)]': field.field_width === 'quarter'
                         }" class="form-group">
                        <label class="form-label">
                            {{ field.label }}
                            <span v-if="field.is_required" class="text-red-500">*</span>
                        </label>

                        <!-- Text, Email, Password, Number -->
                        <input v-if="['text', 'email', 'password', 'number'].includes(field.field_type)"
                            :type="field.field_type" v-model="formData[getFieldKey(field.id, rIndex, section)]" :placeholder="field.placeholder"
                            :required="field.is_required" class="form-input">

                        <!-- Textarea -->
                        <textarea v-if="field.field_type === 'textarea'" v-model="formData[getFieldKey(field.id, rIndex, section)]"
                            :placeholder="field.placeholder" :required="field.is_required" class="form-textarea"
                            rows="3"></textarea>

                        <!-- Date, Time -->
                        <input v-if="['date', 'time'].includes(field.field_type)" :type="field.field_type"
                            v-model="formData[getFieldKey(field.id, rIndex, section)]" :required="field.is_required" class="form-input">

                        <!-- DateTime -->
                        <input v-if="['datetime', 'datetime-local'].includes(field.field_type)" type="datetime-local"
                            v-model="formData[getFieldKey(field.id, rIndex, section)]" :required="field.is_required" class="form-input">

                        <!-- Select Dropdown -->
                        <select v-if="field.field_type === 'select'" v-model="formData[getFieldKey(field.id, rIndex, section)]"
                            :required="field.is_required" class="form-select">
                            <option value="">Select an option...</option>
                            <option v-for="opt in getOptions(field.options)" :key="opt" :value="opt">{{ opt }}</option>
                        </select>

                        <!-- Radio Buttons -->
                        <div v-if="field.field_type === 'radio'" class="space-y-2">
                            <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center">
                                <input type="radio" :name="'field_' + getFieldKey(field.id, rIndex, section)" :value="opt"
                                    v-model="formData[getFieldKey(field.id, rIndex, section)]" :required="field.is_required" class="mr-2">
                                <span class="text-sm">{{ opt }}</span>
                            </label>
                        </div>

                        <!-- Checkboxes -->
                        <div v-if="field.field_type === 'checkbox'" class="space-y-2">
                            <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center">
                                <input type="checkbox" :value="opt" @change="updateCheckbox(getFieldKey(field.id, rIndex, section), opt, $event)"
                                    :checked="isCheckboxChecked(getFieldKey(field.id, rIndex, section), opt)" class="mr-2">
                                <span class="text-sm">{{ opt }}</span>
                            </label>
                        </div>

                        <!-- File Upload -->
                        <div v-if="field.field_type === 'file'">
                            <!-- Show existing files -->
                            <div v-if="hasExistingFiles(getFieldKey(field.id, rIndex, section))" class="mb-3">
                                <p class="text-xs text-gray-600 mb-2">Current files:</p>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mb-2">
                                    <div v-for="(file, index) in getExistingFiles(getFieldKey(field.id, rIndex, section))" :key="index"
                                        class="relative group">
                                        <div v-if="isImageFile(file)" class="border rounded relative">
                                            <img :src="'../' + file.file_path" :alt="file.original_name"
                                                class="w-full h-24 object-cover rounded">
                                            <button type="button" @click="triggerDelete(getFieldKey(field.id, rIndex, section), index)"
                                                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors flex items-center justify-center shadow-lg">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div v-else
                                            class="border rounded h-24 flex flex-col items-center justify-center bg-gray-50 relative">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            <span class="text-xs text-gray-600 mt-1 px-1 text-center truncate w-full">{{
                                                file.original_name }}</span>
                                            <button type="button" @click="triggerDelete(getFieldKey(field.id, rIndex, section), index)"
                                                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors flex items-center justify-center shadow-lg">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Show newly selected files preview -->
                            <div v-if="hasNewFiles(getFieldKey(field.id, rIndex, section))" class="mb-3">
                                <p class="text-xs text-green-600 font-semibold mb-2">New files to upload:</p>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mb-2">
                                    <div v-for="(file, index) in getNewFiles(getFieldKey(field.id, rIndex, section))" :key="'new_' + index"
                                        class="relative group">
                                        <div v-if="isNewImageFile(file)" class="border-2 border-green-500 rounded relative">
                                            <img :src="getFilePreviewUrl(file)" :alt="file.name"
                                                class="w-full h-24 object-cover rounded">
                                            <button type="button" @click="removeNewFile(getFieldKey(field.id, rIndex, section), index)"
                                                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors flex items-center justify-center shadow-lg">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div v-else
                                            class="border-2 border-green-500 rounded h-24 flex flex-col items-center justify-center bg-green-50 relative">
                                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            <span class="text-xs text-green-700 mt-1 px-1 text-center truncate w-full">{{
                                                file.name }}</span>
                                            <button type="button" @click="removeNewFile(getFieldKey(field.id, rIndex, section), index)"
                                                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors flex items-center justify-center shadow-lg">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- File input for new uploads -->
                            <input type="file" :multiple="field.allow_multiple"
                                @change="handleFileUpload(getFieldKey(field.id, rIndex, section), $event, field)" class="form-input">
                            <p v-if="field.help_text" class="text-xs text-gray-500 mt-1">{{ field.help_text }}</p>
                        </div>
                    </div>
                </div>

                <!-- Subsections -->
                <div v-if="section.subsections && section.subsections.length > 0" class="space-y-6">
                    <div v-for="subsection in section.subsections" :key="subsection.id"
                        class="bg-purple-50/50 rounded-xl p-6 border-2 border-purple-200">
                        <h5 class="text-md font-bold text-purple-900 mb-4">{{ subsection.title }}</h5>

                        <div class="flex flex-wrap gap-6">
                            <div v-for="field in subsection.fields" :key="field.id" :class="{
                                     'w-full': field.field_width === 'full' || !field.field_width,
                                     'w-full md:w-[calc(50%-0.75rem)]': field.field_width === 'half',
                                     'w-full md:w-[calc(33.333%-1rem)]': field.field_width === 'third',
                                     'w-full md:w-[calc(25%-1.125rem)]': field.field_width === 'quarter'
                                 }" class="form-group">
                                <label class="form-label">
                                    {{ field.label }}
                                    <span v-if="field.is_required" class="text-red-500">*</span>
                                </label>

                                <!-- Similar field types as above -->
                                <input v-if="['text', 'email', 'password', 'number'].includes(field.field_type)"
                                    :type="field.field_type" v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                    :placeholder="field.placeholder" :required="field.is_required" class="form-input">

                                <textarea v-if="field.field_type === 'textarea'" v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                    :placeholder="field.placeholder" :required="field.is_required" class="form-textarea"
                                    rows="3"></textarea>

                                <input v-if="['date', 'time'].includes(field.field_type)" :type="field.field_type"
                                    v-model="formData[getFieldKey(field.id, rIndex, section)]" :required="field.is_required" class="form-input">

                                <input v-if="['datetime', 'datetime-local'].includes(field.field_type)"
                                    type="datetime-local" v-model="formData[getFieldKey(field.id, rIndex, section)]" :required="field.is_required"
                                    class="form-input">

                                <select v-if="field.field_type === 'select'" v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                    :required="field.is_required" class="form-select">
                                    <option value="">Select an option...</option>
                                    <option v-for="opt in getOptions(field.options)" :key="opt" :value="opt">{{ opt }}
                                    </option>
                                </select>

                                <!-- File Upload -->
                                <div v-if="field.field_type === 'file'">
                                    <!-- Show existing files -->
                                    <div v-if="hasExistingFiles(getFieldKey(field.id, rIndex, section))" class="mb-3">
                                        <p class="text-xs text-gray-600 mb-2">Current files:</p>
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mb-2">
                                            <div v-for="(file, index) in getExistingFiles(getFieldKey(field.id, rIndex, section))" :key="index"
                                                class="relative group">
                                                <div v-if="isImageFile(file)" class="border rounded relative">
                                                    <img :src="'../' + file.file_path" :alt="file.original_name"
                                                        class="w-full h-24 object-cover rounded">
                                                    <button type="button" @click="triggerDelete(getFieldKey(field.id, rIndex, section), index)"
                                                        class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors flex items-center justify-center shadow-lg">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div v-else
                                                    class="border rounded h-24 flex flex-col items-center justify-center bg-gray-50 relative">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                        </path>
                                                    </svg>
                                                    <span
                                                        class="text-xs text-gray-600 mt-1 px-1 text-center truncate w-full">{{
                                                        file.original_name }}</span>
                                                    <button type="button" @click="triggerDelete(getFieldKey(field.id, rIndex, section), index)"
                                                        class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors flex items-center justify-center shadow-lg">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Show newly selected files preview -->
                                    <div v-if="hasNewFiles(getFieldKey(field.id, rIndex, section))" class="mb-3">
                                        <p class="text-xs text-green-600 font-semibold mb-2">New files to upload:</p>
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mb-2">
                                            <div v-for="(file, index) in getNewFiles(getFieldKey(field.id, rIndex, section))" :key="'new_' + index"
                                                class="relative group">
                                                <div v-if="isNewImageFile(file)" class="border-2 border-green-500 rounded relative">
                                                    <img :src="getFilePreviewUrl(file)" :alt="file.name"
                                                        class="w-full h-24 object-cover rounded">
                                                    <button type="button" @click="removeNewFile(getFieldKey(field.id, rIndex, section), index)"
                                                        class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors flex items-center justify-center shadow-lg">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div v-else
                                                    class="border-2 border-green-500 rounded h-24 flex flex-col items-center justify-center bg-green-50 relative">
                                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                                        </path>
                                                    </svg>
                                                    <span class="text-xs text-green-700 mt-1 px-1 text-center truncate w-full">{{
                                                        file.name }}</span>
                                                    <button type="button" @click="removeNewFile(getFieldKey(field.id, rIndex, section), index)"
                                                        class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors flex items-center justify-center shadow-lg">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- File input for new uploads -->
                                    <input type="file" :multiple="field.allow_multiple"
                                        @change="handleFileUpload(getFieldKey(field.id, rIndex, section), $event, field)" class="form-input">
                                    <p v-if="field.help_text" class="text-xs text-gray-500 mt-1">{{ field.help_text }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Cumulative Summary for Repeatable Sections (Outside the repeat loop) -->
            <div v-if="(section.is_repeatable || section.title.toLowerCase().includes('floor wise')) && getRepeatCount(section) > 0" class="mb-8 p-6 bg-blue-50 rounded-xl border-2 border-blue-100">
                <h4 class="text-sm font-bold text-blue-800 uppercase tracking-widest mb-4">Cumulative Summary</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Main section fields totals -->
                    <template v-for="field in section.fields" :key="'total_' + field.id">
                        <div v-if="field.field_type === 'number'" class="bg-white p-4 rounded-lg shadow-sm border border-blue-200">
                            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">
                                Total {{ field.label }}
                            </p>
                            <p class="text-2xl font-bold text-blue-900">{{ totals[field.id] || 0 }}</p>
                        </div>
                    </template>
                    
                    <!-- Subsection fields totals -->
                    <template v-if="section.subsections" v-for="subsection in section.subsections" :key="'sub_' + subsection.id">
                        <template v-for="field in subsection.fields" :key="'total_sub_' + field.id">
                            <div v-if="field.field_type === 'number'" class="bg-white p-4 rounded-lg shadow-sm border border-blue-200">
                                <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">
                                    Total {{ field.label }}
                                </p>
                                <p class="text-2xl font-bold text-blue-900">{{ totals[field.id] || 0 }}</p>
                            </div>
                        </template>
                    </template>
                </div>
            </div>
            
            </template>

            <!-- Fixed Action Bar at Bottom -->
            <div class="bottom-action-bar bg-white border-t-2 border-gray-200 shadow-lg">
                <div class="px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <a href="view-survey2.php?id=<?php echo $responseId; ?>" 
                               class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                Cancel
                            </a>
                            
                            <!-- Last Saved Indicator -->
                            <span v-if="lastSavedAt" class="text-sm text-gray-500 flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Last saved: {{ formatTime(lastSavedAt) }}
                            </span>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <!-- Save Draft Button -->
                            <button @click="saveDraft" 
                                    type="button"
                                    :disabled="saving"
                                    class="inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors disabled:opacity-50">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293zM9 4a1 1 0 012 0v2H9V4z"></path>
                                </svg>
                                {{ saving ? 'Saving...' : 'Save Draft' }}
                            </button>
                            
                            <!-- Update Survey Button -->
                            <button type="submit" 
                                    :disabled="submitting"
                                    class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors disabled:opacity-50 shadow-lg">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                {{ submitting ? 'Updating...' : 'Update Survey' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Spacer to prevent content from being hidden behind fixed buttons -->
            <div class="h-24"></div>
        </form>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-all">
        <div
            class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 transform transition-all scale-100 border border-gray-100">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                    </path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Confirm Deletion</h3>
            <p class="text-gray-500 text-center mb-6">Do you really want to delete this? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button @click="showDeleteModal = false"
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors">
                    No, Cancel
                </button>
                <button @click="confirmDelete"
                    class="flex-1 px-4 py-2 bg-red-600 text-white font-medium rounded-xl hover:bg-red-700 shadow-lg shadow-red-200 transition-all">
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>
    </div>

    <!-- Toast Notification -->
    <div v-if="toast.show" :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'"
        class="fixed bottom-8 right-8 z-[110] text-white px-6 py-3 rounded-2xl shadow-2xl flex items-center space-x-3 animate-bounce-subtle">
        <svg v-if="toast.type === 'success'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span class="font-medium">{{ toast.message }}</span>
    </div>
</div>

<style>
    @keyframes bounce-subtle {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-5px);
        }
    }

    .animate-bounce-subtle {
        animation: bounce-subtle 2s infinite ease-in-out;
    }
</style>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
    const { createApp } = Vue;

    createApp({
        data() {
            return {
                loading: true,
                error: null,
                surveyResponse: null,
                sections: [],
                formData: {},
                files: {},
                submitting: false,
                saving: false,
                lastSavedAt: null,
                autoSaveInterval: null,
                showDeleteModal: false,
                pendingDelete: { fieldId: null, index: null },
                toast: { show: false, message: '', type: 'success' }
            };
        },
        watch: {
            formData: {
                handler(newVal) {
                    // Initialize default values for repeated sections when floor count changes
                    this.sections.forEach(section => {
                        if (section.is_repeatable || section.title.toLowerCase().includes('floor wise')) {
                            const count = this.getRepeatCount(section);
                            
                            // Initialize fields for each repeat
                            for (let i = 1; i <= count; i++) {
                                section.fields.forEach(field => {
                                    const key = `${field.id}_${i}`;
                                    // Only set default if the field doesn't exist yet
                                    if (this.formData[key] === undefined || this.formData[key] === '') {
                                        if (field.default_value !== undefined && field.default_value !== null) {
                                            this.formData[key] = field.default_value;
                                        }
                                    }
                                });
                                
                                // Initialize subsection fields
                                if (section.subsections) {
                                    section.subsections.forEach(subsection => {
                                        subsection.fields.forEach(field => {
                                            const key = `${field.id}_${i}`;
                                            if (this.formData[key] === undefined || this.formData[key] === '') {
                                                if (field.default_value !== undefined && field.default_value !== null) {
                                                    this.formData[key] = field.default_value;
                                                }
                                            }
                                        });
                                    });
                                }
                            }
                        }
                    });
                },
                deep: true
            }
        },
        methods: {
            getFieldKey(fieldId, rIndex, section) {
                // Check if section is repeatable or is "Floor Wise Camera Details"
                const isRepeatable = section.is_repeatable || (section.title || '').toLowerCase().includes('floor wise');
                if (!isRepeatable) return fieldId;
                return `${fieldId}_${rIndex}`;
            },
            getRepeatCount(section) {
                // Special handling for "Floor Wise Camera Details" section
                const sectionTitle = (section.title || '').trim().toLowerCase();
                
                if (sectionTitle === 'floor wise camera details') {
                    // Find the "No of Floors" field in "General Information" section
                    const generalInfoSection = this.sections.find(s => 
                        (s.title || '').trim().toLowerCase() === 'general information'
                    );
                    
                    if (generalInfoSection) {
                        const floorsField = generalInfoSection.fields.find(f => 
                            (f.label || '').trim().toLowerCase() === 'no of floors'
                        );
                        
                        if (floorsField) {
                            const val = this.formData[floorsField.id];
                            const count = parseInt(val);
                            
                            console.log('Floor Wise Camera Details - No of Floors value:', val, 'parsed:', count);
                            
                            if (isNaN(count) || count <= 0) return 0;
                            return Math.max(0, count);
                        } else {
                            console.warn('No of Floors field not found in General Information section');
                        }
                    } else {
                        console.warn('General Information section not found');
                    }
                    
                    return 0;
                }
                
                // For all other sections, check if they are repeatable
                if (!section.is_repeatable) return 1;
                
                const sourceId = section.repeat_source_field_id;
                if (!sourceId) return 1;
                
                const val = this.formData[sourceId];
                const count = parseInt(val);
                
                if (isNaN(count)) return 0;
                return Math.max(0, count);
            },
            getOptions(optionsString) {
                if (!optionsString) return [];
                return optionsString.split(',').map(opt => opt.trim());
            },
            hasExistingFiles(fieldId) {
                const value = this.formData[fieldId];
                if (!value) return false;
                if (Array.isArray(value)) return value.length > 0;
                if (typeof value === 'object' && value.file_path) return true;
                return false;
            },
            getExistingFiles(fieldId) {
                const value = this.formData[fieldId];
                if (!value) return [];
                if (Array.isArray(value)) return value;
                if (typeof value === 'object' && value.file_path) return [value];
                return [];
            },
            isImageFile(file) {
                if (!file || !file.file_path) return false;
                const ext = file.file_path.split('.').pop().toLowerCase();
                return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
            },
            updateCheckbox(fieldId, value, event) {
                if (!this.formData[fieldId]) {
                    this.formData[fieldId] = [];
                }
                if (event.target.checked) {
                    if (!this.formData[fieldId].includes(value)) {
                        this.formData[fieldId].push(value);
                    }
                } else {
                    this.formData[fieldId] = this.formData[fieldId].filter(v => v !== value);
                }
            },
            isCheckboxChecked(fieldId, value) {
                const fieldValue = this.formData[fieldId];
                if (Array.isArray(fieldValue)) {
                    return fieldValue.includes(value);
                }
                if (typeof fieldValue === 'string') {
                    return fieldValue.split(',').map(v => v.trim()).includes(value);
                }
                return false;
            },
            handleFileUpload(fieldId, event, field) {
                const files = Array.from(event.target.files);
                if (field.allow_multiple) {
                    this.files[fieldId] = files;
                } else {
                    this.files[fieldId] = files[0];
                }
                console.log('Files uploaded for field', fieldId, ':', files);
            },
            getNewFiles(fieldId) {
                const files = this.files[fieldId];
                if (!files) return [];
                if (Array.isArray(files)) return files;
                return [files];
            },
            hasNewFiles(fieldId) {
                return this.getNewFiles(fieldId).length > 0;
            },
            isNewImageFile(file) {
                if (!file || !file.name) return false;
                const ext = file.name.split('.').pop().toLowerCase();
                return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
            },
            getFilePreviewUrl(file) {
                return URL.createObjectURL(file);
            },
            removeNewFile(fieldId, index) {
                const files = this.getNewFiles(fieldId);
                files.splice(index, 1);
                if (files.length === 0) {
                    delete this.files[fieldId];
                } else {
                    this.files[fieldId] = files;
                }
                this.showToast('File removed');
            },
            triggerDelete(fieldId, index) {
                this.pendingDelete = { fieldId, index };
                this.showDeleteModal = true;
            },
            confirmDelete() {
                const { fieldId, index } = this.pendingDelete;
                this.removeExistingFile(fieldId, index);
                this.showDeleteModal = false;
                this.showToast('File removed successfully');
            },
            removeExistingFile(fieldId, index) {
                const files = this.getExistingFiles(fieldId);
                if (Array.isArray(files)) {
                    files.splice(index, 1);
                    if (files.length === 0) {
                        this.formData[fieldId] = null;
                    } else {
                        this.formData[fieldId] = files;
                    }
                } else {
                    this.formData[fieldId] = null;
                }
            },
            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => {
                    this.toast.show = false;
                }, 3000);
            },
            formatTime(datetime) {
                if (!datetime) return '';
                const date = new Date(datetime);
                const now = new Date();
                const diff = Math.floor((now - date) / 1000); // seconds
                
                if (diff < 60) return 'Just now';
                if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
                if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
                return date.toLocaleString();
            },
            async saveDraft(isAutoSave = false) {
                if (this.saving) return;
                
                this.saving = true;
                try {
                    // Ensure formData is sent as an object, not an array
                    const formDataObj = {};
                    for (const key in this.formData) {
                        if (this.formData.hasOwnProperty(key)) {
                            formDataObj[key] = this.formData[key];
                        }
                    }
                    
                    const response = await fetch('../api/survey-progress.php?action=save_draft', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            response_id: <?php echo $responseId; ?>,
                            form_data: JSON.stringify(formDataObj),
                            site_master: JSON.stringify({}) // Empty for edit page
                        })
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        this.lastSavedAt = data.saved_at;
                        if (!isAutoSave) {
                            this.showToast('Draft saved successfully!');
                        }
                        console.log('Draft saved at:', data.saved_at);
                    } else {
                        if (!isAutoSave) {
                            this.showToast('Failed to save draft: ' + data.message, 'error');
                        }
                    }
                } catch (err) {
                    console.error('Save draft error:', err);
                    if (!isAutoSave) {
                        this.showToast('Failed to save draft', 'error');
                    }
                } finally {
                    this.saving = false;
                }
            },
            async updateSurvey() {
                if (this.submitting) return;

                this.submitting = true;
                try {
                    const formData = new FormData();
                    formData.append('response_id', <?php echo $responseId; ?>);
                    
                    // Ensure formData is sent as an object, not an array
                    const formDataObj = {};
                    for (const key in this.formData) {
                        if (this.formData.hasOwnProperty(key)) {
                            formDataObj[key] = this.formData[key];
                        }
                    }
                    formData.append('form_data', JSON.stringify(formDataObj));

                    // Add files
                    for (const fieldId in this.files) {
                        const fileArray = this.files[fieldId];
                        if (Array.isArray(fileArray)) {
                            fileArray.forEach((file) => {
                                formData.append(`file_${fieldId}[]`, file);
                            });
                        } else {
                            formData.append(`file_${fieldId}`, fileArray);
                        }
                    }

                    const response = await fetch('../admin/update-survey-dynamic.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('Survey updated successfully!');
                        window.location.href = 'view-survey2.php?id=<?php echo $responseId; ?>';
                    } else {
                        alert('Error: ' + (data.message || 'Failed to update survey'));
                    }
                } catch (err) {
                    alert('An error occurred while updating the survey');
                    console.error(err);
                } finally {
                    this.submitting = false;
                }
            },
            async loadSurveyData() {
                try {
                    this.loading = true;
                    this.error = null;
                    
                    const response = await fetch('../api/get-survey-data.php?response_id=<?php echo $responseId; ?>');
                    const data = await response.json();
                    
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to load survey data');
                    }
                    
                    // Check if survey is approved - redirect to view page
                    if (data.response.survey_status === 'approved') {
                        window.location.href = 'view-survey2.php?id=<?php echo $responseId; ?>';
                        return;
                    }
                    
                    this.surveyResponse = data.response;
                    this.sections = data.formStructure.sections || [];
                    this.formData = data.formData || {};
                    this.lastSavedAt = data.response.last_saved_at;
                    
                    console.log('Survey data loaded:', {
                        response: this.surveyResponse,
                        sections: this.sections.length,
                        formDataKeys: Object.keys(this.formData).length
                    });
                    
                } catch (err) {
                    console.error('Failed to load survey data:', err);
                    this.error = err.message || 'Failed to load survey data';
                } finally {
                    this.loading = false;
                }
            }
        },
        computed: {
            totals() {
                const res = {};
                this.sections.forEach(section => {
                    // Check if section is repeatable or is "Floor Wise Camera Details"
                    const isRepeatable = section.is_repeatable || (section.title || '').toLowerCase().includes('floor wise');
                    
                    if (isRepeatable) {
                        const count = this.getRepeatCount(section);
                        
                        // Process main fields
                        if (section.fields) {
                            section.fields.forEach(field => {
                                if (field.field_type === 'number') {
                                    let sum = 0;
                                    for (let i = 1; i <= count; i++) {
                                        const key = `${field.id}_${i}`;
                                        const value = this.formData[key];
                                        sum += parseFloat(value) || 0;
                                    }
                                    res[field.id] = sum;
                                }
                            });
                        }
                        
                        // Process subsections
                        if (section.subsections) {
                            section.subsections.forEach(sub => {
                                if (sub.fields) {
                                    sub.fields.forEach(field => {
                                        if (field.field_type === 'number') {
                                            let sum = 0;
                                            for (let i = 1; i <= count; i++) {
                                                const key = `${field.id}_${i}`;
                                                const value = this.formData[key];
                                                sum += parseFloat(value) || 0;
                                            }
                                            res[field.id] = sum;
                                        }
                                    });
                                }
                            });
                        }
                    }
                });
                return res;
            }
        },
        async mounted() {
            console.log('=== Edit Survey App Mounted ===');
            
            // Load survey data from API
            await this.loadSurveyData();
            
            // Start auto-save every 30 seconds
            this.autoSaveInterval = setInterval(() => {
                if (!this.loading && !this.error) {
                    console.log('Auto-saving draft...');
                    this.saveDraft(true);
                }
            }, 30000);
            console.log('Auto-save started (every 30 seconds)');
        },
        beforeUnmount() {
            // Clean up auto-save interval
            if (this.autoSaveInterval) {
                clearInterval(this.autoSaveInterval);
                console.log('Auto-save stopped');
            }
        }
    }).mount('#editSurveyApp');
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/admin_layout.php';
?>
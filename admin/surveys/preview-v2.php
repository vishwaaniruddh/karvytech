<?php
$id = $_GET['id'] ?? null;
if (!$id) die("Survey ID required");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Survey Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; }
        .form-card { background: white; border-radius: 1.5rem; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); }
        .input-field { width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.75rem; background: #fff; transition: all 0.2s; }
        .input-field:focus { border-color: #3b82f6; ring: 3px; ring-color: rgba(59, 130, 246, 0.1); outline: none; }
        [v-cloak] { display: none; }
    </style>
</head>
<body class="py-12 px-4">

<div id="app" class="max-w-2xl mx-auto space-y-8">
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-20">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-500">Loading form...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="form-card p-10 text-center">
        <p class="text-red-600 font-semibold">{{ error }}</p>
    </div>

    <!-- Form Content -->
    <template v-if="!loading && !error">
        <!-- Form Header -->
        <div class="form-card p-10 overflow-hidden relative">
            <div class="absolute top-0 left-0 w-full h-2 bg-blue-600"></div>
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">{{ survey.title }}</h1>
            <p class="text-gray-500 leading-relaxed">{{ survey.description }}</p>
        </div>

        <!-- Form Body -->
        <form @submit.prevent="submitForm" class="space-y-8">
            <input type="hidden" name="survey_id" :value="survey.id">

            <section v-for="(section, sIndex) in sections" :key="section.id" class="form-card overflow-hidden">
                <div class="bg-gray-50 px-8 py-6 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800">{{ section.title }}</h2>
                    <p v-if="section.description" class="text-sm text-gray-500 mt-1">{{ section.description }}</p>
                    <div v-if="section.is_repeatable" class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        Repeating ({{ getRepeatCount(section) }} entries)
                    </div>
                </div>
                
                <div v-for="rIndex in getRepeatCount(section)" :key="'repeat-' + section.id + '-' + rIndex" class="p-8 space-y-8 border-b border-gray-100 last:border-b-0">
                    <h3 v-if="section.is_repeatable" class="text-lg font-bold text-blue-600 flex items-center gap-2">
                        <span class="bg-blue-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs">{{ rIndex }}</span>
                        {{ section.title }} - Entry {{ rIndex }}
                    </h3>
                    
                    <!-- Main Section Fields -->
                    <div v-if="section.fields && section.fields.length > 0" class="flex flex-wrap gap-6">
                        <div v-for="field in section.fields" :key="field.id" 
                             :class="{
                                 'w-full': field.field_width === 'full' || !field.field_width,
                                 'w-full md:w-[calc(50%-0.75rem)]': field.field_width === 'half',
                                 'w-full md:w-[calc(33.333%-1rem)]': field.field_width === 'third',
                                 'w-full md:w-[calc(25%-1.125rem)]': field.field_width === 'quarter'
                             }"
                             class="space-y-3">
                            <label class="block text-sm font-bold text-gray-700">
                                {{ field.label }}
                                <span v-if="field.is_required" class="text-red-500">*</span>
                            </label>

                        <div class="field-container">
                            <!-- Text, Email, Password, Number -->
                            <input 
                                v-if="['text', 'email', 'password', 'number'].includes(field.field_type)"
                                :type="field.field_type"
                                v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                :placeholder="field.placeholder"
                                :required="field.is_required"
                                :min="field.field_type === 'number' ? (field.min_value !== null ? field.min_value : (!field.allow_negative ? '0' : null)) : null"
                                :step="field.field_type === 'number' && field.is_integer ? '1' : 'any'"
                                class="input-field">

                            <!-- Textarea -->
                            <textarea 
                                v-if="field.field_type === 'textarea'"
                                v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                :placeholder="field.placeholder"
                                :required="field.is_required"
                                class="input-field min-h-[120px]"></textarea>

                            <!-- Date, Time -->
                            <input 
                                v-if="['date', 'time'].includes(field.field_type)"
                                :type="field.field_type"
                                v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                :required="field.is_required"
                                class="input-field">

                            <!-- DateTime -->
                            <input 
                                v-if="['datetime', 'datetime-local'].includes(field.field_type)"
                                type="datetime-local"
                                v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                :required="field.is_required"
                                class="input-field">

                            <!-- Select Dropdown -->
                            <select 
                                v-if="field.field_type === 'select'"
                                v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                :required="field.is_required"
                                class="input-field">
                                <option value="">Select an option...</option>
                                <option v-for="opt in getOptions(field.options)" :key="opt" :value="opt">{{ opt }}</option>
                            </select>

                            <!-- Customer Dropdown -->
                            <select 
                                v-if="field.field_type === 'customer'"
                                v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                :required="field.is_required"
                                class="input-field">
                                <option value="">Select customer...</option>
                                <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</option>
                            </select>

                            <!-- Radio Buttons -->
                            <div v-if="field.field_type === 'radio'" class="space-y-3 mt-1">
                                <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center gap-3 cursor-pointer group">
                                    <input 
                                        type="radio" 
                                        :name="'field_' + field.id + '_' + rIndex"
                                        :value="opt"
                                        v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                        :required="field.is_required"
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <span class="text-sm text-gray-600 group-hover:text-gray-900">{{ opt }}</span>
                                </label>
                            </div>

                            <!-- Checkboxes -->
                            <div v-if="field.field_type === 'checkbox'" class="space-y-3 mt-1">
                                <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center gap-3 cursor-pointer group">
                                    <input 
                                        type="checkbox" 
                                        :value="opt"
                                        @change="updateCheckbox(getFieldKey(field.id, rIndex), opt, $event)"
                                        class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 border-gray-300">
                                    <span class="text-sm text-gray-600 group-hover:text-gray-900">{{ opt }}</span>
                                </label>
                            </div>

                            <!-- File Upload -->
                            <input 
                                v-if="field.field_type === 'file'"
                                type="file"
                                :multiple="field.allow_multiple"
                                :accept="getFileAccept(field)"
                                @change="handleFileUpload(getFieldKey(field.id, rIndex), $event, field)"
                                :required="field.is_required"
                                class="input-field file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            
                            <!-- File Preview -->
                            <div v-if="field.field_type === 'file' && field.show_preview && filePreviews[getFieldKey(field.id, rIndex)] && filePreviews[getFieldKey(field.id, rIndex)].length > 0" class="mt-3 grid grid-cols-2 md:grid-cols-3 gap-3">
                                <div v-for="(preview, index) in filePreviews[getFieldKey(field.id, rIndex)]" :key="index" class="relative group">
                                    <div class="border-2 border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                                        <!-- Image Preview -->
                                        <img v-if="preview.type === 'image'" :src="preview.url" class="w-full h-32 object-cover">
                                        
                                        <!-- Document Preview -->
                                        <div v-else class="w-full h-32 flex flex-col items-center justify-center p-3">
                                            <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                            <p class="text-xs text-gray-600 font-medium text-center truncate w-full">{{ preview.name }}</p>
                                        </div>
                                    </div>
                                    <button @click="removeFile(getFieldKey(field.id, rIndex), index)" type="button" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                    <p class="text-xs text-gray-500 mt-1 truncate">{{ formatFileSize(preview.size) }}</p>
                                </div>
                            </div>
                            
                            <p v-if="field.field_type === 'file' && field.max_files && field.allow_multiple" class="text-xs text-gray-400 mt-1">
                                Max {{ field.max_files }} files, {{ field.max_file_size }}MB each
                            </p>
                        </div>
                            
                            <p v-if="field.help_text" class="text-xs text-gray-400 font-medium italic">{{ field.help_text }}</p>
                        </div>
                    </div>

                    <!-- Subsections -->
                    <div v-if="section.subsections && section.subsections.length > 0" class="space-y-6 mt-8">
                        <div v-for="subsection in section.subsections" :key="subsection.id" class="bg-purple-50/50 rounded-2xl p-6 border-2 border-purple-200">
                            <div class="mb-4">
                                <h3 class="text-lg font-bold text-purple-900 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    {{ subsection.title }}
                                </h3>
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
                                     class="space-y-3">
                                    <label class="block text-sm font-bold text-gray-700">
                                        {{ field.label }}
                                        <span v-if="field.is_required" class="text-red-500">*</span>
                                    </label>

                                    <div class="field-container">
                                        <!-- Text, Email, Password, Number -->
                                        <input 
                                            v-if="['text', 'email', 'password', 'number'].includes(field.field_type)"
                                            :type="field.field_type"
                                            v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                            :placeholder="field.placeholder"
                                            :required="field.is_required"
                                            :min="field.field_type === 'number' ? (field.min_value !== null ? field.min_value : (!field.allow_negative ? '0' : null)) : null"
                                            :step="field.field_type === 'number' && field.is_integer ? '1' : 'any'"
                                            class="input-field">

                                        <!-- Textarea -->
                                        <textarea 
                                            v-if="field.field_type === 'textarea'"
                                            v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                            :placeholder="field.placeholder"
                                            :required="field.is_required"
                                            class="input-field min-h-[120px]"></textarea>

                                        <!-- Date, Time -->
                                        <input 
                                            v-if="['date', 'time'].includes(field.field_type)"
                                            :type="field.field_type"
                                            v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                            :required="field.is_required"
                                            class="input-field">

                                        <!-- DateTime -->
                                        <input 
                                            v-if="['datetime', 'datetime-local'].includes(field.field_type)"
                                            type="datetime-local"
                                            v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                            :required="field.is_required"
                                            class="input-field">

                                        <!-- Select Dropdown -->
                                        <select 
                                            v-if="field.field_type === 'select'"
                                            v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                            :required="field.is_required"
                                            class="input-field">
                                            <option value="">Select an option...</option>
                                            <option v-for="opt in getOptions(field.options)" :key="opt" :value="opt">{{ opt }}</option>
                                        </select>

                                        <!-- Customer Dropdown -->
                                        <select 
                                            v-if="field.field_type === 'customer'"
                                            v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                            :required="field.is_required"
                                            class="input-field">
                                            <option value="">Select customer...</option>
                                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</option>
                                        </select>

                                        <!-- Radio Buttons -->
                                        <div v-if="field.field_type === 'radio'" class="space-y-3 mt-1">
                                            <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center gap-3 cursor-pointer group">
                                                <input 
                                                    type="radio" 
                                                    :name="'field_' + field.id + '_' + rIndex"
                                                    :value="opt"
                                                    v-model="formData[getFieldKey(field.id, rIndex, section)]"
                                                    :required="field.is_required"
                                                    class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                                <span class="text-sm text-gray-600 group-hover:text-gray-900">{{ opt }}</span>
                                            </label>
                                        </div>

                                        <!-- Checkboxes -->
                                        <div v-if="field.field_type === 'checkbox'" class="space-y-3 mt-1">
                                            <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center gap-3 cursor-pointer group">
                                                <input 
                                                    type="checkbox" 
                                                    :value="opt"
                                                    @change="updateCheckbox(getFieldKey(field.id, rIndex), opt, $event)"
                                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 border-gray-300">
                                                <span class="text-sm text-gray-600 group-hover:text-gray-900">{{ opt }}</span>
                                            </label>
                                        </div>

                                        <!-- File Upload -->
                                        <input 
                                            v-if="field.field_type === 'file'"
                                            type="file"
                                            :multiple="field.allow_multiple"
                                            :accept="getFileAccept(field)"
                                            @change="handleFileUpload(getFieldKey(field.id, rIndex), $event, field)"
                                            :required="field.is_required"
                                            class="input-field file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                        
                                        <!-- File Preview -->
                                        <div v-if="field.field_type === 'file' && field.show_preview && filePreviews[getFieldKey(field.id, rIndex)] && filePreviews[getFieldKey(field.id, rIndex)].length > 0" class="mt-3 grid grid-cols-2 md:grid-cols-3 gap-3">
                                            <div v-for="(preview, index) in filePreviews[getFieldKey(field.id, rIndex)]" :key="index" class="relative group">
                                                <div class="border-2 border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                                                    <!-- Image Preview -->
                                                    <img v-if="preview.type === 'image'" :src="preview.url" class="w-full h-32 object-cover">
                                                    
                                                    <!-- Document Preview -->
                                                    <div v-else class="w-full h-32 flex flex-col items-center justify-center p-3">
                                                        <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <p class="text-xs text-gray-600 font-medium text-center truncate w-full">{{ preview.name }}</p>
                                                    </div>
                                                </div>
                                                <button @click="removeFile(getFieldKey(field.id, rIndex), index)" type="button" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                                <p class="text-xs text-gray-500 mt-1 truncate">{{ formatFileSize(preview.size) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <p v-if="field.help_text" class="text-xs text-gray-400 font-medium italic">{{ field.help_text }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>

                    <!-- Cumulative Totals for Repeatable Section -->
                    <div v-if="section.is_repeatable && getRepeatCount(section) > 0" class="mt-8 p-6 bg-blue-50 rounded-2xl border-2 border-blue-100">
                        <h4 class="text-sm font-bold text-blue-800 uppercase tracking-widest mb-4">Cumulative Summary</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div v-for="field in section.fields" v-if="field.field_type === 'number'" :key="'total-' + field.id" class="p-3 bg-white rounded-xl shadow-sm border border-blue-100 text-center">
                                <p class="text-xs text-gray-500 font-bold mb-1">{{ field.label }}</p>
                                <p class="text-xl font-extrabold text-blue-600">{{ getFieldTotal(field.id, section) }}</p>
                            </div>
                            <!-- Totals for subsection fields -->
                            <template v-for="subsection in section.subsections">
                                <div v-for="field in subsection.fields" v-if="field.field_type === 'number'" :key="'total-sub-' + field.id" class="p-3 bg-white rounded-xl shadow-sm border border-blue-100 text-center">
                                    <p class="text-xs text-purple-500 font-bold mb-1">{{ field.label }}</p>
                                    <p class="text-xl font-extrabold text-purple-600">{{ getFieldTotal(field.id, section) }}</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </section>

            <div class="pt-8 flex justify-center">
                <button type="submit" :disabled="submitting" class="w-full md:w-auto px-12 py-4 bg-blue-600 text-white rounded-2xl font-extrabold shadow-lg shadow-blue-200 hover:bg-blue-700 hover:-translate-y-0.5 transition-all active:translate-y-0 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span v-if="submitting">Submitting...</span>
                    <span v-else>Submit Response</span>
                </button>
            </div>
        </form>
    </template>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            surveyId: <?php echo json_encode($id); ?>,
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
    computed: {
        totals() {
            const res = {};
            this.sections.forEach(section => {
                if (section.is_repeatable) {
                    const count = this.getRepeatCount(section);
                    // Process main fields
                    section.fields.forEach(field => {
                        if (field.field_type === 'number') {
                            let sum = 0;
                            for (let i = 1; i <= count; i++) {
                                sum += parseFloat(this.formData[`${field.id}_${i}`]) || 0;
                            }
                            res[field.id] = sum;
                        }
                    });
                    // Process subsections
                    if (section.subsections) {
                        section.subsections.forEach(sub => {
                            sub.fields.forEach(field => {
                                if (field.field_type === 'number') {
                                    let sum = 0;
                                    for (let i = 1; i <= count; i++) {
                                        sum += parseFloat(this.formData[`${field.id}_${i}`]) || 0;
                                    }
                                    res[field.id] = sum;
                                }
                            });
                        });
                    }
                }
            });
            return res;
        }
    },
    methods: {
        async loadSurvey() {
            try {
                const response = await fetch(`../../api/surveys_v2.php?action=load&id=${this.surveyId}`);
                const data = await response.json();
                
                if (data.success) {
                    this.survey = data.survey;
                    this.sections = data.sections;
                    
                    // Initialize formData with empty values
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
                const response = await fetch('../../api/surveys_v2.php?action=get_customers');
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

        getRepeatCount(section) {
            if (!section.is_repeatable) return 1;
            if (!section.repeat_source_field_id) return 1;
            
            const count = parseInt(this.formData[section.repeat_source_field_id]) || 0;
            return Math.max(0, count);
        },

        getFieldKey(fieldId, repeatIndex, section) {
            if (!section.is_repeatable) return fieldId;
            return `${fieldId}_${repeatIndex}`;
        },

        getFieldTotal(fieldId, section) {
            return this.totals[fieldId] || 0;
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
            const maxSize = (field.max_file_size || 5) * 1024 * 1024; // Convert MB to bytes
            
            // Validate file count
            if (selectedFiles.length > maxFiles) {
                alert(`You can only upload up to ${maxFiles} file(s)`);
                event.target.value = '';
                return;
            }
            
            // Validate file sizes and types
            const validFiles = [];
            const acceptedTypes = this.getFileAcceptArray(field);
            
            for (const file of selectedFiles) {
                // Check file size
                if (file.size > maxSize) {
                    alert(`File "${file.name}" exceeds the maximum size of ${field.max_file_size}MB`);
                    continue;
                }
                
                // Check file type if restrictions exist
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
            
            // Store files
            if (!this.files[fieldId]) {
                this.files[fieldId] = [];
            }
            this.files[fieldId] = field.allow_multiple ? validFiles : [validFiles[0]];
            
            // Generate previews
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
        
        async submitForm() {
            this.submitting = true;
            
            try {
                // For now, just show the data in console
                console.log('Form Data:', this.formData);
                console.log('Files:', this.files);
                
                // TODO: Implement actual submission API
                alert('Form submitted successfully! (API endpoint not yet implemented)');
                
            } catch (err) {
                alert('An error occurred while submitting the form');
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
}).mount('#app');
</script>

</body>
</html>

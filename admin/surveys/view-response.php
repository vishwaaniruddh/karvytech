<?php
require_once __DIR__ . '/../../config/auth.php';

$responseId = $_GET['id'] ?? null;

if (!$responseId) {
    header('Location: ../sites/');
    exit;
}

$title = 'Survey Response';
ob_start();
?>

<div id="responseViewApp">
    <div v-if="loading" class="p-12 text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-500">Loading response...</p>
    </div>

    <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6">
        <p class="text-red-600">{{ error }}</p>
    </div>

    <template v-if="!loading && !error && response">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ response.survey_title }}</h1>
                    <p class="text-gray-600 mt-2">{{ response.survey_description }}</p>
                    <div class="flex items-center gap-4 mt-4 text-sm text-gray-500">
                        <span>Site: <strong>{{ response.site_code }}</strong></span>
                        <span>•</span>
                        <span>Submitted: <strong>{{ formatDate(response.submitted_date) }}</strong></span>
                        <span>•</span>
                        <span>By: <strong>{{ response.surveyor_name || 'Unknown' }}</strong></span>
                    </div>
                </div>
                <a href="../sites/" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Back to Sites
                </a>
            </div>
        </div>

        <!-- Site Master Data -->
        <div v-if="response.site_master_data" class="professional-table bg-white mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Site Information</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="(value, key) in response.site_master_data" :key="key">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ formatLabel(key) }}</label>
                        <p class="text-sm text-gray-900">{{ value || 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Survey Responses -->
        <div class="professional-table bg-white">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Survey Responses</h3>
            </div>
            <div class="p-6 space-y-8">
                <div v-for="(section, sIndex) in sections" :key="section.id" class="border-b border-gray-200 pb-6 last:border-0">
                    <h4 class="text-lg font-bold text-gray-800 mb-4">{{ section.title }}</h4>
                    <p v-if="section.description" class="text-sm text-gray-500 mb-4">{{ section.description }}</p>
                    
                    <!-- Direct Fields of Parent Section -->
                    <div v-if="section.fields && section.fields.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div v-for="field in section.fields" :key="field.id"
                             :class="{'md:col-span-2': field.field_width === 'full'}">
                            <label class="block text-xs font-medium text-gray-500 mb-1">
                                {{ field.label }}
                                <span v-if="field.is_required" class="text-red-500">*</span>
                            </label>
                            <div v-html="renderFieldValue(field)"></div>
                        </div>
                    </div>

                    <!-- Subsections -->
                    <div v-if="section.subsections && section.subsections.length > 0" class="space-y-4">
                        <div v-for="subsection in section.subsections" :key="subsection.id" 
                             class="bg-purple-50 rounded-lg p-4 border-2 border-purple-200">
                            <h5 class="text-md font-bold text-purple-900 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                {{ subsection.title }}
                            </h5>
                            <p v-if="subsection.description" class="text-sm text-purple-700 mb-3">{{ subsection.description }}</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="field in subsection.fields" :key="field.id"
                                     :class="{'md:col-span-2': field.field_width === 'full'}">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">
                                        {{ field.label }}
                                        <span v-if="field.is_required" class="text-red-500">*</span>
                                    </label>
                                    <div v-html="renderFieldValue(field)"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js"></script>
<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            loading: true,
            error: null,
            response: null,
            sections: []
        };
    },
    mounted() {
        this.loadResponse();
    },
    methods: {
        async loadResponse() {
            try {
                const responseId = <?php echo json_encode($responseId); ?>;
                const res = await fetch(`../../api/surveys_v2.php?action=get_response&response_id=${responseId}`);
                const data = await res.json();
                
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load response');
                }
                
                this.response = data.response;
                this.sections = data.sections;
            } catch (err) {
                this.error = err.message;
            } finally {
                this.loading = false;
            }
        },
        formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        formatLabel(key) {
            return key.split('_').map(word => 
                word.charAt(0).toUpperCase() + word.slice(1)
            ).join(' ');
        },
        renderFieldValue(field) {
            const value = this.response.form_data[field.id];
            
            if (!value || (Array.isArray(value) && value.length === 0)) {
                return '<p class="text-sm text-gray-900">N/A</p>';
            }
            
            // File upload handling
            if (field.field_type === 'file' && Array.isArray(value)) {
                // Single file (has file_path directly)
                if (value.file_path) {
                    const isImage = this.isImageFile(value.mime_type || value.original_name);
                    if (isImage) {
                        return `
                            <div class="mt-2">
                                <a href="../../${value.file_path}" target="_blank">
                                    <img src="../../${value.file_path}" alt="${value.original_name}" 
                                         class="max-w-xs max-h-48 rounded border border-gray-200 hover:border-blue-500 transition-colors cursor-pointer">
                                </a>
                                <p class="text-xs text-gray-500 mt-1">${value.original_name}</p>
                            </div>
                        `;
                    } else {
                        return `
                            <a href="../../${value.file_path}" target="_blank" class="inline-flex items-center gap-2 text-blue-600 hover:underline text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                ${value.original_name}
                            </a>
                        `;
                    }
                }
                // Multiple files
                let html = '<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 mt-2">';
                value.forEach(file => {
                    if (file.file_path) {
                        const isImage = this.isImageFile(file.mime_type || file.original_name);
                        if (isImage) {
                            html += `
                                <div class="group">
                                    <a href="../../${file.file_path}" target="_blank">
                                        <img src="../../${file.file_path}" alt="${file.original_name}" 
                                             class="w-full h-32 object-cover rounded border border-gray-200 hover:border-blue-500 transition-colors cursor-pointer">
                                    </a>
                                    <p class="text-xs text-gray-500 mt-1 truncate" title="${file.original_name}">${file.original_name}</p>
                                </div>
                            `;
                        } else {
                            html += `
                                <div class="border border-gray-200 rounded p-3 hover:border-blue-500 transition-colors">
                                    <a href="../../${file.file_path}" target="_blank" class="flex flex-col items-center gap-2 text-blue-600 hover:underline">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-xs text-center truncate w-full" title="${file.original_name}">${file.original_name}</span>
                                    </a>
                                </div>
                            `;
                        }
                    }
                });
                html += '</div>';
                return html;
            }
            
            // Array values (checkboxes)
            if (Array.isArray(value)) {
                return `<p class="text-sm text-gray-900">${value.join(', ')}</p>`;
            }
            
            // Regular text value
            return `<p class="text-sm text-gray-900">${value}</p>`;
        },
        isImageFile(mimeTypeOrFilename) {
            if (!mimeTypeOrFilename) return false;
            
            // Check by MIME type
            if (mimeTypeOrFilename.startsWith('image/')) {
                return true;
            }
            
            // Check by file extension
            const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp', '.svg'];
            const filename = mimeTypeOrFilename.toLowerCase();
            return imageExtensions.some(ext => filename.endsWith(ext));
        }
    }
}).mount('#responseViewApp');
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>

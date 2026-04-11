<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

if (!Auth::isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$responseId = $_GET['id'] ?? null;
if (!$responseId) {
    header('Location: ../admin/surveys/index2.php');
    exit;
}

$db = Database::getInstance()->getConnection();

// Fetch survey response
$stmt = $db->prepare("
    SELECT sr.*, 
           ds.title as survey_title, 
           ds.description as survey_description,
           s.site_id, s.store_id, s.site_ticket_id
    FROM dynamic_survey_responses sr
    LEFT JOIN dynamic_surveys ds ON sr.survey_form_id = ds.id
    LEFT JOIN sites s ON sr.site_id = s.id
    WHERE sr.id = ?
");
$stmt->execute([$responseId]);
$response = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$response) {
    header('Location: ../admin/surveys/index2.php');
    exit;
}

// Check if survey is approved - cannot edit approved surveys
if ($response['survey_status'] === 'approved') {
    header('Location: view-survey2.php?id=' . $responseId);
    exit;
}

$formData = json_decode($response['form_data'], true) ?? [];
$siteMasterData = json_decode($response['site_master_data'], true) ?? [];

// Get form structure from database tables
$formStructure = ['sections' => []];

// Fetch main sections (parent_section_id IS NULL)
$stmt = $db->prepare("SELECT * FROM dynamic_survey_sections WHERE survey_id = ? AND parent_section_id IS NULL ORDER BY sort_order ASC");
$stmt->execute([$response['survey_form_id']]);
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($sections as &$section) {
    // Get fields for this section
    $stmt = $db->prepare("SELECT * FROM dynamic_survey_fields WHERE section_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$section['id']]);
    $section['fields'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get subsections
    $stmt = $db->prepare("SELECT * FROM dynamic_survey_sections WHERE parent_section_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$section['id']]);
    $subsections = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($subsections as &$subsection) {
        // Get fields for subsection
        $stmt = $db->prepare("SELECT * FROM dynamic_survey_fields WHERE section_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$subsection['id']]);
        $subsection['fields'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $section['subsections'] = $subsections;
}

$formStructure['sections'] = $sections;

$title = 'Edit Survey - ' . ($response['site_id'] ?? 'Unknown Site');
ob_start();
?>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-gray-900">Edit Survey Response</h1>
            <p class="mt-2 text-lg text-gray-600">Site: <span
                    class="font-semibold text-blue-600"><?php echo htmlspecialchars($response['site_id'] ?? 'Unknown'); ?></span>
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

<div id="editSurveyApp" class="professional-table bg-white">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($response['survey_title']); ?></h3>
        <?php if ($response['survey_description']): ?>
            <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($response['survey_description']); ?></p>
        <?php endif; ?>
    </div>

    <div class="p-6">
        <form @submit.prevent="updateSurvey" enctype="multipart/form-data">
            <input type="hidden" name="response_id" value="<?php echo $responseId; ?>">

            <!-- Render form fields based on structure -->
            <div v-for="(section, sIndex) in sections" :key="section.id" class="form-section mb-8">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">{{ section.title }}</h4>
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
                            :type="field.field_type" v-model="formData[field.id]" :placeholder="field.placeholder"
                            :required="field.is_required" class="form-input">

                        <!-- Textarea -->
                        <textarea v-if="field.field_type === 'textarea'" v-model="formData[field.id]"
                            :placeholder="field.placeholder" :required="field.is_required" class="form-textarea"
                            rows="3"></textarea>

                        <!-- Date, Time -->
                        <input v-if="['date', 'time'].includes(field.field_type)" :type="field.field_type"
                            v-model="formData[field.id]" :required="field.is_required" class="form-input">

                        <!-- DateTime -->
                        <input v-if="['datetime', 'datetime-local'].includes(field.field_type)" type="datetime-local"
                            v-model="formData[field.id]" :required="field.is_required" class="form-input">

                        <!-- Select Dropdown -->
                        <select v-if="field.field_type === 'select'" v-model="formData[field.id]"
                            :required="field.is_required" class="form-select">
                            <option value="">Select an option...</option>
                            <option v-for="opt in getOptions(field.options)" :key="opt" :value="opt">{{ opt }}</option>
                        </select>

                        <!-- Radio Buttons -->
                        <div v-if="field.field_type === 'radio'" class="space-y-2">
                            <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center">
                                <input type="radio" :name="'field_' + field.id" :value="opt"
                                    v-model="formData[field.id]" :required="field.is_required" class="mr-2">
                                <span class="text-sm">{{ opt }}</span>
                            </label>
                        </div>

                        <!-- Checkboxes -->
                        <div v-if="field.field_type === 'checkbox'" class="space-y-2">
                            <label v-for="opt in getOptions(field.options)" :key="opt" class="flex items-center">
                                <input type="checkbox" :value="opt" @change="updateCheckbox(field.id, opt, $event)"
                                    :checked="isCheckboxChecked(field.id, opt)" class="mr-2">
                                <span class="text-sm">{{ opt }}</span>
                            </label>
                        </div>

                        <!-- File Upload -->
                        <div v-if="field.field_type === 'file'">
                            <!-- Show existing files -->
                            <div v-if="hasExistingFiles(field.id)" class="mb-3">
                                <p class="text-xs text-gray-600 mb-2">Current files:</p>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mb-2">
                                    <div v-for="(file, index) in getExistingFiles(field.id)" :key="index"
                                        class="relative group">
                                        <div v-if="isImageFile(file)" class="border rounded relative">
                                            <img :src="'../' + file.file_path" :alt="file.original_name"
                                                class="w-full h-24 object-cover rounded">
                                            <button type="button" @click="triggerDelete(field.id, index)"
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
                                            <button type="button" @click="triggerDelete(field.id, index)"
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
                                @change="handleFileUpload(field.id, $event, field)" class="form-input">
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
                                    :type="field.field_type" v-model="formData[field.id]"
                                    :placeholder="field.placeholder" :required="field.is_required" class="form-input">

                                <textarea v-if="field.field_type === 'textarea'" v-model="formData[field.id]"
                                    :placeholder="field.placeholder" :required="field.is_required" class="form-textarea"
                                    rows="3"></textarea>

                                <input v-if="['date', 'time'].includes(field.field_type)" :type="field.field_type"
                                    v-model="formData[field.id]" :required="field.is_required" class="form-input">

                                <input v-if="['datetime', 'datetime-local'].includes(field.field_type)"
                                    type="datetime-local" v-model="formData[field.id]" :required="field.is_required"
                                    class="form-input">

                                <select v-if="field.field_type === 'select'" v-model="formData[field.id]"
                                    :required="field.is_required" class="form-select">
                                    <option value="">Select an option...</option>
                                    <option v-for="opt in getOptions(field.options)" :key="opt" :value="opt">{{ opt }}
                                    </option>
                                </select>

                                <!-- File Upload -->
                                <div v-if="field.field_type === 'file'">
                                    <!-- Show existing files -->
                                    <div v-if="hasExistingFiles(field.id)" class="mb-3">
                                        <p class="text-xs text-gray-600 mb-2">Current files:</p>
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mb-2">
                                            <div v-for="(file, index) in getExistingFiles(field.id)" :key="index"
                                                class="relative group">
                                                <div v-if="isImageFile(file)" class="border rounded relative">
                                                    <img :src="'../' + file.file_path" :alt="file.original_name"
                                                        class="w-full h-24 object-cover rounded">
                                                    <button type="button" @click="triggerDelete(field.id, index)"
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
                                                    <button type="button" @click="triggerDelete(field.id, index)"
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
                                        @change="handleFileUpload(field.id, $event, field)" class="form-input">
                                    <p v-if="field.help_text" class="text-xs text-gray-500 mt-1">{{ field.help_text }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t">
                <a href="view-survey2.php?id=<?php echo $responseId; ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" :disabled="submitting" class="btn btn-primary">
                    <span v-if="!submitting">Update Survey</span>
                    <span v-else>Updating...</span>
                </button>
            </div>
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
                sections: <?php echo json_encode($formStructure['sections'] ?? []); ?>,
                formData: <?php echo json_encode($formData); ?>,
                files: {},
                submitting: false,
                showDeleteModal: false,
                pendingDelete: { fieldId: null, index: null },
                toast: { show: false, message: '', type: 'success' }
            };
        },
        methods: {
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
            async updateSurvey() {
                if (this.submitting) return;

                this.submitting = true;
                try {
                    const formData = new FormData();
                    formData.append('response_id', <?php echo $responseId; ?>);
                    formData.append('form_data', JSON.stringify(this.formData));

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
            }
        }
    }).mount('#editSurveyApp');
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../includes/admin_layout.php';
?>
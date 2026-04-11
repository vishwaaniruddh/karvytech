<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/DynamicSurvey.php';

$surveyModel = new DynamicSurvey();
$db = Database::getInstance()->getConnection();

// Get filter parameters
$typeFilter = $_GET['type'] ?? '';
$customerFilter = $_GET['customer_id'] ?? '';
$defaultCreateType = $typeFilter ?: 'survey';

// Build query
$query = "
    SELECT s.*, c.name as customer_name 
    FROM dynamic_surveys s 
    LEFT JOIN customers c ON s.customer_id = c.id 
    WHERE 1=1
";
$params = [];

if ($typeFilter) {
    $query .= " AND s.form_type = ?";
    $params[] = $typeFilter;
}
if ($customerFilter) {
    $query .= " AND s.customer_id = ?";
    $params[] = $customerFilter;
}

$query .= " ORDER BY s.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$surveys = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get customers for filters
$stmt = $db->query("SELECT id, name as company_name FROM customers ORDER BY name ASC");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="flex justify-between items-start mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Form Maker</h1>
        <p class="mt-1 text-sm text-gray-600">Create and manage dynamic forms for Surveys and Installations</p>
    </div>
    <a href="form-maker-v2.php?type=<?php echo htmlspecialchars(urlencode($defaultCreateType)); ?><?php echo $customerFilter !== '' ? '&customer_id=' . htmlspecialchars(urlencode($customerFilter)) : ''; ?>" class="btn btn-primary shadow-sm bg-blue-600 border-none hover:bg-blue-700">
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
        </svg>
        Add New Form (V2)
    </a>
</div>

<!-- Filters -->
<div class="card mb-6 bg-gray-50 border-gray-200">
    <div class="card-body p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="form-label text-xs uppercase tracking-wider text-gray-500">Filter by Type</label>
                <select name="type" class="form-select text-sm" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="survey" <?php echo $typeFilter === 'survey' ? 'selected' : ''; ?>>Survey</option>
                    <option value="installation" <?php echo $typeFilter === 'installation' ? 'selected' : ''; ?>>Installation</option>
                </select>
            </div>
            <div>
                <label class="form-label text-xs uppercase tracking-wider text-gray-500">Filter by Customer</label>
                <select name="customer_id" class="form-select text-sm" onchange="this.form.submit()">
                    <option value="">All Customers</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo $customerFilter == $c['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['company_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="md:col-span-2 flex justify-end">
                <a href="form-maker.php" class="text-sm text-blue-600 hover:underline">Clear Filters</a>
            </div>
        </form>
    </div>
</div>

<!-- Forms List -->
<div class="card">
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Form Details</th>
                        <th>Type</th>
                        <th>Customer</th>
                        <th>Fields</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($surveys)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-12 text-gray-400 italic">No forms found matching your criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($surveys as $survey): ?>
                            <tr>
                                <td>
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($survey['title']); ?></div>
                                    <div class="text-xs text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars($survey['description']); ?></div>
                                </td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $survey['form_type'] === 'survey' ? 'bg-indigo-100 text-indigo-800' : 'bg-orange-100 text-orange-800'; ?>">
                                        <?php echo ucfirst($survey['form_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($survey['customer_name']): ?>
                                        <div class="text-sm text-gray-700"><?php echo htmlspecialchars($survey['customer_name']); ?></div>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 italic">Global</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-sm"><?php echo count($surveyModel->getFields($survey['id'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $survey['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo ucfirst($survey['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="flex space-x-2">
                                        <a href="form-maker-v2.php?id=<?php echo $survey['id']; ?>" class="btn btn-sm btn-secondary" title="Edit Form">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
                                        </a>
                                        <button onclick="previewSurvey(<?php echo $survey['id']; ?>)" class="btn btn-sm btn-secondary" title="Preview Form">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path></svg>
                                        </button>
                                        <a href="form-responses.php?id=<?php echo $survey['id']; ?>" class="btn btn-sm btn-secondary" title="View Responses">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path></svg>
                                        </a>
                                        <button onclick="deleteSurvey(<?php echo $survey['id']; ?>, '<?php echo htmlspecialchars(addslashes($survey['title'])); ?>')" class="btn btn-sm btn-danger" title="Delete Form">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Builder Modal (Same as before but with Customer select and Type select) -->
<div id="surveyBuilderModal" class="modal">
    <div class="modal-content max-w-4xl">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Create New Form</h3>
            <button onclick="closeModal('surveyBuilderModal')" class="modal-close">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
            </button>
        </div>
        <form id="surveyBuilderForm">
            <input type="hidden" id="survey_id" name="survey_id">
            <div class="modal-body max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="form-group">
                        <label class="form-label font-semibold">Form Title *</label>
                        <input type="text" name="title" id="survey_title" class="form-input" required>
                    </div>
                    <div class="form-group text-sm">
                        <label class="form-label font-semibold">Type *</label>
                        <div class="flex space-x-6 mt-2">
                             <label class="inline-flex items-center">
                                 <input type="radio" name="form_type" value="survey" checked class="form-radio text-blue-600">
                                 <span class="ml-2">Survey</span>
                             </label>
                             <label class="inline-flex items-center">
                                 <input type="radio" name="form_type" value="installation" class="form-radio text-blue-600">
                                 <span class="ml-2">Installation</span>
                             </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label font-semibold">Customer (Optional)</label>
                        <select name="customer_id" id="customer_id_modal" class="form-select">
                            <option value="">All Customers</option>
                            <?php foreach ($customers as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['company_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label font-semibold">Status</label>
                        <select name="status" id="survey_status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group col-span-2">
                        <label class="form-label font-semibold">Description</label>
                        <textarea name="description" id="survey_description" class="form-textarea" rows="2"></textarea>
                    </div>
                </div>

                <hr class="my-6 border-gray-100">
                
                <div class="flex justify-between items-center mb-6">
                    <h4 class="text-md font-bold text-gray-700 uppercase tracking-wide">Fields Configuration</h4>
                    <button type="button" onclick="addNewField()" class="btn btn-sm btn-secondary flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Add New Field
                    </button>
                </div>
                
                <div id="fieldsContainer" class="space-y-4"></div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('surveyBuilderModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Form Configuration</button>
            </div>
        </form>
    </div>
</div>

<template id="fieldTemplate">
    <div class="field-item bg-white border border-gray-200 rounded-lg p-4 shadow-sm" data-index="{INDEX}">
        <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-50">
            <span class="text-xs font-bold text-blue-600 uppercase">Field {NUM}</span>
            <button type="button" onclick="removeField(this)" class="p-1 hover:bg-red-50 rounded-full text-red-400 hover:text-red-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        </div>
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 md:col-span-6">
                <label class="form-label text-xs font-semibold">Field Label *</label>
                <input type="text" name="fields[{INDEX}][label]" class="form-input text-sm" required>
            </div>
            <div class="col-span-12 md:col-span-4">
                <label class="form-label text-xs font-semibold">Field Type *</label>
                <select name="fields[{INDEX}][field_type]" class="form-select text-sm" onchange="handleFieldTypeChange(this)">
                    <option value="text">Short Text</option>
                    <option value="textarea">Long Text</option>
                    <option value="number">Number</option>
                    <option value="date">Date</option>
                    <option value="select">Dropdown</option>
                    <option value="radio">Radio Buttons</option>
                    <option value="checkbox">Checkboxes</option>
                    <option value="file">File Upload</option>
                </select>
            </div>
            <div class="col-span-12 md:col-span-2">
                <label class="form-label text-xs font-semibold">Required</label>
                <div class="mt-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="fields[{INDEX}][is_required]" value="1" class="form-checkbox text-blue-600 rounded">
                        <span class="ml-2 text-sm text-gray-600">Yes</span>
                    </label>
                </div>
            </div>

            <div class="col-span-12 options-config hidden">
                <label class="form-label text-xs font-semibold">Options (Comma separated)</label>
                <input type="text" name="fields[{INDEX}][options]" class="form-input text-sm" placeholder="e.g. Option 1, Option 2">
            </div>

            <div class="col-span-12 file-config hidden bg-blue-50 p-3 rounded">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label text-xs font-semibold text-blue-800">Multiplicity</label>
                        <select name="fields[{INDEX}][file_config][multiple]" class="form-select text-sm border-blue-200">
                            <option value="0">Single Upload</option>
                            <option value="1">Multiple Uploads</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-xs font-semibold text-blue-800">Accepted Extensions</label>
                        <input type="text" name="fields[{INDEX}][file_config][accept]" class="form-input text-sm border-blue-200" placeholder=".jpg,.png,.pdf">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    let fieldsCount = 0;

    function openCreateSurveyModal() {
        document.getElementById('modalTitle').textContent = 'Add New Form';
        document.getElementById('surveyBuilderForm').reset();
        document.getElementById('survey_id').value = '';
        document.getElementById('fieldsContainer').innerHTML = '';
        fieldsCount = 0;
        addNewField();
        openModal('surveyBuilderModal');
    }

    function addNewField(data = null) {
        const container = document.getElementById('fieldsContainer');
        const template = document.getElementById('fieldTemplate').innerHTML;
        const index = fieldsCount++;
        
        let html = template.replace(/{INDEX}/g, index).replace(/{NUM}/g, index + 1);
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        container.appendChild(wrapper.firstElementChild);
        
        if (data) {
            const item = container.querySelector(`[data-index="${index}"]`);
            item.querySelector(`[name="fields[${index}][label]"]`).value = data.label;
            item.querySelector(`[name="fields[${index}][field_type]"]`).value = data.field_type;
            item.querySelector(`[name="fields[${index}][is_required]"]`).checked = data.is_required == 1;
            handleFieldTypeChange(item.querySelector(`[name="fields[${index}][field_type]"]`));
            
            if (data.options) item.querySelector(`[name="fields[${index}][options]"]`).value = data.options;
            
            if (data.file_config) {
                const config = JSON.parse(data.file_config);
                if (item.querySelector(`[name="fields[${index}][file_config][multiple]"]`))
                    item.querySelector(`[name="fields[${index}][file_config][multiple]"]`).value = config.multiple ? 1 : 0;
                if (item.querySelector(`[name="fields[${index}][file_config][accept]"]`))
                    item.querySelector(`[name="fields[${index}][file_config][accept]"]`).value = config.accept || '';
            }
        }
    }

    function handleFieldTypeChange(select) {
        const item = select.closest('.field-item');
        const optionsConfig = item.querySelector('.options-config');
        const fileConfig = item.querySelector('.file-config');
        optionsConfig.classList.add('hidden');
        fileConfig.classList.add('hidden');
        if (['select', 'radio', 'checkbox'].includes(select.value)) optionsConfig.classList.remove('hidden');
        else if (select.value === 'file') fileConfig.classList.remove('hidden');
    }

    function removeField(btn) { btn.closest('.field-item').remove(); }

    function editSurvey(id) {
        fetch(`../../api/surveys.php?id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const s = data.survey;
                    document.getElementById('modalTitle').textContent = 'Edit Form Structure';
                    document.getElementById('survey_id').value = s.id;
                    document.getElementById('survey_title').value = s.title;
                    document.getElementById('survey_description').value = s.description;
                    document.getElementById('survey_status').value = s.status;
                    document.getElementById('customer_id_modal').value = s.customer_id || '';
                    
                    const radios = document.getElementsByName('form_type');
                    radios.forEach(r => {
                         if (r.value === s.form_type) r.checked = true;
                    });
                    
                    document.getElementById('fieldsContainer').innerHTML = '';
                    fieldsCount = 0;
                    data.fields.forEach(f => addNewField(f));
                    openModal('surveyBuilderModal');
                }
            });
    }

    document.getElementById('surveyBuilderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('../../api/surveys.php?action=save', { method: 'POST', body: new FormData(this) })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('Form saved successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else showAlert(data.message, 'error');
        });
    });

    function previewSurvey(id) { window.open(`../surveys/preview-v2.php?id=${id}`, '_blank'); }
    
    function deleteSurvey(id, title) {
        if (confirm(`Are you sure you want to delete the form "${title}"?\n\nThis will also delete all sections, fields, and responses associated with this form. This action cannot be undone.`)) {
            fetch(`../../api/surveys_v2.php?action=delete&id=${id}`, { method: 'DELETE' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Form deleted successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showAlert(data.message || 'Failed to delete form', 'error');
                    }
                })
                .catch(err => {
                    showAlert('An error occurred while deleting the form', 'error');
                    console.error(err);
                });
        }
    }
    
    function openModal(id) { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }
    function showAlert(m, t) {
        const d = document.createElement('div');
        d.className = `fixed bottom-4 right-4 z-50 p-4 rounded bg-${t==='success'?'green':'red'}-500 text-white shadow-xl`;
        d.textContent = m; document.body.appendChild(d); setTimeout(() => d.remove(), 2500);
    }
</script>

<style>
    .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1000; padding: 2rem; }
    .modal.active { display: flex; align-items: center; justify-content: center; }
    .modal-content { background: white; border-radius: 0.75rem; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
    .form-radio { color: #3b82f6; }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>

<?php
require_once __DIR__ . '/../../config/database.php';

$surveyId = $_GET['id'] ?? null;
$type = $_GET['type'] ?? 'survey';
$type = ($type === 'installation') ? 'installation' : 'survey';
$customerId = $_GET['customer_id'] ?? '';
$customerId = ($customerId !== '' && ctype_digit((string)$customerId)) ? (string)$customerId : '';

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id, name as company_name FROM customers ORDER BY name ASC");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = $surveyId ? 'Edit Form Builder' : 'Create Form Builder';

ob_start();
?>

<div class="flex items-start justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center text-sm text-gray-600 mb-2">
            <a href="form-maker.php?type=<?php echo htmlspecialchars(urlencode($type)); ?><?php echo $customerId !== '' ? '&customer_id=' . htmlspecialchars(urlencode($customerId)) : ''; ?>" class="text-blue-600 hover:underline">Form Maker</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
            <span class="text-gray-500"><?php echo $surveyId ? 'Edit' : 'New'; ?> Dynamic Form</span>
        </div>
        <h1 class="text-2xl font-semibold text-gray-900">Form Builder</h1>
        <p class="text-sm text-gray-600 mt-2">
            Create a Google-Forms style questionnaire. Add questions, choose types, and save the configuration.
        </p>
    </div>

    <div class="flex items-center gap-3">
        <a href="form-maker.php?type=<?php echo htmlspecialchars(urlencode($type)); ?><?php echo $customerId !== '' ? '&customer_id=' . htmlspecialchars(urlencode($customerId)) : ''; ?>" class="btn btn-secondary">Back to List</a>
        <a id="previewLink" href="#" target="_blank" class="btn btn-secondary <?php echo $surveyId ? '' : 'opacity-50 pointer-events-none'; ?>">
            Preview
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Builder -->
    <div class="lg:col-span-2">
        <div class="card overflow-hidden">
            <div class="card-body p-6">
                <form id="surveyBuilderForm">
                    <input type="hidden" id="survey_id" name="survey_id" value="<?php echo htmlspecialchars($surveyId ?? ''); ?>">
                    <div class="form-section">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-lg font-bold text-gray-800">Form details</h2>
                                <p class="text-xs text-gray-500 mt-1">These settings control how the form is presented to respondents.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label font-semibold">Form Title *</label>
                                <input type="text" name="title" id="survey_title" class="form-input" required>
                            </div>

                            <div class="form-group text-sm">
                                <label class="form-label font-semibold">Type *</label>
                                <div class="flex gap-6 mt-2">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="form_type" value="survey" class="form-radio text-blue-600" <?php echo $type === 'survey' ? 'checked' : ''; ?>>
                                        <span class="ml-2">Survey</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="form_type" value="installation" class="form-radio text-blue-600" <?php echo $type === 'installation' ? 'checked' : ''; ?>>
                                        <span class="ml-2">Installation</span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label font-semibold">Customer (Optional)</label>
                                <select name="customer_id" id="customer_id_modal" class="form-select">
                                    <option value="" <?php echo $customerId === '' ? 'selected' : ''; ?>>All Customers</option>
                                    <?php foreach ($customers as $c): ?>
                                        <option value="<?php echo (int)$c['id']; ?>" <?php echo ((string)$c['id'] === $customerId) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($c['company_name']); ?>
                                        </option>
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

                            <div class="form-group col-span-1 md:col-span-2">
                                <label class="form-label font-semibold">Description</label>
                                <textarea name="description" id="survey_description" class="form-textarea" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h2 class="text-lg font-bold text-gray-800">Questions</h2>
                                <p class="text-xs text-gray-500 mt-1">
                                    Add questions in order. For Dropdown/Radio/Checkbox, enter options separated by commas.
                                </p>
                            </div>

                            <button type="button" onclick="addNewField()" class="btn btn-sm btn-secondary flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Question
                            </button>
                            <button type="button" onclick="addNewField({field_type: 'section'})" class="btn btn-sm btn-outline-primary flex items-center ml-2">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                                Add Section
                            </button>
                        </div>

                        <div id="fieldsContainer" class="space-y-4"></div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 flex justify-between items-center gap-3">
                        <button type="button" onclick="openLivePreview()" class="btn btn-secondary">
                            Live Preview
                        </button>

                        <div class="flex items-center gap-3">
                            <button type="button" onclick="addNewField()" class="btn btn-secondary">
                                Add Question
                            </button>
                            <button type="button" onclick="addNewField({field_type: 'section'})" class="btn btn-secondary border-blue-200 text-blue-700">
                                Add Section
                            </button>
                            <button type="submit" id="saveSurveyBtn" class="btn btn-primary">
                                Save Form
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview -->
    <div class="lg:col-span-1">
        <div class="card overflow-hidden sticky top-6">
            <div class="card-body p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-md font-bold text-gray-800">Preview</h2>
                    <span class="badge badge-info">Google style</span>
                </div>
                <div id="formPreview" class="space-y-4">
                    <div class="p-4 rounded-lg border border-gray-200 bg-white">
                        <div class="font-bold text-gray-900" id="previewTitle">Your form title</div>
                        <div class="text-sm text-gray-600 mt-1" id="previewDescription"></div>
                        <div class="text-xs text-gray-500 mt-2">Questions will appear here as you build.</div>
                    </div>
                    <div class="text-xs text-gray-500">
                        Tip: use <span class="font-semibold">Save Form</span> to persist your changes.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="fieldTemplate">
    <div class="field-item bg-white border border-gray-200 rounded-xl p-4 shadow-sm" data-index="{INDEX}">
        <div class="flex justify-between items-center mb-3 pb-3 border-b border-gray-50">
            <span class="text-xs font-bold text-blue-600 uppercase">Question {NUM}</span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="addNewField({}, this.closest('.field-item'))" class="btn-add-inside hidden btn btn-xs btn-outline-primary py-1 px-2 text-[10px] uppercase font-bold tracking-wider rounded-lg flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Question Inside
                </button>
                <button type="button" onclick="removeField(this)" class="p-1 hover:bg-red-50 rounded-full text-red-400 hover:text-red-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 md:col-span-6">
                <label class="form-label text-xs font-semibold">Question Text *</label>
                <input type="text" name="fields[{INDEX}][label]" class="form-input text-sm" required>
            </div>

            <div class="col-span-12 md:col-span-4">
                <label class="form-label text-xs font-semibold">Question Type *</label>
                <select name="fields[{INDEX}][field_type]" class="form-select text-sm" onchange="handleFieldTypeChange(this)">
                    <option value="text">Short Text</option>
                    <option value="textarea">Long Text</option>
                    <option value="number">Number</option>
                    <option value="date">Date</option>
                    <option value="datetime">Date & Time</option>
                    <option value="section">Section Header</option>
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

            <div class="col-span-12 file-config hidden bg-blue-50 p-3 rounded-xl">
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
                <p class="text-xs text-blue-800 mt-2">Files will be validated on upload based on these settings.</p>
            </div>
        </div>
    </div>
</template>

<script>
    let fieldsCount = 0;
    const existingSurveyId = document.getElementById('survey_id').value || '';

    function addNewField(data = null, insertAfterEl = null) {
        const container = document.getElementById('fieldsContainer');
        const template = document.getElementById('fieldTemplate').innerHTML;
        const index = fieldsCount++;

        const html = template.replace(/{INDEX}/g, index).replace(/{NUM}/g, index + 1);
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        const newEl = wrapper.firstElementChild;

        if (insertAfterEl) {
            insertAfterEl.parentNode.insertBefore(newEl, insertAfterEl.nextSibling);
        } else {
            container.appendChild(newEl);
        }

        if (data) {
            const item = container.querySelector(`[data-index="${index}"]`);
            item.querySelector(`[name="fields[${index}][label]"]`).value = data.label || '';
            const loadedType = (data.field_type ?? 'text');
            // Normalize any legacy `datetime-local` to `datetime`.
            const normalizedType = (String(loadedType).trim() === 'datetime-local') ? 'datetime' : loadedType;
            item.querySelector(`[name="fields[${index}][field_type]"]`).value = normalizedType || 'text';
            item.querySelector(`[name="fields[${index}][is_required]"]`).checked = (data.is_required == 1);
            
            if (normalizedType === 'section') {
                item.classList.add('bg-blue-50', 'border-blue-200');
                item.querySelector(`[name="fields[${index}][label]"]`).placeholder = "Enter Section Title (e.g. Power Details)";
                item.querySelector(`[name="fields[${index}][label]"]`).classList.add('text-lg', 'font-bold');
            }

            handleFieldTypeChange(item.querySelector(`[name="fields[${index}][field_type]"]`));

            if (data.options) {
                item.querySelector(`[name="fields[${index}][options]"]`).value = data.options;
            }

            if (data.file_config) {
                try {
                    const config = JSON.parse(data.file_config);
                    if (item.querySelector(`[name="fields[${index}][file_config][multiple]"]`)) {
                        item.querySelector(`[name="fields[${index}][file_config][multiple]"]`).value = config.multiple ? 1 : 0;
                    }
                    if (item.querySelector(`[name="fields[${index}][file_config][accept]"]`)) {
                        item.querySelector(`[name="fields[${index}][file_config][accept]"]`).value = config.accept || '';
                    }
                } catch (e) {
                    // Ignore invalid JSON stored in DB for file config
                }
            }
        }

        syncPreview();
    }

    function handleFieldTypeChange(select) {
        const fileConfig = item.querySelector('.file-config');
        const requiredConfig = item.querySelector('.col-span-12.md\\:col-span-2'); // Required checkbox container
        const optionsLabel = optionsConfig.querySelector('.form-label');
        const addInsideBtn = item.querySelector('.btn-add-inside');

        optionsConfig.classList.add('hidden');
        fileConfig.classList.add('hidden');
        if (requiredConfig) requiredConfig.classList.remove('hidden');
        if (addInsideBtn) addInsideBtn.classList.add('hidden');
        item.classList.remove('bg-blue-50', 'border-blue-200');
        if (optionsLabel) optionsLabel.textContent = "Options (Comma separated)";

        if (['select', 'radio', 'checkbox'].includes(select.value)) {
            optionsConfig.classList.remove('hidden');
        } else if (select.value === 'file') {
            fileConfig.classList.remove('hidden');
        } else if (select.value === 'section') {
            if (requiredConfig) requiredConfig.classList.add('hidden');
            if (addInsideBtn) addInsideBtn.classList.remove('hidden');
            optionsConfig.classList.remove('hidden');
            if (optionsLabel) optionsLabel.textContent = "Section Description (Optional)";
            item.classList.add('bg-blue-50', 'border-blue-200');
        }

        syncPreview();
    }

    function removeField(btn) {
        btn.closest('.field-item').remove();
        syncPreview();
    }

    function getPreviewUrl() {
        const id = document.getElementById('survey_id').value;
        const formType = (document.querySelector('input[name="form_type"]:checked') || {}).value || 'survey';
        if (!id) {
            // For unsaved forms, show the preview panel only (no external preview page).
            return null;
        }
        return `../surveys/preview.php?id=${encodeURIComponent(id)}&type=${encodeURIComponent(formType)}`;
    }

    function openLivePreview() {
        const url = getPreviewUrl();
        if (!url) {
            showToast('Save the form to enable preview', 'error');
            return;
        }
        window.open(url, '_blank');
    }

    function showToast(message, type) {
        const d = document.createElement('div');
        d.className = `fixed bottom-5 right-5 z-50 p-4 rounded-xl text-white shadow-lg ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
        d.textContent = message;
        document.body.appendChild(d);
        setTimeout(() => d.remove(), 2600);
    }

    function syncPreview() {
        const title = (document.getElementById('survey_title').value || '').trim() || 'Your form title';
        const description = (document.getElementById('survey_description').value || '').trim();
        document.getElementById('previewTitle').textContent = title;

        const descEl = document.getElementById('previewDescription');
        if (description) {
            descEl.textContent = description;
            descEl.classList.remove('hidden');
        } else {
            descEl.textContent = '';
            descEl.classList.add('hidden');
        }

        const container = document.getElementById('formPreview');
        // Keep the first card (title/description), rebuild questions beneath it.
        const existingQuestionNodes = container.querySelectorAll('[data-preview-question="1"]');
        existingQuestionNodes.forEach(n => n.remove());

        const fieldItems = document.querySelectorAll('#fieldsContainer .field-item');
        fieldItems.forEach((item, idx) => {
            const actualIndex = item.getAttribute('data-index') || idx;
            const displayNumber = idx + 1;

            const labelEl = item.querySelector(`input[name="fields[${actualIndex}][label]"]`);
            const typeEl = item.querySelector(`select[name="fields[${actualIndex}][field_type]"]`);
            const requiredEl = item.querySelector(`input[name="fields[${actualIndex}][is_required]"]`);

            const label = (labelEl && labelEl.value ? labelEl.value : '').trim();
            const type = (typeEl && typeEl.value ? typeEl.value : 'text');
            const required = !!(requiredEl && requiredEl.checked);
            const optionsEl = item.querySelector(`input[name="fields[${actualIndex}][options]"]`);
            const optionsVal = (optionsEl && optionsEl.value) ? optionsEl.value : '';

            let inputMarkup = '';
            if (type === 'text' || type === 'number' || type === 'date' || type === 'datetime' || type === 'datetime-local') {
                const inputType = type === 'text' ? 'text' : (type === 'datetime' || type === 'datetime-local' ? 'datetime-local' : type);
                inputMarkup = `<input disabled class="w-full p-2 border border-gray-200 rounded-md bg-gray-50" type="${inputType}">`;
            } else if (type === 'textarea') {
                inputMarkup = `<textarea disabled rows="3" class="w-full p-2 border border-gray-200 rounded-md bg-gray-50"></textarea>`;
            } else if (type === 'select') {
                const options = optionsVal.split(',').map(s => s.trim()).filter(Boolean);
                const opts = options.map(o => `<option>${o}</option>`).join('');
                inputMarkup = `<select disabled class="w-full p-2 border border-gray-200 rounded-md bg-gray-50"><option value="">Select...</option>${opts}</select>`;
            } else if (type === 'radio') {
                const options = optionsVal.split(',').map(s => s.trim()).filter(Boolean);
                inputMarkup = `<div class="space-y-2">${options.map(o => `<label class="flex items-center gap-2 text-sm text-gray-700"><input disabled type="radio" name="r${idx}"> <span>${o}</span></label>`).join('')}</div>`;
            } else if (type === 'checkbox') {
                const options = optionsVal.split(',').map(s => s.trim()).filter(Boolean);
                inputMarkup = `<div class="space-y-2">${options.map(o => `<label class="flex items-center gap-2 text-sm text-gray-700"><input disabled type="checkbox" name="c${idx}"> <span>${o}</span></label>`).join('')}</div>`;
            } else if (type === 'file') {
                const multipleEl = item.querySelector(`select[name="fields[${actualIndex}][file_config][multiple]"]`);
                const acceptEl = item.querySelector(`input[name="fields[${actualIndex}][file_config][accept]"]`);
                const multiple = (multipleEl && multipleEl.value === '1');
                const accept = (acceptEl && acceptEl.value) ? acceptEl.value : '';
                inputMarkup = `<div class="p-3 border border-dashed border-gray-300 rounded-md bg-gray-50 text-sm text-gray-700">Upload ${multiple ? 'multiple files' : 'a file'}${accept ? ' (accept: ' + accept + ')' : ''}</div>`;
            }

            const q = document.createElement('div');
            q.dataset.previewQuestion = '1';
            
            if (type === 'section') {
                q.className = 'mt-6 mb-4 overflow-hidden rounded-xl border border-gray-200 shadow-sm';
                q.innerHTML = `
                    <div class="bg-gray-50 p-4 border-b border-gray-200">
                        <div class="text-lg font-bold text-gray-900 leading-tight">
                            ${label || '(Untitled Section)'}
                        </div>
                        ${optionsVal ? `<div class="text-xs text-gray-500 mt-1 font-medium italic">${optionsVal}</div>` : ''}
                    </div>
                `;
            } else {
                q.className = 'p-4 rounded-xl border border-gray-200 bg-white mb-4';
                q.innerHTML = `
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="font-semibold text-gray-800 text-sm">
                            ${label || '(Untitled question)'}
                            ${required ? '<span class="text-red-500 font-bold">*</span>' : ''}
                        </div>
                    </div>
                    <div>${inputMarkup}</div>
                `;
            }
            container.appendChild(q);
        });
    }

    document.getElementById('survey_title').addEventListener('input', syncPreview);
    document.getElementById('survey_description').addEventListener('input', syncPreview);
    document.getElementById('fieldsContainer').addEventListener('input', syncPreview);
    document.getElementById('fieldsContainer').addEventListener('change', syncPreview);

    document.getElementById('surveyBuilderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('saveSurveyBtn');
        const original = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Saving...';

        const payload = new FormData(this);
        fetch('../../api/surveys.php?action=save', {
            method: 'POST',
            body: payload
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Form saved successfully', 'success');
                const savedId = data.id || '';
                if (savedId) document.getElementById('survey_id').value = savedId;

                const formType = (document.querySelector('input[name="form_type"]:checked') || {}).value || 'survey';
                const previewHref = savedId ? `../surveys/preview.php?id=${encodeURIComponent(savedId)}` : '#';

                const previewLink = document.getElementById('previewLink');
                if (savedId) {
                    previewLink.href = previewHref;
                    previewLink.classList.remove('opacity-50', 'pointer-events-none');
                }

                setTimeout(() => {
                    const customerEl = document.getElementById('customer_id_modal');
                    const customerId = (customerEl && customerEl.value) ? customerEl.value : '';
                    const customerQuery = customerId ? `&customer_id=${encodeURIComponent(customerId)}` : '';
                    window.location.href = `form-maker-builder.php?id=${encodeURIComponent(savedId)}&type=${encodeURIComponent(formType)}${customerQuery}`;
                }, 900);
            } else {
                showToast(data.message || 'Failed to save form', 'error');
                btn.disabled = false;
                btn.textContent = original;
            }
        })
        .catch(err => {
            console.error(err);
            showToast('An error occurred while saving', 'error');
            btn.disabled = false;
            btn.textContent = original;
        });
    });

    function setInitialFieldIdNumbers() {
        // Keep numbers stable via fieldsCount; preview uses idx-based selectors for speed.
    }

    function loadExistingSurvey(id) {
        fetch(`../../api/surveys.php?id=${encodeURIComponent(id)}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    showToast(data.message || 'Failed to load form', 'error');
                    return;
                }

                const s = data.survey;
                document.getElementById('survey_title').value = s.title || '';
                document.getElementById('survey_description').value = s.description || '';
                document.getElementById('survey_status').value = s.status || 'active';
                document.getElementById('customer_id_modal').value = s.customer_id || '';

                const radios = document.getElementsByName('form_type');
                radios.forEach(r => {
                    if (r.value === s.form_type) r.checked = true;
                });

                document.getElementById('fieldsContainer').innerHTML = '';
                fieldsCount = 0;
                (data.fields || []).forEach(f => addNewField(f));

                document.getElementById('previewLink').href = `../surveys/preview.php?id=${encodeURIComponent(id)}`;
                syncPreview();
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (existingSurveyId) {
            loadExistingSurvey(existingSurveyId);
        } else {
            addNewField();
            syncPreview();
        }
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>


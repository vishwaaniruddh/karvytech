<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/BaseModel.php';

// Auth::requireRole(ADMIN_ROLE);

$title = 'Bulk Survey Management';
ob_start();
?>

<div class="min-h-screen bg-gray-50 pb-12">
    <!-- Header Area -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-20">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <div class="p-2 bg-indigo-600 rounded-lg text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </div>
                        Bulk Survey Management
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Efficiently manage survey data, approvals, and massive imports</p>
                </div>
                <div class="flex items-center gap-2 bg-gray-100 p-1 rounded-xl border border-gray-200">
                    <button onclick="switchTab('manage')" id="tab-manage" class="px-6 py-2 text-sm font-bold rounded-lg transition-all tab-btn active bg-white text-indigo-600 shadow-sm border border-gray-200">
                        Approvals & Management
                    </button>
                    <button onclick="switchTab('import')" id="tab-import" class="px-6 py-2 text-sm font-bold rounded-lg transition-all tab-btn text-gray-500 hover:text-gray-700">
                        Bulk Import Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto px-4 mt-8">
        
        <!-- SECTION 1: MANAGE & APPROVE -->
        <div id="section-manage" class="tab-pane active">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-2xl border border-gray-200 p-6 flex items-center gap-4 shadow-sm">
                    <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 font-bold uppercase tracking-wider">Pending Review</div>
                        <div class="text-2xl font-bold text-gray-900" id="stat-pending">0</div>
                    </div>
                </div>
                <!-- Other stats... -->
            </div>

            <!-- Management Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <h3 class="font-bold text-gray-900">Survey Submissions</h3>
                        <div class="flex items-center bg-white border border-gray-300 rounded-lg overflow-hidden h-9 px-3">
                             <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                             <input type="text" id="search-submissions" class="border-none outline-none text-sm w-48" placeholder="Search site or status...">
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button onclick="bulkApprove()" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-bold rounded-xl hover:bg-emerald-700 transition-all shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Bulk Approve
                        </button>
                        <button onclick="bulkReject()" class="inline-flex items-center px-4 py-2 bg-rose-600 text-white text-sm font-bold rounded-xl hover:bg-rose-700 transition-all shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Bulk Reject
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto min-h-[400px]">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">
                                    <input type="checkbox" id="select-all" class="rounded text-indigo-600 focus:ring-indigo-500">
                                </th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Site Information</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Customer / Form</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Surveyor</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Submitted</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="survey-list-body" class="divide-y divide-gray-100 italic text-gray-400">
                            <!-- JS loaded content -->
                            <tr><td colspan="7" class="text-center py-20">Loading submissions...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECTION 2: BULK IMPORT -->
        <div id="section-import" class="tab-pane hidden">
             <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                 <!-- Config Panel -->
                 <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                             <span class="w-6 h-6 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center text-xs">1</span>
                             Select Import Type
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Customer</label>
                                <select id="customer-select" onchange="loadCustomerForms(this.value)" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-sm">
                                    <option value="">-- Select Customer --</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Survey Form</label>
                                <select id="form-select" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 text-sm">
                                    <option value="">-- Choose Form --</option>
                                </select>
                            </div>
                            <div class="pt-4 border-t border-gray-100">
                                <button onclick="downloadSurveyTemplate()" class="w-full inline-flex items-center justify-center px-4 py-3 bg-white border-2 border-indigo-600 text-indigo-600 text-sm font-bold rounded-xl hover:bg-indigo-50 transition-all">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Download Template
                                </button>
                                <p class="text-[10px] text-center text-gray-400 mt-2 italic">Template will be generated based on selected form fields</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-indigo-900 rounded-2xl p-6 text-white shadow-lg overflow-hidden relative">
                         <div class="relative z-10">
                            <h4 class="font-bold mb-2">Pro Tip:</h4>
                            <p class="text-sm text-indigo-100 opacity-90 leading-relaxed">
                                Always use a fresh template. If you add or removed fields from the Form Maker, previous templates may fail to map correctly.
                            </p>
                         </div>
                         <svg class="absolute bottom-0 right-0 w-32 h-32 text-indigo-800 -mb-8 -mr-8 opacity-50" fill="currentColor" viewBox="0 0 20 20"><path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM5.884 6.643a1 1 0 10-1.242-1.564l-.707.707a1 1 0 001.414 1.414l.535-.557zm12.873 8.24l-.535.557a1 1 0 001.414 1.414l.707-.707a1 1 0 10-1.242-1.564zM11 16a1 1 0 10-2 0v1a1 1 0 102 0v-1zM5.884 13.357l-.535.557a1 1 0 101.414 1.414l.707-.707a1 1 0 00-1.242-1.564zM16.707 5.293l-.707.707a1 1 0 101.414 1.414l.707-.707a1 1 0 10-1.414-1.414zM3 11a1 1 0 100-2H2a1 1 0 100 2h1zm15 0a1 1 0 100-2h-1a1 1 0 100 2h1z"/></svg>
                    </div>
                 </div>

                 <!-- Upload Panel -->
                 <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                        <div id="drop-zone" class="relative group border-2 border-dashed border-gray-300 rounded-2xl p-16 text-center transition-all hover:border-indigo-400 hover:bg-indigo-50/30 cursor-pointer">
                            <input type="file" id="survey-file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".xlsx,.xls,.csv">
                            <div class="transition-transform group-hover:scale-110 duration-300">
                                <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                </div>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Upload Survey Data</h3>
                            <p class="text-gray-500 mb-0">Drag and drop or click to browse</p>
                        </div>

                        <div id="import-progress" class="hidden mt-8">
                            <div class="flex items-center justify-between mb-2">
                                <span id="import-status" class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Processing...</span>
                                <span id="import-percent" class="text-xs font-bold text-gray-900">0%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div id="import-bar" class="bg-indigo-600 h-full w-0 transition-all duration-300"></div>
                            </div>
                        </div>
                    </div>

                    <div id="import-results" class="hidden bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                            <h4 class="font-bold text-gray-900">Import Results</h4>
                            <div id="import-summary" class="flex gap-4">
                                <!-- Results summary badges -->
                            </div>
                        </div>
                        <div class="overflow-x-auto max-h-[400px]">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-white sticky top-0 shadow-sm border-b">
                                    <tr>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Row</th>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Site ID</th>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Outcome</th>
                                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Details</th>
                                    </tr>
                                </thead>
                                <tbody id="import-list-body" class="divide-y divide-gray-100">
                                    <!-- Dynamic content -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                 </div>
             </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let submissions = [];

document.addEventListener('DOMContentLoaded', () => {
    loadSubmissions();
    loadCustomers();
    
    // Select all functionality
    document.getElementById('select-all').addEventListener('change', (e) => {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = e.target.checked);
    });

    // File input handler
    document.getElementById('survey-file').addEventListener('change', handleImport);
});

function switchTab(tab) {
    document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
    document.getElementById('section-' + tab).classList.remove('hidden');
    
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-white', 'text-indigo-600', 'shadow-sm', 'border', 'border-gray-200');
        btn.classList.add('text-gray-500');
    });
    
    const activeBtn = document.getElementById('tab-' + tab);
    activeBtn.classList.remove('text-gray-500');
    activeBtn.classList.add('active', 'bg-white', 'text-indigo-600', 'shadow-sm', 'border', 'border-gray-200');
}

async function loadSubmissions() {
    try {
        const response = await fetch('../../api/survey_responses.php?action=get_all_pending');
        const data = await response.json();
        
        if (data.success) {
            submissions = data.responses || [];
            document.getElementById('stat-pending').textContent = submissions.length;
            renderSubmissions();
        }
    } catch (err) {
        console.error("Failed to load submissions", err);
    }
}

function renderSubmissions() {
    const body = document.getElementById('survey-list-body');
    if (submissions.length === 0) {
        body.innerHTML = '<tr><td colspan="7" class="text-center py-20 text-gray-400">No pending submissions found.</td></tr>';
        return;
    }

    body.innerHTML = submissions.map(s => `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4">
                <input type="checkbox" class="row-checkbox rounded text-indigo-600 focus:ring-indigo-500" value="${s.id}">
            </td>
            <td class="px-6 py-4">
                <div class="font-bold text-gray-900">${s.site_code || 'N/A'}</div>
                <div class="text-[10px] text-gray-500">${s.location || ''}</div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm text-gray-900">${s.customer_name || 'N/A'}</div>
                <div class="text-[10px] text-indigo-500 font-medium">${s.form_title || ''}</div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm text-gray-900">${s.surveyor_name || 'System'}</div>
            </td>
            <td class="px-6 py-4">
                <span class="px-2 py-1 bg-amber-100 text-amber-700 text-[10px] font-bold uppercase rounded-md flex items-center gap-1 w-fit">
                    <span class="w-1 h-1 bg-amber-500 rounded-full animate-pulse"></span>
                    ${s.survey_status}
                </span>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm text-gray-600">${new Date(s.submitted_date).toLocaleDateString()}</div>
                <div class="text-[10px] text-gray-400">${new Date(s.submitted_date).toLocaleTimeString()}</div>
            </td>
            <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center gap-2">
                    <a href="../surveys/view-response.php?id=${s.id}" target="_blank" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="View Full Report">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                    <button onclick="approveOne(${s.id})" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all" title="Approve">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                    <button onclick="rejectOne(${s.id})" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Reject">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

async function bulkApprove() {
    const selected = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) return Swal.fire('No Selection', 'Please select at least one survey to approve.', 'warning');

    const { value: remarks } = await Swal.fire({
        title: 'Bulk Approval',
        input: 'textarea',
        inputLabel: 'Approval Remarks (Optional)',
        inputPlaceholder: 'Type your remarks here...',
        showCancelButton: true,
        confirmButtonText: 'Approve Selected',
        confirmButtonColor: '#059669'
    });

    if (remarks !== undefined) {
        processBulkAction('approve', selected, remarks);
    }
}

async function bulkReject() {
    const selected = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) return Swal.fire('No Selection', 'Please select at least one survey to reject.', 'warning');

    const { value: remarks } = await Swal.fire({
        title: 'Bulk Rejection',
        input: 'textarea',
        inputLabel: 'Reason for Rejection',
        inputPlaceholder: 'Explain why these surveys are being rejected...',
        showCancelButton: true,
        confirmButtonText: 'Reject Selected',
        confirmButtonColor: '#e11d48',
        inputValidator: (value) => {
            if (!value) return 'Providing a reason is mandatory for rejection.';
        }
    });

    if (remarks) {
        processBulkAction('reject', selected, remarks);
    }
}

async function processBulkAction(action, ids, remarks) {
    Swal.fire({ title: 'Processing...', didOpen: () => Swal.showLoading() });
    
    try {
        const response = await fetch('../../api/survey_responses.php?action=bulk_process', {
            method: 'POST',
            body: JSON.stringify({ action, ids, remarks }),
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await response.json();
        
        if (data.success) {
            Swal.fire('Success', `${ids.length} surveys have been ${action}d.`, 'success');
            loadSubmissions();
        } else {
            Swal.fire('Error', data.message || 'Operation failed', 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Technical failure during bulk action', 'error');
    }
}

async function loadCustomers() {
    try {
        const response = await fetch('../../api/survey_responses.php?action=get_customers');
        const data = await response.json();
        const select = document.getElementById('customer-select');
        if (data.success) {
            select.innerHTML = '<option value="">-- Select Customer --</option>' + 
                data.customers.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
        }
    } catch(err) {}
}

async function loadCustomerForms(customerId) {
    const select = document.getElementById('form-select');
    if (!customerId) {
        select.innerHTML = '<option value="">-- Choose Form --</option>';
        return;
    }
    
    try {
        const response = await fetch(`../../api/survey_responses.php?action=get_report&customer_id=${customerId}`);
        const data = await response.json();
        if (data.success && data.surveyForm) {
            select.innerHTML = `<option value="${data.surveyForm.id}">${data.surveyForm.title}</option>`;
        } else {
            select.innerHTML = '<option value="">No Active Form Found</option>';
        }
    } catch(err) {}
}

function downloadSurveyTemplate() {
    const formId = document.getElementById('form-select').value;
    if (!formId) return Swal.fire('Form Required', 'Please select a customer and form to generate a template.', 'warning');
    
    Swal.fire({ title: 'Generating Template...', didOpen: () => Swal.showLoading() });
    window.location.href = `api/download-survey-template.php?form_id=${formId}`;
    setTimeout(() => Swal.close(), 2000);
}

async function handleImport() {
    const file = document.getElementById('survey-file').files[0];
    const formId = document.getElementById('form-select').value;
    
    if (!formId) {
        Swal.fire('Form Required', 'Please select a customer and form before uploading.', 'error');
        return;
    }
    if (!file) return;

    document.getElementById('import-progress').classList.remove('hidden');
    document.getElementById('import-results').classList.add('hidden');
    updateImportProgress(20, 'Uploading...');

    const formData = new FormData();
    formData.append('file', file);
    formData.append('form_id', formId);

    try {
        const response = await fetch('api/process-bulk-surveys.php', {
            method: 'POST',
            body: formData
        });
        updateImportProgress(60, 'Saving entries...');
        const data = await response.json();
        
        if (data.success) {
            updateImportProgress(100, 'Complete');
            showImportResults(data);
        } else {
            Swal.fire('Failed', data.message || 'Import failed', 'error');
            document.getElementById('import-progress').classList.add('hidden');
        }
    } catch(err) {
        Swal.fire('Error', 'Technical failure', 'error');
        document.getElementById('import-progress').classList.add('hidden');
    }
}

function updateImportProgress(p, label) {
    document.getElementById('import-bar').style.width = p + '%';
    document.getElementById('import-percent').textContent = p + '%';
    document.getElementById('import-status').textContent = label;
}

function showImportResults(data) {
    document.getElementById('import-results').classList.remove('hidden');
    document.getElementById('import-progress').classList.add('hidden');
    
    document.getElementById('import-summary').innerHTML = `
        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-bold">Imported: ${data.imported}</span>
        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-bold">Failed: ${data.failed}</span>
    `;

    document.getElementById('import-list-body').innerHTML = data.rows.map(r => `
        <tr class="${r.status === 'failed' ? 'bg-red-50' : ''}">
            <td class="px-6 py-4 text-xs font-bold text-gray-500">${r.row}</td>
            <td class="px-6 py-4 text-xs font-bold text-gray-900">${r.site_id || '-'}</td>
            <td class="px-6 py-4">
                <span class="px-2 py-0.5 rounded uppercase text-[9px] font-bold ${r.status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                    ${r.status}
                </span>
            </td>
            <td class="px-6 py-4 text-xs text-gray-600">${r.message}</td>
        </tr>
    `).join('');
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../includes/admin_layout.php';
?>

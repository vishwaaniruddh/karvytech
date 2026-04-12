<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

// Auth::requireRole(ADMIN_ROLE);

$title = 'System Operations Master';
ob_start();

$db = Database::getInstance()->getConnection();
// Get statistics for Systems
$totalSystems = $db->query("SELECT COUNT(*) FROM project_category")->fetchColumn();
$activeSystems = $db->query("SELECT COUNT(*) FROM project_category WHERE status = 1")->fetchColumn();
$inactiveSystems = $totalSystems - $activeSystems;
?>

<div class="min-h-screen bg-transparent pb-12">
    <!-- Header Area -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                        <div class="p-2 bg-indigo-600 rounded-lg text-white shadow-lg shadow-indigo-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        System Operations Master
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Bulk manage system architectures, technology categories and project classifications</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="downloadTemplate()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download Template
                    </button>
                    <a href="../masters/?type=boq" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        Item Master
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto px-4 mt-8">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 flex items-center gap-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div>
                    <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Total Systems</div>
                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($totalSystems); ?></div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-6 flex items-center gap-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Active Systems</div>
                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($activeSystems); ?></div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-6 flex items-center gap-4 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Inactive / Archived</div>
                    <div class="text-2xl font-bold text-gray-900"><?php echo number_format($inactiveSystems); ?></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Guidelines -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-8">
                    <h3 class="font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                        System Sync Protocol
                    </h3>
                    <div class="space-y-4">
                        <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100">
                             <div class="text-xs font-bold text-indigo-700 uppercase mb-1">Row Key</div>
                             <p class="text-xs text-gray-600 leading-relaxed font-medium">Use <strong>System Name</strong> as the primary identifier. The system will auto-map to existing entries to prevent duplication.</p>
                        </div>
                        <div class="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                             <div class="text-xs font-bold text-emerald-700 uppercase mb-1">Status Mapping</div>
                             <p class="text-xs text-gray-600 leading-relaxed font-medium">Status column accepts 'Active' (Mapped to 1) or 'Inactive' (Mapped to 0).</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Processing Terminal -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-10">
                    <div id="drop-zone" class="relative group border-2 border-dashed border-gray-300 rounded-3xl p-20 text-center transition-all hover:border-indigo-500 hover:bg-indigo-50/20 cursor-pointer">
                        <input type="file" id="bulk-file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".xlsx,.xls,.csv">
                        <div class="w-24 h-24 bg-indigo-100/50 rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 group-hover:bg-indigo-100 transition-all duration-300">
                            <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </div>
                        <h3 class="text-2xl font-extrabold text-gray-900 mb-2 tracking-tight">Sync System Registry</h3>
                        <p class="text-gray-500 font-medium">Drop your system manifest here or click to choose from disk</p>
                    </div>

                    <div id="import-progress" class="hidden mt-10 p-6 bg-gray-50 rounded-2xl border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></div>
                                <span id="import-status" class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em]">Processing Ingestion</span>
                            </div>
                            <span id="import-percent" class="text-sm font-black text-gray-900">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                            <div id="import-bar" class="bg-indigo-600 h-full w-0 transition-all duration-500 ease-out"></div>
                        </div>
                    </div>
                </div>

                <!-- Results -->
                <div id="import-results" class="hidden bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h4 class="font-bold text-gray-900 flex items-center gap-2">
                             <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                             System Sync Log
                        </h4>
                        <div id="import-summary" class="flex gap-2"></div>
                    </div>
                    <div class="overflow-x-auto max-h-[500px]">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-white sticky top-0 z-10 shadow-sm border-b">
                                <tr>
                                    <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">No</th>
                                    <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">System Architecture</th>
                                    <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Action</th>
                                    <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Trace</th>
                                </tr>
                            </thead>
                            <tbody id="import-list-body" class="divide-y divide-gray-50"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('bulk-file').addEventListener('change', handleUpload);

async function handleUpload() {
    const file = document.getElementById('bulk-file').files[0];
    if (!file) return;

    const progressContainer = document.getElementById('import-progress');
    const resultsTable = document.getElementById('import-results');

    progressContainer.classList.remove('hidden');
    resultsTable.classList.add('hidden');
    
    updateProgress(20, 'Analyzing data structure...');

    const formData = new FormData();
    formData.append('file', file);

    try {
        const response = await fetch('api/process-bulk-systems.php', {
            method: 'POST',
            body: formData
        });
        updateProgress(80, 'Finalizing database batch...');
        const data = await response.json();
        
        if (data.success) {
            updateProgress(100, 'Process Complete');
            showResults(data);
        } else {
            Swal.fire('Failed', data.message || 'Processing error', 'error');
            progressContainer.classList.add('hidden');
        }
    } catch (err) {
        Swal.fire('Error', 'API connection lost', 'error');
        progressContainer.classList.add('hidden');
    }
}

function updateProgress(p, label) {
    document.getElementById('import-bar').style.width = p + '%';
    document.getElementById('import-percent').textContent = p + '%';
    document.getElementById('import-status').textContent = label;
}

function showResults(data) {
    const resultsContainer = document.getElementById('import-results');
    const listBody = document.getElementById('import-list-body');
    const summary = document.getElementById('import-summary');

    resultsContainer.classList.remove('hidden');
    document.getElementById('import-progress').classList.add('hidden');

    summary.innerHTML = `
        <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-black ring-1 ring-emerald-100 uppercase">New: ${data.created}</span>
        <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-[10px] font-black ring-1 ring-blue-100 uppercase">Updates: ${data.updated}</span>
        <span class="px-3 py-1 bg-rose-50 text-rose-700 rounded-full text-[10px] font-black ring-1 ring-rose-100 uppercase">Errors: ${data.failed}</span>
    `;

    listBody.innerHTML = data.rows.map((r, idx) => `
        <tr class="${r.status === 'failed' ? 'bg-rose-50/30' : 'hover:bg-gray-50/50 transition-colors'}">
            <td class="px-8 py-5 text-[10px] font-bold text-gray-400 text-center">${idx + 1}</td>
            <td class="px-8 py-5">
                <div class="text-sm font-bold text-gray-900">${r.name || 'System Name Missing'}</div>
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">${r.status_label || ''}</div>
            </td>
            <td class="px-8 py-5">
                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black tracking-widest uppercase ${
                    r.action === 'create' ? 'bg-emerald-100 text-emerald-700' : 
                    (r.action === 'update' ? 'bg-blue-100 text-blue-700' : 'bg-rose-100 text-rose-700')
                }">${r.action || r.status}</span>
            </td>
            <td class="px-8 py-5 text-[11px] font-medium text-gray-500 italic">${r.message}</td>
        </tr>
    `).join('');
}

function downloadTemplate() { 
    // Usually a CSV content via blob or link
    const headers = "System Name,Status (Active/Inactive)\nFire Alarm System,Active\nCCTV System,Active\nAccess Control System,Inactive";
    const blob = new Blob([headers], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', 'system_template.csv');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>

<?php
$content = ob_get_clean();
if (isset($_GET['ajax'])) {
    echo $content;
} else {
    require_once __DIR__ . '/../../includes/admin_layout.php';
}
?>

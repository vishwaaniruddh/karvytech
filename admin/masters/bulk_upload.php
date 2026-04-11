<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/compatibility.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$type = $_GET['type'] ?? 'cities';
$title = 'Bulk Upload ' . ucfirst($type);

// Supported master types configuration
$masterConfigs = [
    'cities' => [
        'name' => 'Cities',
        'template' => '../../assets/samples/cities_bulk_sample.csv',
        'back_url' => 'index.php?type=cities',
        'columns' => [
            'City Name (Required)',
            'Country (Required)',
            'State (Required)',
            'Status (active/inactive)'
        ]
    ]
];

$config = $masterConfigs[$type] ?? $masterConfigs['cities'];

ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Bulk Upload <?php echo htmlspecialchars($config['name']); ?></h1>
        <p class="mt-2 text-sm text-gray-700">Upload multiple records using a CSV file</p>
    </div>
    <div class="flex space-x-2">
        <a href="<?php echo htmlspecialchars($config['template']); ?>" class="btn btn-secondary" download="cities_bulk_template.csv">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            Download Template
        </a>
        <a href="<?php echo htmlspecialchars($config['back_url']); ?>" class="btn btn-secondary">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
            </svg>
            Back to List
        </a>
    </div>
</div>

<!-- Instructions Card -->
<div class="card mb-6 overflow-hidden border-0 shadow-sm rounded-xl bg-white">
    <div class="p-6">
        <div class="flex items-center mb-4">
            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Upload Instructions</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">File Requirements</h4>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            Supported format: CSV only
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            First row must contain headers
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            UTF-8 encoding recommended
                        </li>
                    </ul>
                </div>
            </div>
            
            <div>
                <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Column Order (Left to Right)</h4>
                <div class="space-y-2">
                    <?php foreach ($config['columns'] as $index => $col): ?>
                        <div class="flex items-center text-sm">
                            <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center mr-3 text-xs font-bold"><?php echo $index + 1; ?></span>
                            <span class="text-gray-700"><?php echo htmlspecialchars($col); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="mt-6 p-4 bg-amber-50 border border-amber-100 rounded-lg">
            <div class="flex">
                <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <div class="text-sm text-amber-800">
                    <p class="font-bold mb-1">Important Mapping Notice</p>
                    <p>Make sure <strong>Country</strong> and <strong>State</strong> names match exactly as they appear in the system. The system will automatically map the correct Zone based on the selected state.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Card -->
<div class="card overflow-hidden border-0 shadow-sm rounded-xl bg-white mb-6">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Upload File</h3>
        
        <form id="bulkUploadForm" class="space-y-6">
            <div class="relative group">
                <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-xl p-12 text-center hover:border-blue-400 hover:bg-blue-50 transition-all cursor-pointer group-hover:shadow-inner">
                    <input type="file" id="bulkFile" name="bulkFile" accept=".csv" class="hidden">
                    <div class="space-y-2">
                        <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </div>
                        <p id="fileName" class="text-gray-600 font-medium">Click to upload or drag and drop</p>
                        <p class="text-xs text-gray-400">CSV files only, max 5MB</p>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end pt-4">
                <button type="submit" id="uploadBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-bold shadow-md hover:shadow-lg transform transition-all hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed">
                    <div class="flex items-center">
                        <span id="btnText">Upload <?php echo htmlspecialchars($config['name']); ?></span>
                        <div id="btnLoader" class="hidden ml-3">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Results Section -->
<div id="resultsSection" class="hidden">
    <div class="card overflow-hidden border-0 shadow-sm rounded-xl bg-white mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Process Results</h3>
            
            <div id="resultSummary" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <!-- Summary cards populated by JS -->
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Row</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Detailed Reason</th>
                        </tr>
                    </thead>
                    <tbody id="resultsTableBody" class="bg-white divide-y divide-gray-200 text-sm">
                        <!-- Table rows populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('bulkFile');
    const fileNameDisplay = document.getElementById('fileName');
    const form = document.getElementById('bulkUploadForm');
    const uploadBtn = document.getElementById('uploadBtn');
    const btnText = document.getElementById('btnText');
    const btnLoader = document.getElementById('btnLoader');
    const resultsSection = document.getElementById('resultsSection');
    const resultSummary = document.getElementById('resultSummary');
    const resultsTableBody = document.getElementById('resultsTableBody');

    // Drop zone interactions
    dropZone.addEventListener('click', () => fileInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-blue-500', 'bg-blue-50');
    });

    ['dragleave', 'drop'].forEach(event => {
        dropZone.addEventListener(event, () => {
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleFileSelect();
        }
    });

    fileInput.addEventListener('change', handleFileSelect);

    function handleFileSelect() {
        if (fileInput.files.length) {
            fileNameDisplay.textContent = fileInput.files[0].name;
            fileNameDisplay.classList.add('text-blue-600', 'font-bold');
        }
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!fileInput.files.length) {
            showAlert('Please select a CSV file first', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('bulkFile', fileInput.files[0]);

        // Show loading state
        uploadBtn.disabled = true;
        btnText.textContent = 'Processing File...';
        btnLoader.classList.remove('hidden');
        resultsSection.classList.add('hidden');

        try {
            const response = await fetch(`../../api/masters.php?path=<?php echo $type; ?>/bulk-upload`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                displayResults(data.data);
                showAlert(data.message, 'success');
            } else {
                showAlert(data.message || 'Error processing file', 'error');
            }
        } catch (error) {
            console.error('Upload Error:', error);
            showAlert('Failed to connect to the server', 'error');
        } finally {
            uploadBtn.disabled = false;
            btnText.textContent = `Upload <?php echo htmlspecialchars($config['name']); ?>`;
            btnLoader.classList.add('hidden');
        }
    });

    function displayResults(data) {
        const rows = data.rows || [];
        const successCount = data.success_count || 0;
        const failCount = data.fail_count || 0;
        const totalCount = rows.length;

        // Populate Summary
        resultSummary.innerHTML = `
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase mb-1">Total Processed</p>
                <p class="text-2xl font-black text-gray-700">${totalCount}</p>
            </div>
            <div class="p-4 bg-green-50 rounded-xl border border-green-100">
                <p class="text-xs font-bold text-green-400 uppercase mb-1">Success</p>
                <p class="text-2xl font-black text-green-700">${successCount}</p>
            </div>
            <div class="p-4 bg-red-50 rounded-xl border border-red-100">
                <p class="text-xs font-bold text-red-400 uppercase mb-1">Failed</p>
                <p class="text-2xl font-black text-red-700">${failCount}</p>
            </div>
            <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                <p class="text-xs font-bold text-blue-400 uppercase mb-1">Accuracy</p>
                <p class="text-2xl font-black text-blue-700">${totalCount > 0 ? Math.round((successCount/totalCount)*100) : 0}%</p>
            </div>
        `;

        // Populate Table
        resultsTableBody.innerHTML = rows.map(row => `
            <tr class="${row.status === 'failed' ? 'bg-red-50/30' : ''}">
                <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-medium">${row.row}</td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-bold">${row.name || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-600">${row.state}, ${row.country}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase ${
                        row.status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                    }">
                        ${row.status}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-500 italic">${row.message || '-'}</td>
            </tr>
        `).join('');

        resultsSection.classList.remove('hidden');
        resultsSection.scrollIntoView({ behavior: 'smooth' });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>

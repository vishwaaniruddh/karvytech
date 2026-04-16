<!-- Create Site Modal -->
<div id="createSiteModal" class="modal">
    <div class="modal-content-large">
        <div class="modal-header-fixed">
            <h3 class="modal-title">Add New Site</h3>
            <button type="button" class="modal-close" onclick="closeModal('createSiteModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="createSiteForm" action="create.php" method="POST">
            <div class="modal-body-scrollable">
                <div class="form-section">
                    <h4 class="form-section-title">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="site_id" class="form-label">Site ID *</label>
                            <input type="text" id="site_id" name="site_id" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="store_id" class="form-label">Store ID</label>
                            <input type="text" id="store_id" name="store_id" class="form-input">
                        </div>
                        <div class="form-group md:col-span-2">
                            <label class="form-label">Location & Pincode *</label>
                            <div class="flex gap-2">
                                <input type="text" id="location" name="location" class="form-input w-[70%]" placeholder="Enter location address" required>
                                <input type="text" id="pincode" name="pincode" class="form-input w-[30%]" placeholder="Pincode" maxlength="6" pattern="[0-9]{6}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="form-section-title">Location Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div class="form-group">
                            <label for="country_id" class="form-label">Country *</label>
                            <select id="country_id" name="country_id" class="form-select" required onchange="loadStatesForSite(this.value)">
                                <option value="">Select Country</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="state_id" class="form-label">State *</label>
                            <select id="state_id" name="state_id" class="form-select" required onchange="loadCitiesForSite(this.value)">
                                <option value="">Select State</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="city_id" class="form-label">City *</label>
                            <select id="city_id" name="city_id" class="form-select" required>
                                <option value="">Select City</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="zone" class="form-label">Zone</label>
                            <input type="text" id="zone" name="zone" class="form-input" placeholder="e.g. West Zone">
                        </div>
                        <div class="form-group">
                            <label for="branch" class="form-label">Branch</label>
                            <input type="text" id="branch" name="branch" class="form-input">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="form-section-title">Client Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select id="customer_id" name="customer_id" class="form-select">
                                <option value="">Select Customer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bank_id" class="form-label">Bank</label>
                            <select id="bank_id" name="bank_id" class="form-select">
                                <option value="">Select Bank</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="form-section-title">Contact Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-group">
                            <label for="contact_person_name" class="form-label">Contact Person</label>
                            <input type="text" name="contact_person_name" id="contact_person_name" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="contact_person_number" class="form-label">Phone</label>
                            <input type="tel" name="contact_person_number" id="contact_person_number" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="contact_person_email" class="form-label">Email</label>
                            <input type="email" name="contact_person_email" id="contact_person_email" class="form-input">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer-fixed">
                <button type="button" onclick="closeModal('createSiteModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Site</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Site Modal -->
<div id="editSiteModal" class="modal">
    <div class="modal-content-large">
        <div class="modal-header-fixed">
            <h3 class="modal-title">Edit Site</h3>
            <button type="button" class="modal-close" onclick="closeModal('editSiteModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <form id="editSiteForm" method="POST">
            <input type="hidden" id="edit_id" name="id">
            <div class="modal-body-scrollable">
                <div class="form-section">
                    <h4 class="form-section-title">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Site ID *</label>
                            <input type="text" id="edit_site_id" name="site_id" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Store ID</label>
                            <input type="text" id="edit_store_id" name="store_id" class="form-input">
                        </div>
                        <div class="form-group md:col-span-2">
                            <label class="form-label">Location & Pincode *</label>
                            <div class="flex gap-2">
                                <input type="text" id="edit_location" name="location" class="form-input w-[70%]" required>
                                <input type="text" id="edit_pincode" name="pincode" class="form-input w-[30%]" maxlength="6">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="form-section-title">Location Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div class="form-group">
                            <label class="form-label">Country *</label>
                            <select id="edit_country_id" name="country_id" class="form-select" required onchange="loadStatesForSite(this.value, 'edit_state_id')">
                                <option value="">Select Country</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">State *</label>
                            <select id="edit_state_id" name="state_id" class="form-select" required onchange="loadCitiesForSite(this.value, 'edit_city_id')">
                                <option value="">Select State</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">City *</label>
                            <select id="edit_city_id" name="city_id" class="form-select" required>
                                <option value="">Select City</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Zone</label>
                            <input type="text" id="edit_zone" name="zone" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Branch</label>
                            <input type="text" id="edit_branch" name="branch" class="form-input">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="form-section-title">Status & Customer</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Customer</label>
                            <select id="edit_customer_id" name="customer_id" class="form-select">
                                <option value="">Select Customer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Activity Status</label>
                            <select id="edit_activity_status" name="activity_status" class="form-select">
                                <option value="Pending">Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                                <option value="On Hold">On Hold</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer-fixed">
                <button type="button" onclick="closeModal('editSiteModal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Site</button>
            </div>
        </form>
    </div>
</div>

<!-- View Site Modal -->
<div id="viewSiteModal" class="modal">
    <div class="modal-content-large">
        <div class="modal-header-fixed">
            <h3 class="modal-title text-blue-600">Site Information Details</h3>
            <button type="button" class="modal-close" onclick="closeModal('viewSiteModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body-scrollable bg-gray-50">
            <div id="viewModalContent" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-2">
                <!-- Content will be injected by JavaScript -->
                <div class="col-span-full py-20 text-center">
                    <div class="animate-spin rounded-full h-10 w-10 border-4 border-blue-500 border-t-transparent mx-auto"></div>
                    <p class="mt-4 text-gray-500 font-medium">Fetching site details...</p>
                </div>
            </div>
        </div>
        <div class="modal-footer-fixed">
            <button type="button" onclick="closeModal('viewSiteModal')" class="btn btn-secondary px-6">Close</button>
            <button type="button" id="editFromViewBtn" class="btn btn-primary px-6">Edit Site</button>
        </div>
    </div>
</div>

<!-- View BOQ Items Modal -->
<div id="viewBOQModal" class="modal">
    <div class="modal-content max-w-2xl rounded-2xl shadow-2xl">
        <div class="modal-header-fixed bg-blue-600 text-white rounded-t-2xl">
            <div class="flex flex-col">
                <h3 class="modal-title !text-white">Bill of Quantities (BOQ)</h3>
                <p id="boqRequestLabel" class="text-[10px] opacity-80 font-bold uppercase tracking-widest mt-0.5">---</p>
            </div>
            <button type="button" class="modal-close !text-white hover:text-blue-100" onclick="closeModal('viewBOQModal')">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body p-0">
            <div id="boqLoading" class="p-12 text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-2 text-sm text-gray-500">Loading requested materials...</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Material Name</th>
                            <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-right">Quantity</th>
                            <th class="px-6 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Unit</th>
                        </tr>
                    </thead>
                    <tbody id="boqItemsBody" class="divide-y divide-gray-50">
                        <!-- Items injected here -->
                    </tbody>
                </table>
            </div>
            <div id="boqEmpty" class="p-12 text-center hidden">
                <p class="text-sm text-gray-500">No items found in this request.</p>
            </div>
        </div>
        <div class="modal-footer-fixed border-t border-gray-50">
            <button type="button" onclick="closeModal('viewBOQModal')" class="btn btn-secondary px-8 font-bold text-xs uppercase letter-wide">Close</button>
        </div>
    </div>
</div>

<script>
async function loadCountriesForSite(targetSelectId = 'country_id') {
    try {
        const response = await fetch('../../api/masters.php?path=countries');
        const data = await response.json();
        if (data.success) {
            const select = document.getElementById(targetSelectId);
            if (select) {
                select.innerHTML = '<option value="">Select Country</option>';
                data.data.records.forEach(c => select.innerHTML += `<option value="${c.id}">${c.name}</option>`);
            }
        }
    } catch (e) { console.error(e); }
}

async function loadStatesForSite(countryId, targetSelectId = 'state_id') {
    if (!countryId) return;
    try {
        const response = await fetch(`../../api/states.php?action=getByCountry&country_id=${countryId}`);
        const data = await response.json();
        const select = document.getElementById(targetSelectId);
        if (select && data.success) {
            select.innerHTML = '<option value="">Select State</option>';
            data.data.forEach(s => select.innerHTML += `<option value="${s.id}">${s.name}</option>`);
        }
    } catch (e) { console.error(e); }
}

async function loadCitiesForSite(stateId, targetSelectId = 'city_id') {
    if (!stateId) return;
    try {
        const response = await fetch(`../../api/cities.php?action=getByState&state_id=${stateId}`);
        const data = await response.json();
        const select = document.getElementById(targetSelectId);
        if (select && data.success) {
            select.innerHTML = '<option value="">Select City</option>';
            data.data.forEach(c => select.innerHTML += `<option value="${c.id}">${c.name}</option>`);
        }
    } catch (e) { console.error(e); }
}

async function loadCustomersForSite(targetSelectId = 'customer_id') {
    try {
        const response = await fetch('../../api/masters.php?path=customers');
        const data = await response.json();
        if (data.success) {
            const select = document.getElementById(targetSelectId);
            if (select) {
                select.innerHTML = '<option value="">Select Customer</option>';
                data.data.records.forEach(c => select.innerHTML += `<option value="${c.id}">${c.name}</option>`);
            }
        }
    } catch (e) { console.error(e); }
}

async function loadBanksForSite(targetSelectId = 'bank_id') {
    try {
        const response = await fetch('../../api/masters.php?path=banks');
        const data = await response.json();
        if (data.success) {
            const select = document.getElementById(targetSelectId);
            if (select) {
                select.innerHTML = '<option value="">Select Bank</option>';
                data.data.records.forEach(b => select.innerHTML += `<option value="${b.id}">${b.name}</option>`);
            }
        }
    } catch (e) { console.error(e); }
}

// Global View function
async function viewSite(id) {
    if (typeof openModal === 'function') openModal('viewSiteModal');
    else document.getElementById('viewSiteModal').style.display = 'block';

    const container = document.getElementById('viewModalContent');
    container.innerHTML = `<div class="col-span-full py-20 text-center"><div class="animate-spin rounded-full h-10 w-10 border-4 border-blue-500 border-t-transparent mx-auto"></div><p class="mt-4 text-gray-500 font-medium">Fetching site details...</p></div>`;

    document.getElementById('editFromViewBtn').onclick = () => {
        closeModal('viewSiteModal');
        editSite(id);
    };

    try {
        const response = await fetch(`api/get-site-details.php?id=${id}`);
        const result = await response.json();
        if (result.success) {
            const s = result.data;
            const fields = [
                { label: 'Site ID', value: s.site_id, icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4' },
                { label: 'Site Ticket ID', value: s.site_ticket_id, icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z' },
                { label: 'Store ID', value: s.store_id || 'N/A', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
                { label: 'Location', value: s.location, icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z' },
                { label: 'City', value: s.city_name || s.city, icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-10V4m-5 4V4m8 8H7M5 10h14' },
                { label: 'State', value: s.state_name || s.state, icon: 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7l5-2 5.447 2.724A1 1 0 0120 8.618v10.764a1 1 0 01-1.447.894L14 18l-5 2z' },
                { label: 'Customer', value: s.customer_name || s.customer, icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
                { label: 'Contact Person', value: s.contact_person_name || 'N/A', icon: 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
                { label: 'Phone', value: s.contact_person_number || 'N/A', icon: 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z' },
                { label: 'Status', value: s.activity_status, icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
                { label: 'Bank', value: s.bank_name || 'N/A', icon: 'M3 10h18M7 15h1m4 0h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' }
            ];

            container.innerHTML = fields.map(f => `
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${f.icon}"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">${f.label}</span>
                    </div>
                    <div class="text-sm font-semibold text-gray-800 ml-11">${f.value}</div>
                </div>
            `).join('');
        } else {
            container.innerHTML = `<div class="col-span-full py-10 text-center text-red-500">${result.message}</div>`;
        }
    } catch (e) { container.innerHTML = `<div class="col-span-full py-10 text-center text-red-500">Error loading details</div>`; }
}

// Global Edit function
async function editSite(id) {
    if (typeof openModal === 'function') openModal('editSiteModal');
    else document.getElementById('editSiteModal').style.display = 'block';

    const form = document.getElementById('editSiteForm');
    form.reset();

    try {
        const response = await fetch(`api/get-site-details.php?id=${id}`);
        const result = await response.json();
        if (result.success) {
            const s = result.data;
            document.getElementById('edit_id').value = s.id;
            document.getElementById('edit_site_id').value = s.site_id;
            document.getElementById('edit_store_id').value = s.store_id;
            document.getElementById('edit_location').value = s.location;
            document.getElementById('edit_pincode').value = s.pincode;
            document.getElementById('edit_zone').value = s.zone;
            document.getElementById('edit_branch').value = s.branch;
            
            // Set simple dropdowns
            document.getElementById('edit_activity_status').value = s.activity_status;
            
            // Load and set dependent dropdowns
            await loadCountriesForSite('edit_country_id');
            document.getElementById('edit_country_id').value = s.country_id;
            
            await loadStatesForSite(s.country_id, 'edit_state_id');
            document.getElementById('edit_state_id').value = s.state_id;
            
            await loadCitiesForSite(s.state_id, 'edit_city_id');
            document.getElementById('edit_city_id').value = s.city_id;
            
            await loadCustomersForSite('edit_customer_id');
            document.getElementById('edit_customer_id').value = s.customer_id;
        }
    } catch (e) { console.error('Error in editSite:', e); }
}

document.addEventListener('DOMContentLoaded', () => {
    loadCountriesForSite();
    loadCustomersForSite();
    loadBanksForSite();
    
    // Create Site Form Submit
    const createForm = document.getElementById('createSiteForm');
    if (createForm) {
        createForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(createForm);
            try {
                const response = await fetch('create.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    Swal.fire('Success', 'Site created successfully', 'success');
                    closeModal('createSiteModal');
                    loadSites();
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) { Swal.fire('Error', 'Failed to create site', 'error'); }
        });
    }

    // Edit Site Form Submit
    const editForm = document.getElementById('editSiteForm');
    if (editForm) {
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(editForm);
            try {
                const response = await fetch('edit.php', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    Swal.fire('Success', 'Site updated successfully', 'success');
                    closeModal('editSiteModal');
                    loadSites();
                } else { Swal.fire('Error', result.message, 'error'); }
            } catch (error) { Swal.fire('Error', 'Failed to update site', 'error'); }
        });
    }
});
</script>

// Masters API JavaScript
class MastersAPI {
    constructor() {
        this.baseUrl = '/api/masters.php';
    }
    
    // Generic API call method
    async apiCall(url, options = {}) {
        
        // console.log('url  = '+ url)
        try {
            const response = await fetch('../../'+url, {
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                ...options
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API call failed:', error);
            return { success: false, message: 'Network error occurred' };
        }
    }
    
    // Get all records with pagination
    async getRecords(type, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = `${this.baseUrl}?path=${type}&${queryString}`;
        return await this.apiCall(url);
    }
    
    // Get single record
    async getRecord(type, id) {
        const url = `${this.baseUrl}?path=${type}/${id}`;
        return await this.apiCall(url);
    }
    
    // Create new record
    async createRecord(type, data) {
        const formData = new FormData();
        Object.keys(data).forEach(key => {
            formData.append(key, data[key]);
        });
        
        const url = `${this.baseUrl}?path=${type}`;
        return await this.apiCall(url, {
            method: 'POST',
            body: formData,
            headers: {} // Remove Content-Type to let browser set it for FormData
        });
    }
    
    // Update record
    async updateRecord(type, id, data) {
        const formData = new FormData();
        Object.keys(data).forEach(key => {
            formData.append(key, data[key]);
        });
        
        const url = `${this.baseUrl}?path=${type}/${id}`;
        return await this.apiCall(url, {
            method: 'POST',
            body: formData,
            headers: {} // Remove Content-Type to let browser set it for FormData
        });
    }
    
    // Delete record
    async deleteRecord(type, id) {
        const url = `${this.baseUrl}?path=${type}/${id}`;
        return await this.apiCall(url, {
            method: 'DELETE'
        });
    }
    
    // Toggle status
    async toggleStatus(type, id) {
        const url = `${this.baseUrl}?path=${type}/${id}/toggle-status`;
        return await this.apiCall(url, {
            method: 'POST'
        });
    }
    
    // Location-specific methods
    async getStatesByCountry(countryId) {
        const url = `/api/states.php?action=getByCountry&country_id=${countryId}`;
        return await this.apiCall(url);
    }
    
    async getCitiesByState(stateId) {
        const url = `/api/cities.php?action=getByState&state_id=${stateId}`;
        return await this.apiCall(url);
    }
}

// Global instance
const mastersAPI = new MastersAPI();

// Enhanced masters management functions
window.viewMaster = async function(id) {
    try {
        const data = await mastersAPI.getRecord(currentMasterType, id);
        if (data.success) {
            const record = data.data.record;
            let details = `${currentSingular} Details:\n\nName: ${record.name}\nStatus: ${record.status}\nCreated: ${formatDate(record.created_at)}`;
            if (record.updated_at) {
                details += `\nUpdated: ${formatDate(record.updated_at)}`;
            }
            
            // Add type-specific details
            if (currentMasterType === 'states' && record.country_name) {
                details += `\nCountry: ${record.country_name}`;
            }
            if (currentMasterType === 'cities' && record.state_name) {
                details += `\nState: ${record.state_name}`;
                if (record.country_name) {
                    details += `\nCountry: ${record.country_name}`;
                }
            }
            
            alert(details);
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Failed to load data', 'error');
    }
};

window.editMaster = async function(id) {
    try {
        const data = await mastersAPI.getRecord(currentMasterType, id);
        if (data.success) {
            const record = data.data.record;
            
            // For now, show a simple prompt - can be enhanced with a modal later
            const newName = prompt(`Enter new name for ${currentSingular}:`, record.name);
            if (newName && newName.trim() && newName.trim() !== record.name) {
                const updateData = {
                    name: newName.trim(),
                    status: record.status
                };
                
                // Add type-specific fields
                if (currentMasterType === 'states' && record.country_id) {
                    updateData.country_id = record.country_id;
                }
                if (currentMasterType === 'cities' && record.state_id) {
                    updateData.state_id = record.state_id;
                }
                
                const updateResult = await mastersAPI.updateRecord(currentMasterType, id, updateData);
                if (updateResult.success) {
                    showAlert(updateResult.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(updateResult.message, 'error');
                }
            }
        } else {
            showAlert(data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Failed to update', 'error');
    }
};

window.toggleMasterStatus = async function(id) {
    confirmAction(`Are you sure you want to change this ${currentSingular.toLowerCase()}'s status?`, async function() {
        try {
            const data = await mastersAPI.toggleStatus(currentMasterType, id);
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Failed to update status', 'error');
        }
    });
};

window.deleteMaster = async function(id) {
    confirmAction(`Are you sure you want to delete this ${currentSingular.toLowerCase()}? This action cannot be undone.`, async function() {
        try {
            const data = await mastersAPI.deleteRecord(currentMasterType, id);
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Failed to delete', 'error');
        }
    });
};

// Enhanced form submission
window.submitMasterForm = async function(formId, callback) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner mr-2"></span>Loading...';
    
    try {
        const result = await mastersAPI.createRecord(currentMasterType, data);
        if (result.success) {
            if (callback) callback(result);
            showAlert(result.message || 'Operation completed successfully', 'success');
        } else {
            showAlert(result.message || 'An error occurred', 'error');
            if (result.errors) {
                displayFormErrors(form, result.errors);
            }
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('An error occurred. Please try again.', 'error');
    } finally {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
};

// Enhanced cascading dropdowns using API
window.loadStates = async function(countryId, targetSelectId = 'state_id') {
    const stateSelect = document.getElementById(targetSelectId);
    const citySelect = document.getElementById('city_id');
    
    if (!stateSelect) return;
    
    // Clear existing options
    stateSelect.innerHTML = '<option value="">Loading...</option>';
    if (citySelect) {
        citySelect.innerHTML = '<option value="">Select City</option>';
    }
    
    if (!countryId) {
        stateSelect.innerHTML = '<option value="">Select State</option>';
        return;
    }
    
    try {
        const data = await mastersAPI.getStatesByCountry(countryId);
        if (data.success) {
            stateSelect.innerHTML = '<option value="">Select State</option>';
            data.data.forEach(state => {
                stateSelect.innerHTML += `<option value="${state.id}">${state.name}</option>`;
            });
        } else {
            stateSelect.innerHTML = '<option value="">Error loading states</option>';
            console.error('Error loading states:', data.error);
        }
    } catch (error) {
        stateSelect.innerHTML = '<option value="">Error loading states</option>';
        console.error('Error:', error);
    }
};

window.loadCities = async function(stateId, targetSelectId = 'city_id') {
    const citySelect = document.getElementById(targetSelectId);
    
    if (!citySelect) return;
    
    // Clear existing options
    citySelect.innerHTML = '<option value="">Loading...</option>';
    
    if (!stateId) {
        citySelect.innerHTML = '<option value="">Select City</option>';
        return;
    }
    
    try {
        const data = await mastersAPI.getCitiesByState(stateId);
        if (data.success) {
            citySelect.innerHTML = '<option value="">Select City</option>';
            data.data.forEach(city => {
                citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
            });
        } else {
            citySelect.innerHTML = '<option value="">Error loading cities</option>';
            console.error('Error loading cities:', data.error);
        }
    } catch (error) {
        citySelect.innerHTML = '<option value="">Error loading cities</option>';
        console.error('Error:', error);
    }
};
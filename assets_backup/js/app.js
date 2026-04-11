// Main application JavaScript

// Utility functions
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert-${type}`;
    alertDiv.textContent = message;
    
    const container = document.querySelector('.main-content') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Form validation
function validateForm(formId, rules) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    let isValid = true;
    const errors = {};
    
    Object.keys(rules).forEach(fieldName => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        const rule = rules[fieldName];
        
        if (!field) return;
        
        const value = field.value.trim();
        
        // Required validation
        if (rule.required && !value) {
            errors[fieldName] = `${fieldName} is required`;
            isValid = false;
            return;
        }
        
        // Length validation
        if (value && rule.minLength && value.length < rule.minLength) {
            errors[fieldName] = `${fieldName} must be at least ${rule.minLength} characters`;
            isValid = false;
        }
        
        if (value && rule.maxLength && value.length > rule.maxLength) {
            errors[fieldName] = `${fieldName} must not exceed ${rule.maxLength} characters`;
            isValid = false;
        }
        
        // Email validation
        if (value && rule.email && !isValidEmail(value)) {
            errors[fieldName] = `${fieldName} must be a valid email address`;
            isValid = false;
        }
    });
    
    // Display errors
    displayFormErrors(form, errors);
    
    return isValid;
}

function displayFormErrors(form, errors) {
    // Clear previous errors
    form.querySelectorAll('.error-message').forEach(el => el.remove());
    form.querySelectorAll('.border-red-500').forEach(el => {
        el.classList.remove('border-red-500');
        el.classList.add('border-gray-300');
    });
    
    // Display new errors
    Object.keys(errors).forEach(fieldName => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.classList.remove('border-gray-300');
            field.classList.add('border-red-500');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message text-red-500 text-sm mt-1';
            errorDiv.textContent = errors[fieldName];
            
            field.parentNode.appendChild(errorDiv);
        }
    });
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// AJAX helper functions
function makeRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    const config = { ...defaultOptions, ...options };
    
    return fetch(url, config)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('Request failed:', error);
            showAlert('An error occurred. Please try again.', 'error');
            throw error;
        });
}

// File upload helper
function handleFileUpload(inputElement, allowedTypes = [], maxSize = 5 * 1024 * 1024) {
    const file = inputElement.files[0];
    if (!file) return true;
    
    // Check file type
    if (allowedTypes.length > 0) {
        const fileExtension = file.name.split('.').pop().toLowerCase();
        if (!allowedTypes.includes(fileExtension)) {
            showAlert(`File type not allowed. Allowed types: ${allowedTypes.join(', ')}`, 'error');
            inputElement.value = '';
            return false;
        }
    }
    
    // Check file size
    if (file.size > maxSize) {
        const maxSizeMB = Math.round(maxSize / (1024 * 1024));
        showAlert(`File size too large. Maximum size: ${maxSizeMB}MB`, 'error');
        inputElement.value = '';
        return false;
    }
    
    return true;
}

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('[class*="alert-"]').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    
    // Add confirmation to delete buttons
    document.querySelectorAll('[data-confirm]').forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // File upload validation
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            const allowedTypes = this.getAttribute('data-allowed-types');
            const maxSize = this.getAttribute('data-max-size');
            
            const types = allowedTypes ? allowedTypes.split(',') : [];
            const size = maxSize ? parseInt(maxSize) : 5 * 1024 * 1024;
            
            handleFileUpload(this, types, size);
        });
    });
});
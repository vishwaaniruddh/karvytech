// Admin Panel JavaScript
console.log('Admin JS loaded');

document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin.js DOM loaded');
    // Sidebar toggle functionality - Updated to work with admin and vendor layouts
    const toggleSidebar = document.getElementById('toggleSidebar');
    const sidebar = document.querySelector('.admin-sidebar') || document.querySelector('.vendor-sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    
    console.log('Admin.js elements found:', {
        toggleSidebar: !!toggleSidebar,
        sidebar: !!sidebar,
        sidebarOverlay: !!sidebarOverlay
    });
    
    if (toggleSidebar && sidebar) {
        console.log('Adding click listener to hamburger menu');
        toggleSidebar.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Hamburger menu clicked in admin.js');
            
            const isShown = sidebar.classList.contains('show');
            console.log('Sidebar currently shown:', isShown);
            
            if (isShown) {
                // Close sidebar
                sidebar.classList.remove('show');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.add('hidden');
                }
                console.log('Closing sidebar');
            } else {
                // Open sidebar
                sidebar.classList.add('show');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('hidden');
                }
                console.log('Opening sidebar');
            }
        });
    } else {
        console.error('Hamburger menu or sidebar not found in admin.js');
    }
    
    // Close sidebar when clicking overlay
    if (sidebarOverlay && sidebar) {
        sidebarOverlay.addEventListener('click', function(e) {
            console.log('Overlay clicked - closing sidebar');
            sidebar.classList.remove('show');
            sidebarOverlay.classList.add('hidden');
        });
        
        // Prevent sidebar content clicks from closing the sidebar
        sidebar.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // Close sidebar on window resize if mobile
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) { // lg breakpoint
            if (sidebar) sidebar.classList.remove('show');
            if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
        } else {
            if (sidebar) sidebar.classList.remove('show');
            if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
        }
    });

    // Handle mobile navigation - close sidebar after clicking menu links
    function handleMobileNavigation() {
        // Only apply on mobile devices
        if (window.innerWidth >= 1024) return;
        
        // Close sidebar on mobile after navigation
        if (sidebar && sidebarOverlay) {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.add('hidden');
        }
    }

    // Add click handlers to all navigation links for mobile
    const navLinks = document.querySelectorAll('.sidebar-item[href], .sidebar-subitem[href]');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Don't prevent default navigation, just close sidebar on mobile
            setTimeout(handleMobileNavigation, 100);
        });
    });

    // Prevent submenu toggle buttons from closing sidebar
    const submenuToggles = document.querySelectorAll('.sidebar-item:not([href])');
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            // Don't close sidebar for submenu toggles
        });
    });
    
    // User dropdown functionality
    const userMenuButton = document.getElementById('user-menu-button');
    const userDropdown = document.getElementById('user-dropdown');
    
    if (userMenuButton && userDropdown) {
        userMenuButton.addEventListener('click', function() {
            userDropdown.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!userMenuButton.contains(event.target) && !userDropdown.contains(event.target)) {
                userDropdown.classList.add('hidden');
            }
        });
    }
    
    // Modal functionality
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    };
    
    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    };
    
    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            const modalId = event.target.id;
            closeModal(modalId);
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                closeModal(openModal.id);
            }
        }
    });
    
    // Form submission with AJAX
    window.submitForm = function(formId, callback) {
        const form = document.getElementById(formId);
        if (!form) return;
        
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner mr-2"></span>Loading...';
        
        fetch(form.action, {
            method: form.method,
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (callback) callback(data);
                showAlert(data.message || 'Operation completed successfully', 'success');
            } else {
                showAlert(data.message || 'An error occurred', 'error');
                if (data.errors) {
                    displayFormErrors(form, data.errors);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'error');
        })
        .finally(() => {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    };
    
    // Display form errors
    function displayFormErrors(form, errors) {
        // Clear previous errors
        form.querySelectorAll('.form-error').forEach(el => el.remove());
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
                errorDiv.className = 'form-error';
                errorDiv.textContent = errors[fieldName];
                
                field.parentNode.appendChild(errorDiv);
            }
        });
    }
    
    // Show alert messages
    window.showAlert = function(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} fixed top-20 right-4 z-50 max-w-sm`;
        alertDiv.innerHTML = `
            <div class="flex items-center">
                <span class="flex-1">${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-current opacity-70 hover:opacity-100">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    };
    
    // Confirmation dialog
    window.confirmAction = function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    };
    
    // Data table search functionality
    window.searchTable = function(inputId, tableId) {
        const input = document.getElementById(inputId);
        const table = document.getElementById(tableId);
        
        if (!input || !table) return;
        
        input.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
                
                rows[i].style.display = found ? '' : 'none';
            }
        });
    };
    
    // Auto-hide alerts
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    });
});

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR'
    }).format(amount);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}    

// Location cascading dropdowns
    window.loadStates = function(countryId, targetSelectId = 'state_id') {
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
        
        fetch(`/api/states.php?action=getByCountry&country_id=${countryId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    stateSelect.innerHTML = '<option value="">Select State</option>';
                    data.data.forEach(state => {
                        stateSelect.innerHTML += `<option value="${state.id}">${state.name}</option>`;
                    });
                } else {
                    stateSelect.innerHTML = '<option value="">Error loading states</option>';
                    console.error('Error loading states:', data.error);
                }
            })
            .catch(error => {
                stateSelect.innerHTML = '<option value="">Error loading states</option>';
                console.error('Error:', error);
            });
    };
    
    window.loadCities = function(stateId, targetSelectId = 'city_id') {
        const citySelect = document.getElementById(targetSelectId);
        
        if (!citySelect) return;
        
        // Clear existing options
        citySelect.innerHTML = '<option value="">Loading...</option>';
        
        if (!stateId) {
            citySelect.innerHTML = '<option value="">Select City</option>';
            return;
        }
        
        fetch(`/api/cities.php?action=getByState&state_id=${stateId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    citySelect.innerHTML = '<option value="">Select City</option>';
                    data.data.forEach(city => {
                        citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                    });
                } else {
                    citySelect.innerHTML = '<option value="">Error loading cities</option>';
                    console.error('Error loading cities:', data.error);
                }
            })
            .catch(error => {
                citySelect.innerHTML = '<option value="">Error loading cities</option>';
                console.error('Error:', error);
            });
    };
    
    // Auto-setup cascading dropdowns
    const countrySelects = document.querySelectorAll('select[name="country_id"], #country_id');
    countrySelects.forEach(select => {
        select.addEventListener('change', function() {
            loadStates(this.value);
        });
    });
    
    const stateSelects = document.querySelectorAll('select[name="state_id"], #state_id');
    stateSelects.forEach(select => {
        select.addEventListener('change', function() {
            loadCities(this.value);
        });
    });
// Supplier Dropdown Utility Functions
class SupplierDropdown {
    constructor(options = {}) {
        this.apiEndpoint = options.apiEndpoint || 'supplier_api.php';
        this.suppliers = [];
        this.searchTimeout = null;
    }

    // Initialize supplier dropdown for a specific form
    async initializeDropdown(config) {
        const {
            nameFieldId,
            companyFieldId = null,
            phoneFieldId = null,
            emailFieldId = null,
            addressFieldId = null,
            gstFieldId = null,
            dropdownContainerId = null
        } = config;

        const nameField = document.getElementById(nameFieldId);
        if (!nameField) {
            console.error('Name field not found:', nameFieldId);
            return;
        }

        // Load all suppliers
        await this.loadSuppliers();

        // Create dropdown container if not provided
        let dropdownContainer;
        if (dropdownContainerId) {
            dropdownContainer = document.getElementById(dropdownContainerId);
        } else {
            dropdownContainer = this.createDropdownContainer(nameField);
        }

        // Setup event listeners
        this.setupEventListeners(nameField, dropdownContainer, config);
    }

    // Load suppliers from API
    async loadSuppliers() {
        try {
            const response = await fetch(`${this.apiEndpoint}?action=get_all`);
            const data = await response.json();

            if (data.success) {
                this.suppliers = data.suppliers;
            } else {
                console.error('Failed to load suppliers:', data.message);
            }
        } catch (error) {
            console.error('Error loading suppliers:', error);
        }
    }

    // Create dropdown container
    createDropdownContainer(nameField) {
        const container = document.createElement('div');
        container.className = 'supplier-dropdown-container';
        container.style.cssText = `
            position: relative;
            width: 100%;
        `;

        const dropdown = document.createElement('div');
        dropdown.className = 'supplier-dropdown';
        dropdown.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ccc;
            border-top: none;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        `;

        container.appendChild(dropdown);
        nameField.parentNode.insertBefore(container, nameField);
        container.insertBefore(nameField, dropdown);

        return dropdown;
    }

    // Setup event listeners
    setupEventListeners(nameField, dropdownContainer, config) {
        // Input event for search
        nameField.addEventListener('input', (e) => {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.showDropdown(nameField, dropdownContainer, config, e.target.value);
            }, 300);
        });

        // Focus event
        nameField.addEventListener('focus', () => {
            this.showDropdown(nameField, dropdownContainer, config, nameField.value);
        });

        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!nameField.contains(e.target) && !dropdownContainer.contains(e.target)) {
                this.hideDropdown(dropdownContainer);
            }
        });
    }

    // Show dropdown with filtered suppliers
    showDropdown(nameField, dropdownContainer, config, searchTerm = '') {
        const filteredSuppliers = this.filterSuppliers(searchTerm);

        if (filteredSuppliers.length === 0) {
            this.hideDropdown(dropdownContainer);
            return;
        }

        dropdownContainer.innerHTML = '';

        filteredSuppliers.forEach(supplier => {
            const item = document.createElement('div');
            item.className = 'supplier-dropdown-item';
            item.style.cssText = `
                padding: 10px;
                cursor: pointer;
                border-bottom: 1px solid #eee;
                background: white;
            `;

            // Create display text
            let displayText = supplier.supplier_name;
            if (supplier.supplier_company) {
                displayText += ` (${supplier.supplier_company})`;
            }
            displayText += ` - ${supplier.phone_no}`;

            item.innerHTML = `
                <div style="font-weight: bold;">${supplier.supplier_name}</div>
                <div style="font-size: 0.9em; color: #666;">
                    ${supplier.supplier_company ? supplier.supplier_company + ' - ' : ''}${supplier.phone_no}
                </div>
            `;

            // Hover effects
            item.addEventListener('mouseenter', () => {
                item.style.backgroundColor = '#f0f0f0';
            });

            item.addEventListener('mouseleave', () => {
                item.style.backgroundColor = 'white';
            });

            // Click to select
            item.addEventListener('click', () => {
                this.selectSupplier(supplier, config);
                this.hideDropdown(dropdownContainer);
            });

            dropdownContainer.appendChild(item);
        });

        dropdownContainer.style.display = 'block';
    }

    // Hide dropdown
    hideDropdown(dropdownContainer) {
        dropdownContainer.style.display = 'none';
    }

    // Filter suppliers based on search term
    filterSuppliers(searchTerm) {
        if (!searchTerm.trim()) {
            return this.suppliers.slice(0, 10); // Show first 10 if no search term
        }

        const term = searchTerm.toLowerCase();
        return this.suppliers.filter(supplier => {
            return supplier.supplier_name.toLowerCase().includes(term) ||
                (supplier.supplier_company && supplier.supplier_company.toLowerCase().includes(term)) ||
                supplier.phone_no.includes(term);
        }).slice(0, 10);
    }

    // Select supplier and fill form fields
    selectSupplier(supplier, config) {
        // Fill name field
        const nameField = document.getElementById(config.nameFieldId);
        if (nameField) nameField.value = supplier.supplier_name;

        // Fill company field if exists
        if (config.companyFieldId) {
            const companyField = document.getElementById(config.companyFieldId);
            if (companyField) companyField.value = supplier.supplier_company || '';
        }

        // Fill phone field if exists
        if (config.phoneFieldId) {
            const phoneField = document.getElementById(config.phoneFieldId);
            if (phoneField) phoneField.value = supplier.phone_no || '';
        }

        // Fill email field if exists
        if (config.emailFieldId) {
            const emailField = document.getElementById(config.emailFieldId);
            if (emailField) emailField.value = supplier.email || '';
        }

        // Fill address field if exists
        if (config.addressFieldId) {
            const addressField = document.getElementById(config.addressFieldId);
            if (addressField) addressField.value = supplier.address || '';
        }

        // Fill GST field if exists
        if (config.gstFieldId) {
            const gstField = document.getElementById(config.gstFieldId);
            if (gstField) gstField.value = supplier.gst_id || '';
        }

        // Trigger change events
        [config.nameFieldId, config.companyFieldId, config.phoneFieldId, config.emailFieldId, config.addressFieldId, config.gstFieldId]
        .filter(Boolean)
            .forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
    }

    // Get supplier by ID
    async getSupplierById(id) {
        try {
            const response = await fetch(`${this.apiEndpoint}?action=get_by_id&id=${id}`);
            const data = await response.json();

            if (data.success) {
                return data.supplier;
            } else {
                console.error('Supplier not found:', data.message);
                return null;
            }
        } catch (error) {
            console.error('Error getting supplier:', error);
            return null;
        }
    }

    // Search suppliers
    async searchSuppliers(term) {
        try {
            const response = await fetch(`${this.apiEndpoint}?action=search&term=${encodeURIComponent(term)}`);
            const data = await response.json();

            if (data.success) {
                return data.suppliers;
            } else {
                console.error('Search failed:', data.message);
                return [];
            }
        } catch (error) {
            console.error('Error searching suppliers:', error);
            return [];
        }
    }
}

// Global instance
window.supplierDropdown = new SupplierDropdown();
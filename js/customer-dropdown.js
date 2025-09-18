// Customer Dropdown Utility Functions
class CustomerDropdown {
    constructor(options = {}) {
        this.apiEndpoint = options.apiEndpoint || 'customer_api.php';
        this.customers = [];
        this.searchTimeout = null;
    }

    // Initialize customer dropdown for a specific form
    async initializeDropdown(config) {
        const {
            nameFieldId,
            companyFieldId = null,
            phoneFieldId = null,
            emailFieldId = null,
            addressFieldId = null,
            dropdownContainerId = null
        } = config;

        const nameField = document.getElementById(nameFieldId);
        if (!nameField) {
            console.error('Name field not found:', nameFieldId);
            return;
        }

        // Load all customers
        await this.loadCustomers();

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

    // Load customers from API
    async loadCustomers() {
        try {
            const response = await fetch(`${this.apiEndpoint}?action=get_all`);
            const data = await response.json();

            if (data.success) {
                this.customers = data.customers;
            } else {
                console.error('Failed to load customers:', data.message);
            }
        } catch (error) {
            console.error('Error loading customers:', error);
        }
    }

    // Create dropdown container
    createDropdownContainer(nameField) {
        const container = document.createElement('div');
        container.className = 'customer-dropdown-container';
        container.style.cssText = `
            position: relative;
            width: 100%;
        `;

        const dropdown = document.createElement('div');
        dropdown.className = 'customer-dropdown';
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

    // Show dropdown with filtered customers
    showDropdown(nameField, dropdownContainer, config, searchTerm = '') {
        const filteredCustomers = this.filterCustomers(searchTerm);

        if (filteredCustomers.length === 0) {
            this.hideDropdown(dropdownContainer);
            return;
        }

        dropdownContainer.innerHTML = '';

        filteredCustomers.forEach(customer => {
            const item = document.createElement('div');
            item.className = 'customer-dropdown-item';
            item.style.cssText = `
                padding: 10px;
                cursor: pointer;
                border-bottom: 1px solid #eee;
                background: white;
            `;

            // Create display text
            let displayText = customer.customer_name;
            if (customer.customer_company) {
                displayText += ` (${customer.customer_company})`;
            }
            displayText += ` - ${customer.phone_no}`;

            item.innerHTML = `
                <div style="font-weight: bold;">${customer.customer_name}</div>
                <div style="font-size: 0.9em; color: #666;">
                    ${customer.customer_company ? customer.customer_company + ' - ' : ''}${customer.phone_no}
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
                this.selectCustomer(customer, config);
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

    // Filter customers based on search term
    filterCustomers(searchTerm) {
        if (!searchTerm.trim()) {
            return this.customers.slice(0, 10); // Show first 10 if no search term
        }

        const term = searchTerm.toLowerCase();
        return this.customers.filter(customer => {
            return customer.customer_name.toLowerCase().includes(term) ||
                (customer.customer_company && customer.customer_company.toLowerCase().includes(term)) ||
                customer.phone_no.includes(term);
        }).slice(0, 10);
    }

    // Select customer and fill form fields
    selectCustomer(customer, config) {
        // Fill name field
        const nameField = document.getElementById(config.nameFieldId);
        if (nameField) nameField.value = customer.customer_name;

        // Fill company field if exists
        if (config.companyFieldId) {
            const companyField = document.getElementById(config.companyFieldId);
            if (companyField) companyField.value = customer.customer_company || '';
        }

        // Fill phone field if exists
        if (config.phoneFieldId) {
            const phoneField = document.getElementById(config.phoneFieldId);
            if (phoneField) phoneField.value = customer.phone_no || '';
        }

        // Fill email field if exists
        if (config.emailFieldId) {
            const emailField = document.getElementById(config.emailFieldId);
            if (emailField) emailField.value = customer.email || '';
        }

        // Fill address field if exists
        if (config.addressFieldId) {
            const addressField = document.getElementById(config.addressFieldId);
            if (addressField) addressField.value = customer.address || '';
        }

        // Trigger change events
        [config.nameFieldId, config.companyFieldId, config.phoneFieldId, config.emailFieldId, config.addressFieldId]
        .filter(Boolean)
            .forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
    }

    // Get customer by ID
    async getCustomerById(id) {
        try {
            const response = await fetch(`${this.apiEndpoint}?action=get_by_id&id=${id}`);
            const data = await response.json();

            if (data.success) {
                return data.customer;
            } else {
                console.error('Customer not found:', data.message);
                return null;
            }
        } catch (error) {
            console.error('Error getting customer:', error);
            return null;
        }
    }

    // Search customers
    async searchCustomers(term) {
        try {
            const response = await fetch(`${this.apiEndpoint}?action=search&term=${encodeURIComponent(term)}`);
            const data = await response.json();

            if (data.success) {
                return data.customers;
            } else {
                console.error('Search failed:', data.message);
                return [];
            }
        } catch (error) {
            console.error('Error searching customers:', error);
            return [];
        }
    }
}

// Global instance
window.customerDropdown = new CustomerDropdown();
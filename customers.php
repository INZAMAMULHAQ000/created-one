<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

// Handle AJAX requests for CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $response = ['success' => false];
    if ($_POST['action'] === 'add') {
        $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
        $customer_company = $_POST['customer_company'] ? mysqli_real_escape_string($conn, $_POST['customer_company']) : NULL;
        $phone_no = mysqli_real_escape_string($conn, $_POST['phone_no']);
        $email = $_POST['email'] ? mysqli_real_escape_string($conn, $_POST['email']) : NULL;
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        
        $company_part = $customer_company ? "'$customer_company'" : 'NULL';
        $email_part = $email ? "'$email'" : 'NULL';
        
        $sql = "INSERT INTO customers (customer_name, customer_company, phone_no, email, address) VALUES ('$customer_name', $company_part, '$phone_no', $email_part, '$address')";
        $response['success'] = mysqli_query($conn, $sql);
        $response['id'] = mysqli_insert_id($conn);
        if (!$response['success']) {
            $response['error'] = mysqli_error($conn);
        }
    } elseif ($_POST['action'] === 'update') {
        $id = intval($_POST['id']);
        $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
        $customer_company = $_POST['customer_company'] ? mysqli_real_escape_string($conn, $_POST['customer_company']) : NULL;
        $phone_no = mysqli_real_escape_string($conn, $_POST['phone_no']);
        $email = $_POST['email'] ? mysqli_real_escape_string($conn, $_POST['email']) : NULL;
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        
        $company_part = $customer_company ? "'$customer_company'" : 'NULL';
        $email_part = $email ? "'$email'" : 'NULL';
        
        $sql = "UPDATE customers SET customer_name='$customer_name', customer_company=$company_part, phone_no='$phone_no', email=$email_part, address='$address' WHERE id=$id";
        $response['success'] = mysqli_query($conn, $sql);
        if (!$response['success']) {
            $response['error'] = mysqli_error($conn);
        }
    } elseif ($_POST['action'] === 'delete') {
        $id = intval($_POST['id']);
        $sql = "DELETE FROM customers WHERE id=$id";
        $response['success'] = mysqli_query($conn, $sql);
        if (!$response['success']) {
            $response['error'] = mysqli_error($conn);
        }
    } elseif ($_POST['action'] === 'fetch') {
        $customers = [];
        $result = mysqli_query($conn, "SELECT * FROM customers ORDER BY customer_name");
        while ($row = mysqli_fetch_assoc($result)) {
            $customers[] = $row;
        }
        $response['success'] = true;
        $response['customers'] = $customers;
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="includes/sidebar.css" rel="stylesheet">
    <style>
        :root {
            --background-color: #f8f9fa;
            --text-color: #212529;
            --accent-color: #007bff;
            --form-bg: #ffffff;
            --form-border: #dee2e6;
            --form-focus-bg: #ffffff;
            --form-focus-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
            --btn-text-shadow: none;
            --btn-hover-bg: var(--accent-color);
            --btn-hover-color: #fff;
            --btn-hover-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
            --table-bg: #ffffff;
            --table-border: #dee2e6;
        }

        body.light-theme {
            --background-color: #f0f2f5;
            --text-color: #333;
            --accent-color: #007bff;
            --form-bg: rgba(255, 255, 255, 0.8);
            --form-border: #007bff;
            --form-focus-bg: rgba(255, 255, 255, 0.9);
            --form-focus-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
            --btn-text-shadow: none;
            --btn-hover-bg: var(--accent-color);
            --btn-hover-color: #fff;
            --btn-hover-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
            --table-bg: rgba(255,255,255,0.9);
            --table-border: #ccc;
        }

        body.dark-theme {
            --background-color: #212529;
            --text-color: #e2e6ea;
            --accent-color: #66b3ff;
            --form-bg: #495057;
            --form-border: #6c757d;
            --form-focus-bg: #495057;
            --form-focus-shadow: 0 0 0 0.25rem rgba(102, 179, 255, 0.25);
            --btn-hover-bg: var(--accent-color);
            --btn-hover-color: #fff;
            --btn-hover-shadow: 0 0 0 0.25rem rgba(102, 179, 255, 0.25);
            --table-bg: #495057;
            --table-border: #6c757d;
        }

        body {
            background: var(--background-color);
            color: var(--text-color);
            min-height: 100vh;
            transition: background 0.3s ease, color 0.3s ease;
        }
        .container {
            padding: 2rem;
        }
        .customer-form, .customer-table {
            background: var(--form-bg);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            transition: background 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 2rem;
        }
        .main-text {
            color: var(--text-color);
            transition: color 0.3s ease;
        }
        .form-control, .form-select {
            background: var(--form-bg);
            border: 1px solid var(--form-border);
            color: var(--text-color);
            transition: background 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            background: var(--form-focus-bg);
            border-color: var(--accent-color);
            box-shadow: var(--form-focus-shadow);
            color: var(--text-color);
        }
        .btn-accent {
            background: var(--accent-color);
            border: 1px solid var(--accent-color);
            color: #fff;
            transition: all 0.3s ease;
        }
        .btn-accent:hover {
            background: var(--btn-hover-bg);
            color: var(--btn-hover-color);
            box-shadow: var(--btn-hover-shadow);
        }
        .nav-link {
            color: var(--text-color);
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: var(--accent-color);
        }
        .table {
            color: var(--text-color);
        }
        .table th, .table td {
            background: var(--table-bg);
            border-color: var(--table-border);
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        .navbar-dark .navbar-nav .nav-link {
            color: var(--text-color) !important;
        }

        .navbar-dark .navbar-brand {
            color: var(--text-color) !important;
        }

        .navbar-dark .navbar-toggler-icon {
            filter: invert(var(--navbar-toggler-invert));
        }

        body.dark-theme .navbar-toggler-icon {
            --navbar-toggler-invert: 1;
        }

        body.light-theme .navbar-toggler-icon {
            --navbar-toggler-invert: 0;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .alert {
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.375rem;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body class="dark-theme"> <!-- Default to dark theme -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="customer-form">
            <h2 class="text-center mb-4 main-text">Customer Master</h2>
            
            <div id="alertContainer"></div>
            
            <form id="customerForm">
                <input type="hidden" id="customerId" name="id">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="customerName" class="form-label main-text">Customer Name *</label>
                        <input type="text" class="form-control" id="customerName" name="customer_name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="customerCompany" class="form-label main-text">Company Name (Optional)</label>
                        <input type="text" class="form-control" id="customerCompany" name="customer_company">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phoneNo" class="form-label main-text">Phone Number *</label>
                        <input type="text" class="form-control" id="phoneNo" name="phone_no" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label main-text">Email (Optional)</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="address" class="form-label main-text">Address *</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-accent me-2" id="submitBtn">Add Customer</button>
                    <button type="button" class="btn btn-secondary" id="cancelBtn" style="display: none;">Cancel</button>
                </div>
            </form>
        </div>

        <div class="customer-table">
            <h3 class="text-center mb-4 main-text">Customer List</h3>
            <div class="table-responsive">
                <table class="table table-striped" id="customersTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="customersTableBody">
                        <!-- Data will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="includes/sidebar.js"></script>
    <script>
        let editingCustomerId = null;

        // Use jQuery document ready for proper initialization
        $(document).ready(function() {
            loadCustomers();
            
            // Initialize sidebar
            initializeSidebar();
        });

        // Handle form submission
        document.getElementById('customerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const action = editingCustomerId ? 'update' : 'add';
            formData.append('action', action);
            
            if (editingCustomerId) {
                formData.append('id', editingCustomerId);
            }

            fetch('customers.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Customer ' + (action === 'add' ? 'added' : 'updated') + ' successfully!', 'success');
                    resetForm();
                    loadCustomers();
                } else {
                    showAlert('Error: ' + (data.error || 'Unknown error occurred'), 'danger');
                }
            })
            .catch(error => {
                showAlert('Error: ' + error.message, 'danger');
            });
        });

        // Load customers from server
        function loadCustomers() {
            fetch('customers.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=fetch'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateTable(data.customers);
                }
            });
        }

        // Populate table with customer data
        function populateTable(customers) {
            const tbody = document.getElementById('customersTableBody');
            tbody.innerHTML = '';
            
            customers.forEach(customer => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${customer.customer_name}</td>
                    <td>${customer.customer_company || '-'}</td>
                    <td>${customer.phone_no}</td>
                    <td>${customer.email || '-'}</td>
                    <td>${customer.address.length > 50 ? customer.address.substring(0, 50) + '...' : customer.address}</td>
                    <td>
                        <button class="btn btn-accent btn-sm" onclick="editCustomer(${customer.id})">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteCustomer(${customer.id})">Delete</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Edit customer
        function editCustomer(id) {
            fetch('customers.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=fetch'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const customer = data.customers.find(c => c.id == id);
                    if (customer) {
                        document.getElementById('customerId').value = customer.id;
                        document.getElementById('customerName').value = customer.customer_name;
                        document.getElementById('customerCompany').value = customer.customer_company || '';
                        document.getElementById('phoneNo').value = customer.phone_no;
                        document.getElementById('email').value = customer.email || '';
                        document.getElementById('address').value = customer.address;
                        
                        editingCustomerId = id;
                        document.getElementById('submitBtn').textContent = 'Update Customer';
                        document.getElementById('cancelBtn').style.display = 'inline-block';
                        
                        // Scroll to form
                        document.querySelector('.customer-form').scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        }

        // Delete customer
        function deleteCustomer(id) {
            if (confirm('Are you sure you want to delete this customer?')) {
                fetch('customers.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=delete&id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Customer deleted successfully!', 'success');
                        loadCustomers();
                    } else {
                        showAlert('Error: ' + (data.error || 'Unknown error occurred'), 'danger');
                    }
                });
            }
        }

        // Reset form
        function resetForm() {
            document.getElementById('customerForm').reset();
            document.getElementById('customerId').value = '';
            editingCustomerId = null;
            document.getElementById('submitBtn').textContent = 'Add Customer';
            document.getElementById('cancelBtn').style.display = 'none';
        }

        // Cancel edit
        document.getElementById('cancelBtn').addEventListener('click', resetForm);

        // Show alert message
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.appendChild(alert);
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }
    </script>
</body>
</html>
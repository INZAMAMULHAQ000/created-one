<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

// Fetch materials for dropdown
$materials_query = "SELECT * FROM materials ORDER BY name";
$materials_result = mysqli_query($conn, $materials_query);

// Fetch transports for dropdown
$transports_query = "SELECT * FROM transports ORDER BY name";
$transports_result = mysqli_query($conn, $transports_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Bill</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="includes/sidebar.css" rel="stylesheet">
    <style>
        :root {
            --background-color: #f8f9fa;
            --text-color: #212529;
            --accent-color: #007bff; /* Professional blue for light theme */
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
            --readonly-bg: #e9ecef;
            --readonly-border: #ced4da;
        }

        body.dark-theme {
            --background-color:rgb(133, 141, 148); /* Dark gray for the main page background */
            --text-color: #e2e6ea;
            --accent-color: #66b3ff; /* Lighter blue for dark theme */
            --form-bg: #495057;
            --form-border: #6c757d;
            --form-focus-bg: #495057;
            --form-focus-shadow: 0 0 0 0.25rem rgba(102, 179, 255, 0.25);
            --btn-hover-bg: var(--accent-color);
            --btn-hover-color: #fff;
            --btn-hover-shadow: 0 0 0 0.25rem rgba(102, 179, 255, 0.25);
            --table-bg: #495057;
            --table-border: #6c757d;
            --readonly-bg: #6c757d;
            --readonly-border: #868e96;
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
        .billing-form {
            background: var(--form-bg);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            transition: background 0.3s ease, box-shadow 0.3s ease;
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
            color: var(--text-color);
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

        /* Select2 Custom Styles for Professional Theme */
        .select2-container--default .select2-selection--multiple {
            background-color: var(--form-bg);
            border: 1px solid var(--form-border);
            border-radius: 0.25rem;
            color: var(--text-color);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: var(--accent-color);
            color: #fff;
            border: 1px solid var(--accent-color);
            border-radius: 0.2rem;
            padding: 0 0.5rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            color: var(--text-color);
        }

        .select2-container--default .select2-results__option {
            background-color: var(--form-bg);
            color: var(--text-color);
        }

        .select2-container--default .select2-results__option--highlighted {
            background-color: var(--accent-color) !important;
            color: #fff !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: var(--form-bg);
            border: 1px solid var(--form-border);
            color: var(--text-color);
        }

        /* Ensure placeholders are visible */
        .form-control::placeholder {
            color: var(--text-color);
            opacity: 0.7;
        }

        /* Custom styles for readonly inputs */
        input[readonly].form-control {
            background-color: var(--readonly-bg);
            border-color: var(--readonly-border);
            cursor: default; /* Indicate non-interactiveness */
        }

        input[readonly].form-control:focus {
            box-shadow: none; /* Remove focus shadow for readonly fields */
            border-color: var(--readonly-border); /* Keep border consistent when focused */
        }

        /* Adjusting select2 placeholder color if needed */
        .select2-container .select2-selection--single .select2-selection__placeholder,
        .select2-container .select2-search__field::placeholder {
            color: var(--text-color);
            opacity: 0.7;
        }

        /* Override Bootstrap's default navbar-dark text color to ensure consistency with our theme */
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

        /* Dynamic Bottom-Left Logo Container */
        #dynamicLogoContainer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 150px; /* Size of the circle */
            height: 150px; /* Size of the circle */
            background-color: white; /* White circular background */
            border-radius: 50%; /* Makes it a circle */
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0.1; /* Initially hidden */
            transition: opacity 0.5s ease-in-out; /* Smooth fade effect */
            z-index: 1000; /* Ensure it's on top */
            pointer-events: none; /* Allows clicks to pass through when hidden */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); /* Optional: subtle shadow for the circle */
        }

        #dynamicLogoContainer img {
            max-width: 80%; /* Logo scales within the circle */
            max-height: 80%; /* Logo scales within the circle */
            object-fit: contain;
            border-radius: 50%; /* Ensure logo itself is also circular if desired */
        }



        .btn-download:hover {
            background-color: var(--btn-accent-hover-bg);
            color: var(--btn-accent-hover-text);
            border-color: var(--btn-accent-hover-border);
        }

        /* Message Box Styles */
        .message-box {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }

        .message-box.success {
            background-color: #d4edda; /* Light green */
            color: #155724; /* Dark green text */
            border: 1px solid #c3e6cb; /* Green border */
        }

        .message-box.error {
            background-color: #f8d7da; /* Light red */
            color: #721c24; /* Dark red text */
            border: 1px solid #f5c6cb; /* Red border */
        }

        /* Apply theme-specific colors for message boxes */
        body.dark-theme .message-box.error {
            background-color: #721c24;
            color: #f8d7da;
            border-color: #f5c6cb;
        }

        body.dark-theme .message-box.success {
            background-color: #155724;
            color: #d4edda;
            border-color: #c3e6cb;
        }
    </style>
</head>
<body class="dark-theme"> <!-- Default to dark theme -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="billing-form">
            <h2 class="text-center mb-4 main-text">Generate Bill</h2>

            <?php if (isset($_GET['status']) && $_GET['status'] == 'error' && isset($_GET['message'])): ?>
                <div class="message-box error">
                    <strong>Error:</strong> <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
            <?php endif; ?>

            <form id="billingForm" action="generate_pdf.php" method="post" target="_blank">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="invoice_number" class="form-label main-text">Invoice Number:</label>
                        <input type="text" class="form-control" id="invoice_number" name="invoice_number" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Customer Name</label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>GSTIN ID</label>
                        <input type="text" name="gstin" class="form-control">
                    </div>
                </div>

                <!-- Customer fields with dropdown integration -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Address</label>
                        <textarea name="customer_address" id="customer_address" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Phone No</label>
                        <input type="text" name="customer_phone" id="customer_phone" class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Material</label>
                        <select name="material[]" id="material" class="form-select" multiple="multiple">
                            <?php while($row = mysqli_fetch_assoc($materials_result)): ?>
                                <option value="<?php echo $row['id']; ?>" data-price="<?php echo $row['price']; ?>" data-hsn="<?php echo $row['hsn_code']; ?>">
                                    <?php echo $row['name']; ?> (HSN: <?php echo $row['hsn_code']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Total Price</label>
                        <input type="number" name="price" id="price" class="form-control" required readonly value="0">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Selected Materials</label>
                        <div id="selectedMaterialsTableContainer" style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-bordered table-sm" id="selectedMaterialsTable">
                                <thead>
                                    <tr>
                                        <th>Material</th>
                                        <th>HSN</th>
                                        <th>Price/Unit</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Selected materials will be added here -->
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" name="selected_materials_data" id="selectedMaterialsData">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>CGST (%)</label>
                        <input type="number" name="cgst_rate" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>SGST (%)</label>
                        <input type="number" name="sgst_rate" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>IGST (%)</label>
                        <input type="number" name="igst_rate" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Mode of Transport</label>
                        <select name="transport" id="transport" class="form-select" required>
                            <option value="">Select Mode of Transport</option>
                            <?php while($trow = mysqli_fetch_assoc($transports_result)): ?>
                                <option value="<?php echo $trow['id']; ?>"><?php echo $trow['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="po_number" class="form-label main-text">PO Number:</label>
                        <input type="text" class="form-control" id="po_number" name="po_number">
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-accent btn-lg">Generate Bill</button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <div id="dynamicLogoContainer">
        <img src="logo.png" alt="Company Logo">
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="js/customer-dropdown.js"></script>
    <script src="includes/sidebar.js"></script>
    <script>
        $(document).ready(function() {
            $('#material').select2();

            // Initialize customer dropdown
            if (typeof customerDropdown !== 'undefined') {
                customerDropdown.initializeDropdown({
                    nameFieldId: 'customer_name',
                    phoneFieldId: 'customer_phone',
                    addressFieldId: 'customer_address'
                });
            }

            // Initialize sidebar
            initializeSidebar();

            // Dynamic Logo Logic
            let idleTimeout;
            const dynamicLogoContainer = $('#dynamicLogoContainer');
            const idleTime = 2000; // 2 seconds of inactivity before hiding

            function showLogo() {
                dynamicLogoContainer.css('opacity', '1');
                dynamicLogoContainer.css('pointer-events', 'auto');
                clearTimeout(idleTimeout);
                idleTimeout = setTimeout(hideLogo, idleTime);
            }

            function hideLogo() {
                dynamicLogoContainer.css('opacity', '0');
                dynamicLogoContainer.css('pointer-events', 'none');
            }

            // Show logo on initial load (optional, or wait for first interaction)
            // showLogo(); 

            $(document).on('mousemove scroll touchstart', function() {
                showLogo();
            });

            // Initial hide after page load if no immediate interaction
            idleTimeout = setTimeout(hideLogo, idleTime);

            function calculateGrandTotal() {
                let grandTotal = 0;
                $('#selectedMaterialsTable tbody tr').each(function() {
                    const subtotal = parseFloat($(this).find('.item-subtotal').text());
                    if (!isNaN(subtotal)) {
                        grandTotal += subtotal;
                    }
                });
                $('#price').val(grandTotal.toFixed(2));
            }

            $('#material').on('change', function() {
                const selectedMaterialIds = $(this).val() || [];
                const materialsInTable = {};
                $('#selectedMaterialsTable tbody tr').each(function() {
                    const id = $(this).data('id');
                    materialsInTable[id] = $(this);
                });

                // Remove deselected materials from table
                for (const id in materialsInTable) {
                    if (!selectedMaterialIds.includes(id)) {
                        materialsInTable[id].remove();
                    }
                }

                // Add newly selected materials to table
                selectedMaterialIds.forEach(function(id) {
                    if (!materialsInTable[id]) {
                        const option = $('#material option[value="' + id + '"]');
                        const name = option.text().split('(HSN:')[0].trim();
                        const hsn = option.data('hsn');
                        const pricePerUnit = option.data('price');

                        const newRow = `<tr data-id="${id}" data-price-per-unit="${pricePerUnit}">
                                            <td>${name}</td>
                                            <td>${hsn}</td>
                                            <td>${pricePerUnit}</td>
                                            <td><input type="number" class="form-control form-control-sm item-quantity" value="1" min="1" style="width: 80px;"></td>
                                            <td class="item-subtotal">${(pricePerUnit * 1).toFixed(2)}</td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-item">Remove</button></td>
                                        </tr>`;
                        $('#selectedMaterialsTable tbody').append(newRow);
                    }
                });
                updateHiddenDataAndTotal();
            });

            $('#selectedMaterialsTable').on('input', '.item-quantity', function() {
                const row = $(this).closest('tr');
                const quantity = parseInt($(this).val()) || 0;
                const pricePerUnit = parseFloat(row.data('price-per-unit'));
                const subtotal = quantity * pricePerUnit;
                row.find('.item-subtotal').text(subtotal.toFixed(2));
                updateHiddenDataAndTotal();
            });

            $('#selectedMaterialsTable').on('click', '.remove-item', function() {
                const row = $(this).closest('tr');
                const materialIdToRemove = row.data('id').toString();
                
                // Deselect the item in the select2 dropdown
                const currentSelected = $('#material').val();
                const newSelected = currentSelected.filter(id => id !== materialIdToRemove);
                $('#material').val(newSelected).trigger('change.select2');

                row.remove(); // Remove the row from the table
                updateHiddenDataAndTotal();
            });

            function updateHiddenDataAndTotal() {
                const selectedMaterials = [];
                $('#selectedMaterialsTable tbody tr').each(function() {
                    const id = $(this).data('id');
                    const name = $(this).find('td').eq(0).text().trim();
                    const hsn_code = $(this).find('td').eq(1).text().trim();
                    const price_per_unit = parseFloat($(this).data('price-per-unit'));
                    const quantity = parseInt($(this).find('.item-quantity').val()) || 0;
                    selectedMaterials.push({ id: id, name: name, hsn_code: hsn_code, price_per_unit: price_per_unit, quantity: quantity });
                });
                $('#selectedMaterialsData').val(JSON.stringify(selectedMaterials));
                calculateGrandTotal();
            }

            // Initial call to populate table if there are pre-selected items (e.g., on form reload, though not implemented here)
            updateHiddenDataAndTotal();

            // Theme Toggle Logic
            $('#themeToggle').on('click', function() {
                $('body').toggleClass('light-theme dark-theme');
                // Save preference to localStorage
                if ($('body').hasClass('light-theme')) {
                    localStorage.setItem('theme', 'light');
                } else {
                    localStorage.setItem('theme', 'dark');
                }
            });

            // Load theme preference on page load
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                $('body').removeClass('light-theme dark-theme').addClass(savedTheme + '-theme');
            } else {
                // Default to dark if no preference saved
                $('body').addClass('dark-theme');
            }

            // --- localStorage for GST fields ---

            // Function to load GST values from localStorage
            function loadGstValues() {
                const cgst = localStorage.getItem('cgst_rate');
                const sgst = localStorage.getItem('sgst_rate');
                const igst = localStorage.getItem('igst_rate');

                if (cgst !== null) {
                    $('input[name="cgst_rate"]').val(cgst);
                }
                if (sgst !== null) {
                    $('input[name="sgst_rate"]').val(sgst);
                }
                if (igst !== null) {
                    $('input[name="igst_rate"]').val(igst);
                }
            }

            // Function to save GST value to localStorage
            function saveGstValue(inputElement, key) {
                localStorage.setItem(key, $(inputElement).val());
            }

            // Load values on page load
            loadGstValues();

            // Save values when input changes
            $('input[name="cgst_rate"]').on('input', function() {
                saveGstValue(this, 'cgst_rate');
            });

            $('input[name="sgst_rate"]').on('input', function() {
                saveGstValue(this, 'sgst_rate');
            });

            $('input[name="igst_rate"]').on('input', function() {
                saveGstValue(this, 'igst_rate');
            });

            // Load invoice number from localStorage
            const invoiceNumberField = document.getElementById('invoice_number');
            if (invoiceNumberField) {
                const savedInvoiceNumber = localStorage.getItem('invoice_number');
                if (savedInvoiceNumber !== null) {
                    invoiceNumberField.value = savedInvoiceNumber;
                }
            }

            // Save invoice number to localStorage on input
            if (invoiceNumberField) {
                invoiceNumberField.addEventListener('input', function() {
                    localStorage.setItem('invoice_number', this.value);
                });
            }
        });
    </script>
</body>
</html> 
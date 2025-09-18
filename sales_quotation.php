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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Sales Quotation</title>
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
    </style>
</head>
<body class="dark-theme"> <!-- Default to dark theme -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="billing-form">
            <h2 class="text-center mb-4 main-text">Generate Sales Quotation</h2>

            <form id="quotationForm" action="generate_quotation_pdf.php" method="post" target="_blank">
                <h5 class="main-text">Office Bio</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label main-text">Name</label>
                        <input type="text" name="office_name" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label main-text">Address</label>
                        <input type="text" name="office_address" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label main-text">Phone No</label>
                        <input type="text" name="office_phone" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label main-text">GST ID</label>
                        <input type="text" name="office_gst" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label main-text">Owner Name</label>
                        <input type="text" name="office_owner" class="form-control">
                    </div>
                </div>

                <h5 class="main-text mt-4">Quotation Headlines</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label main-text">Quotation Number</label>
                        <input type="text" name="quotation_number" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label main-text">Date of Issue</label>
                        <input type="date" name="date_issue" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label main-text">Date of Submission</label>
                        <input type="date" name="date_submission" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label main-text">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control">
                    </div>
                </div>

                <h5 class="main-text mt-4">To</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label main-text">Customer Name</label>
                        <input type="text" name="customer_name" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label main-text">Customer Company (Optional)</label>
                        <input type="text" name="customer_company" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label main-text">Address</label>
                        <textarea name="customer_address" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label main-text">Phone No</label>
                        <input type="text" name="customer_phone" class="form-control" required>
                    </div>
                </div>

                <h5 class="main-text mt-4">Materials</h5>
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label>Material</label>
                        <select name="material[]" id="material" class="form-select" multiple="multiple">
                            <?php while($row = mysqli_fetch_assoc($materials_result)): ?>
                                <option value="<?php echo $row['id']; ?>" data-price="<?php echo $row['price']; ?>" data-hsn="<?php echo $row['hsn_code']; ?>">
                                    <?php echo $row['name']; ?> (HSN: <?php echo $row['hsn_code']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label>Selected Materials</label>
                        <div id="selectedMaterialsTableContainer" style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-bordered table-sm" id="selectedMaterialsTable">
                                <thead>
                                    <tr>
                                        <th>S.NO</th>
                                        <th>Material Description</th>
                                        <th>HSN Code</th>
                                        <th>Qty</th>
                                        <th>Price</th>
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
                    <div class="col-md-12 mb-3">
                        <label class="form-label main-text">Terms and Conditions</label>
                        <textarea name="terms_conditions" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label main-text">Quote Prepared by</label>
                        <input type="text" name="quote_prepared_by" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label main-text">Quote Approved By</label>
                        <input type="text" name="quote_approved_by" class="form-control">
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-accent btn-lg">Generate Quotation</button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min..js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="includes/sidebar.js"></script>
    <script>
        $(document).ready(function() {
            $('#material').select2();

            // Initialize sidebar
            initializeSidebar();

            function calculateGrandTotal() {
                // Similar to billing.php, but might not be needed if total is not displayed on this page
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
                let item_count = 1;
                selectedMaterialIds.forEach(function(id) {
                    if (!materialsInTable[id]) {
                        const option = $('#material option[value="' + id + '"]');
                        const name = option.text().split('(HSN:')[0].trim();
                        const hsn = option.data('hsn');
                        const pricePerUnit = option.data('price');

                        const newRow = `<tr data-id="${id}" data-price-per-unit="${pricePerUnit}">
                                            <td>${item_count++}</td>
                                            <td>${name}</td>
                                            <td>${hsn}</td>
                                            <td><input type="number" class="form-control form-control-sm item-quantity" value="1" min="1" style="width: 80px;"></td>
                                            <td class="item-price">${pricePerUnit}</td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-item">Remove</button></td>
                                        </tr>`;
                        $('#selectedMaterialsTable tbody').append(newRow);
                    }
                });
                updateHiddenDataAndTotal();
            });

            $('#selectedMaterialsTable').on('input', '.item-quantity', function() {
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
                let count = 1;
                $('#selectedMaterialsTable tbody tr').each(function() {
                    $(this).find('td:first').text(count++);
                    const id = $(this).data('id');
                    const name = $(this).find('td').eq(1).text().trim();
                    const hsn_code = $(this).find('td').eq(2).text().trim();
                    const quantity = parseInt($(this).find('.item-quantity').val()) || 0;
                    const price_per_unit = parseFloat($(this).data('price-per-unit'));
                    selectedMaterials.push({ id: id, name: name, hsn_code: hsn_code, price_per_unit: price_per_unit, quantity: quantity });
                });
                $('#selectedMaterialsData').val(JSON.stringify(selectedMaterials));
            }

            // Theme Toggle Logic
            $('#themeToggle').on('click', function() {
                $('body').toggleClass('light-theme dark-theme');
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
                $('body').addClass('dark-theme');
            }
        });
    </script>
</body>
</html>

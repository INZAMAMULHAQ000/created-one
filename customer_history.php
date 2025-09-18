<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

// Fetch invoices from the database
$invoices = [];
$sql = "SELECT invoice_number, customer_name, invoice_date, pdf_path, created_at FROM invoices ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $invoices[] = $row;
    }
} else {
    error_log("Error fetching invoices: " . mysqli_error($conn));
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer History</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
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
            --btn-accent-hover-bg: var(--accent-color);
            --btn-accent-hover-text: #fff;
            --btn-accent-hover-border: var(--accent-color);
        }

        body.dark-theme {
            --background-color: #212529; /* Dark gray for the main page background */
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
            --btn-accent-hover-bg: var(--accent-color);
            --btn-accent-hover-text: #fff;
            --btn-accent-hover-border: var(--accent-color);
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

        /* Navbar Styles */
        .navbar {
            background-color: var(--form-bg); /* Use a consistent background for navbar */
            border-bottom: 1px solid var(--form-border);
        }
        .navbar-brand,
        .nav-link {
            font-family: 'Segoe UI', Roboto, "Helvetica Neue", Arial, sans-serif; /* Stylish font */
            font-weight: bold; /* Make it bold */
            color: var(--text-color) !important; /* Ensure visibility */
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: var(--accent-color) !important;
        }

        .btn-download {
            background-color: var(--accent-color);
            color: #fff;
            border: 1px solid var(--accent-color);
            transition: all 0.3s ease;
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

        /* Table specific styles for dark theme in customer_history */
        body.dark-theme .table th {
            background-color: var(--accent-color);
            color: var(--text-color);
            border-color: var(--table-border);
        }

        body.dark-theme .table td {
            background-color: var(--form-bg);
            color: var(--text-color);
            border-color: var(--table-border);
        }

        body.dark-theme .table-hover tbody tr:hover {
            background-color: #5a6268; /* Slightly lighter than form-bg for hover effect */
        }
    </style>
</head>
<body class="dark-theme">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="billing.php">
                <img src="Sun.jpeg" alt="Sun Logo" style="height: 90px; margin-right: 10px; vertical-align: middle;">
                <span class="main-text"> Madhu PaperBags</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="billing.php" style="color: var(--main-text-color) !important;">Billing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="materials.php" style="color: var(--main-text-color) !important;">Materials</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transport.php" style="color: var(--main-text-color) !important;">Transport</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="customer_history.php" style="color: var(--main-text-color) !important;">Customer History</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="delete_invoices.php" style="color: var(--main-text-color) !important;">Delete All Invoices</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php" style="color: var(--main-text-color) !important;">Change Password</a>
                    </li>
                    <li class="nav-item">
                        <button id="themeToggle" class="btn btn-secondary ms-2">Toggle Theme</button>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php" style="color: var(--main-text-color) !important;">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div id="dynamicLogoContainer">
        <img src="logo.png" alt="Company Logo">
    </div>

    <div class="container mt-5">
        <h2 class="text-center mb-4 main-text">Customer Invoice History</h2>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success' && isset($_GET['invoice'])): ?>
            <div class="message-box success">
                Invoice <strong><?php echo htmlspecialchars($_GET['invoice']); ?></strong> generated and saved successfully!
            </div>
        <?php endif; ?>

        <?php if (empty($invoices)): ?>
            <p style="color: var(--main-text-color); text-align: center;">No invoices generated yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice No.</th>
                            <th>Customer Name</th>
                            <th>Invoice Date</th>
                            <th>Generated On</th>
                            <th>Action</th>
                        </tr>
                        <tr id="filter-row">
                            <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search Invoice No." data-column="0"></th>
                            <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search Customer" data-column="1"></th>
                            <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search Date" data-column="2"></th>
                            <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search Generated On" data-column="3"></th>
                            <th></th> <!-- Empty for Action column -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                                <td><?php echo htmlspecialchars($invoice['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars((new DateTime($invoice['invoice_date']))->format('d-m-Y')); ?></td>
                                <td><?php echo htmlspecialchars((new DateTime($invoice['created_at']))->format('d-m-Y H:i:s')); ?></td>
                                <td>
                                    <a href="download_invoice.php?file=<?php echo urlencode(basename($invoice['pdf_path'])); ?>" class="btn btn-download btn-sm">Download PDF</a>
                                    <form action="delete_invoice.php" method="post" style="display:inline;">
                                        <input type="hidden" name="invoice_number" value="<?php echo htmlspecialchars($invoice['invoice_number']); ?>">
                                        <button type="submit" class="btn btn-danger btn-sm btn-delete-invoice" onclick="return confirm('Are you sure you want to delete this invoice?');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        $(document).ready(function() {
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
        });

        // Column Search Functionality
        $('.column-search').on('keyup', function() {
            var filters = {};
            $('.column-search').each(function() {
                var colIndex = $(this).data('column');
                var searchTerm = $(this).val().toLowerCase();
                if (searchTerm) {
                    filters[colIndex] = searchTerm;
                }
            });

            $('#customerHistoryTable tbody tr').each(function() {
                var row = $(this);
                var showRow = true;

                for (var colIndex in filters) {
                    var cellText = row.find('td').eq(colIndex).text().toLowerCase();
                    if (cellText.indexOf(filters[colIndex]) === -1) {
                        showRow = false;
                        break;
                    }
                }
                row.toggle(showRow);
            });
        });

        // Add ID to the table for easy selection
        $('table').attr('id', 'customerHistoryTable');
    </script>
</body>
</html> 
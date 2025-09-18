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
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $price = floatval($_POST['price']);
        $hsn_code = mysqli_real_escape_string($conn, $_POST['hsn_code']);
        $sql = "INSERT INTO materials (name, price, hsn_code) VALUES ('$name', $price, '$hsn_code')";
        $response['success'] = mysqli_query($conn, $sql);
        $response['id'] = mysqli_insert_id($conn);
    } elseif ($_POST['action'] === 'update') {
        $id = intval($_POST['id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $price = floatval($_POST['price']);
        $hsn_code = mysqli_real_escape_string($conn, $_POST['hsn_code']);
        $sql = "UPDATE materials SET name='$name', price=$price, hsn_code='$hsn_code' WHERE id=$id";
        $response['success'] = mysqli_query($conn, $sql);
    } elseif ($_POST['action'] === 'delete') {
        $id = intval($_POST['id']);
        $sql = "DELETE FROM materials WHERE id=$id";
        $response['success'] = mysqli_query($conn, $sql);
    } elseif ($_POST['action'] === 'fetch') {
        $materials = [];
        $result = mysqli_query($conn, "SELECT * FROM materials ORDER BY name");
        while ($row = mysqli_fetch_assoc($result)) {
            $materials[] = $row;
        }
        $response['success'] = true;
        $response['materials'] = $materials;
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
    <title>Manage Materials</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        }

        body.light-theme {
            --background-color: #f0f2f5;
            --text-color: #333;
            --accent-color: #007bff; /* A blue for light theme */
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
        .materials-form, .materials-table {
            background: var(--form-bg);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1); /* Simplified shadow */
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }
        .main-text {
            color: var(--text-color);
            transition: color 0.3s ease;
        }
        .form-control {
            background: var(--form-bg);
            border: 1px solid var(--form-border);
            color: var(--text-color);
            transition: background 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
        .form-control:focus {
            background: var(--form-focus-bg);
            border-color: var(--accent-color); /* Use accent color for focus border */
            box-shadow: var(--form-focus-shadow);
            color: var(--text-color);
        }
        .btn-accent {
            background: var(--accent-color);
            border: 1px solid var(--accent-color);
            color: #fff; /* White text for accent buttons */
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
    </style>
</head>
<body class="dark-theme">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand main-text" href="billing.php">
                <img src="Sun.jpeg" alt="Company Logo" style="height: 90px; margin-right: 10px; vertical-align: middle;">
                Madhu PaperBags
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="materials.php">Manage Materials</a>
                <a class="nav-link" href="transport.php">Manage Transport</a>
                <a class="nav-link" href="customer_history.php">Customer History</a>
                <a class="nav-link" href="change_password.php">Change Password</a>
                <button id="themeToggle" class="btn btn-secondary ms-2">Toggle Theme</button>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="materials-form mb-4">
            <h2 class="text-center mb-4 neon-text">Manage Materials</h2>
            <form id="addMaterialForm" class="row g-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" id="materialName" placeholder="Material Name" required>
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control" id="materialPrice" placeholder="Price" min="0" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control" id="materialHSN" placeholder="HSN Code" maxlength="20" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">Add</button>
                </div>
            </form>
        </div>
        <div class="materials-table">
            <h4 class="mb-3 neon-text">Materials List</h4>
            <table class="table table-hover" id="materialsTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Price</th>
                        <th>HSN Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Materials will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>

    <div id="dynamicLogoContainer">
        <img src="logo.png" alt="Company Logo">
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function fetchMaterials() {
            $.post('materials.php', {action: 'fetch'}, function(data) {
                if(data.success) {
                    let rows = '';
                    data.materials.forEach(function(mat) {
                        rows += `<tr data-id="${mat.id}">
                            <td><span class="mat-name">${mat.name}</span></td>
                            <td><span class="mat-price">${parseFloat(mat.price).toFixed(2)}</span></td>
                            <td><span class="mat-hsn">${mat.hsn_code}</span></td>
                            <td>
                                <button class="btn btn-sm btn-info edit-btn">Edit</button>
                                <button class="btn btn-sm btn-danger delete-btn">Delete</button>
                            </td>
                        </tr>`;
                    });
                    $('#materialsTable tbody').html(rows);
                }
            }, 'json');
        }

        $(document).ready(function() {
            fetchMaterials();

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

            $(document).on('mousemove scroll touchstart', function() {
                showLogo();
            });

            // Initial hide after page load if no immediate interaction
            idleTimeout = setTimeout(hideLogo, idleTime);

            $('#addMaterialForm').submit(function(e) {
                e.preventDefault();
                const name = $('#materialName').val().trim();
                const price = $('#materialPrice').val();
                const hsn_code = $('#materialHSN').val().trim();
                if(name && price && hsn_code) {
                    $.post('materials.php', {action: 'add', name, price, hsn_code}, function(data) {
                        if(data.success) {
                            fetchMaterials();
                            $('#addMaterialForm')[0].reset();
                        } else if(data.error) {
                            alert(data.error);
                        }
                    }, 'json');
                }
            });

            $('#materialsTable').on('click', '.delete-btn', function() {
                if(confirm('Delete this material?')) {
                    const id = $(this).closest('tr').data('id');
                    $.post('materials.php', {action: 'delete', id}, function(data) {
                        if(data.success) fetchMaterials();
                    }, 'json');
                }
            });

            $('#materialsTable').on('click', '.edit-btn', function() {
                const tr = $(this).closest('tr');
                const id = tr.data('id');
                const name = tr.find('.mat-name').text();
                const price = tr.find('.mat-price').text();
                const hsn = tr.find('.mat-hsn').text();
                tr.html(`<td><input type='text' class='form-control form-control-sm edit-name' value='${name}'></td>
                         <td><input type='number' class='form-control form-control-sm edit-price' value='${price}' min='0' step='0.01'></td>
                         <td><input type='text' class='form-control form-control-sm edit-hsn' value='${hsn}' maxlength='20'></td>
                         <td>
                            <button class='btn btn-sm btn-success save-btn'>Save</button>
                            <button class='btn btn-sm btn-secondary cancel-btn'>Cancel</button>
                         </td>`);
            });

            $('#materialsTable').on('click', '.cancel-btn', function() {
                fetchMaterials();
            });

            $('#materialsTable').on('click', '.save-btn', function() {
                const tr = $(this).closest('tr');
                const id = tr.data('id');
                const name = tr.find('.edit-name').val().trim();
                const price = tr.find('.edit-price').val();
                const hsn_code = tr.find('.edit-hsn').val().trim();
                if(name && price && hsn_code) {
                    $.post('materials.php', {action: 'update', id, name, price, hsn_code}, function(data) {
                        if(data.success) fetchMaterials();
                        else if(data.error) alert(data.error);
                    }, 'json');
                }
            });

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
        });
    </script>
</body>
</html> 
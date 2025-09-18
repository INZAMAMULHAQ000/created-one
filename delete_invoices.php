<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
        // Start transaction
        mysqli_begin_transaction($conn);

        try {
            // Step 1: Get all PDF paths from the database
            $sql_select_paths = "SELECT pdf_path FROM invoices";
            $result_paths = mysqli_query($conn, $sql_select_paths);
            $pdf_paths = [];
            if ($result_paths) {
                while ($row = mysqli_fetch_assoc($result_paths)) {
                    $pdf_paths[] = $row['pdf_path'];
                }
            }

            // Step 2: Delete all records from the invoices table
            $sql_delete_db = "DELETE FROM invoices";
            if (!mysqli_query($conn, $sql_delete_db)) {
                throw new Exception("Error deleting records from database: " . mysqli_error($conn));
            }

            // Step 3: Delete the corresponding PDF files
            $deleted_files_count = 0;
            $failed_files_count = 0;
            foreach ($pdf_paths as $path) {
                if (file_exists($path)) {
                    if (!unlink($path)) {
                        error_log("Failed to delete file: " . $path);
                        $failed_files_count++;
                    } else {
                        $deleted_files_count++;
                    }
                }
            }

            mysqli_commit($conn);
            $message_type = 'success';
            $message = "All invoices and " . $deleted_files_count . " associated PDF files have been deleted successfully.";
            if ($failed_files_count > 0) {
                $message .= " (Failed to delete " . $failed_files_count . " PDF files).";
            }

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $message_type = 'error';
            $message = "Deletion failed: " . $e->getMessage();
            error_log($message);
        }
    } else {
        $message_type = 'error';
        $message = 'Deletion not confirmed.';
    }
} else {
    // Display confirmation form
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Invoices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: var(--background-color);
            color: var(--text-color);
            min-height: 100vh;
            transition: background 0.3s ease, color 0.3s ease;
        }
        .container {
            padding: 2rem;
        }
        .delete-form-container {
            background: var(--form-bg);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }
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
        /* Reuse general styles from billing.php or a shared CSS */
        .main-text { color: var(--text-color); }
        .btn-danger { background-color: #dc3545; border-color: #dc3545; color: #fff; }
        .btn-danger:hover { background-color: #c82333; border-color: #bd2130; }
        .btn-secondary { background-color: #6c757d; border-color: #6c757d; color: #fff; }
        .btn-secondary:hover { background-color: #5a6268; border-color: #545b62; }
    </style>
</head>
<body class="dark-theme"> <!-- Default to dark theme -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand main-text" href="#">
                <img src="Sun.jpeg" alt="Company Logo" style="height: 90px; margin-right: 10px; vertical-align: middle;">
                Madhu PaperBags
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="billing.php">Billing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="materials.php">Materials</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transport.php">Transport</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="customer_history.php">Customer History</a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="delete_invoices.php">Delete All Invoices</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php">Change Password</a>
                    </li>
                    <li class="nav-item">
                        <button id="themeToggle" class="btn btn-secondary ms-2">Toggle Theme</button>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="delete-form-container">
            <h2 class="text-center mb-4 main-text">Delete All Invoices</h2>

            <?php if ($message): ?>
                <div class="message-box <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (!isset($_POST['confirm_delete']) || $_POST['confirm_delete'] != 'yes' || $message_type == 'error'): ?>
            <div class="alert alert-warning text-center" role="alert">
                <p class="main-text"><strong>Warning:</strong> This action will permanently delete ALL invoice records from the database and their corresponding PDF files.</p>
                <p class="main-text">This action cannot be undone. Please confirm to proceed.</p>
            </div>
            <form action="delete_invoices.php" method="post" class="text-center">
                <input type="hidden" name="confirm_delete" value="yes">
                <button type="submit" class="btn btn-danger btn-lg me-2">Confirm Delete All</button>
                <a href="customer_history.php" class="btn btn-secondary btn-lg">Cancel</a>
            </form>
            <?php else: // If confirmed and successful, show only message and go back button ?>
                <div class="text-center">
                    <a href="customer_history.php" class="btn btn-primary btn-lg">Go to Customer History</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme Toggle Logic (copied from billing.php to maintain consistency)
        $(document).ready(function() {
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
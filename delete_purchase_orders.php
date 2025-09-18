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
            $sql_select_paths = "SELECT pdf_path FROM purchase_orders";
            $result_paths = mysqli_query($conn, $sql_select_paths);
            $pdf_paths = [];
            if ($result_paths) {
                while ($row = mysqli_fetch_assoc($result_paths)) {
                    $pdf_paths[] = $row['pdf_path'];
                }
            }

            // Step 2: Delete all records from the purchase_orders table
            $sql_delete_db = "DELETE FROM purchase_orders";
            if (!mysqli_query($conn, $sql_delete_db)) {
                throw new Exception("Error deleting records from database: " . mysqli_error($conn));
            }

            // Step 3: Delete all PDF files
            $files_deleted = 0;
            $files_failed = 0;
            foreach ($pdf_paths as $pdf_path) {
                if ($pdf_path && file_exists($pdf_path)) {
                    if (unlink($pdf_path)) {
                        $files_deleted++;
                    } else {
                        $files_failed++;
                    }
                } elseif ($pdf_path && file_exists(__DIR__ . '/' . $pdf_path)) {
                    if (unlink(__DIR__ . '/' . $pdf_path)) {
                        $files_deleted++;
                    } else {
                        $files_failed++;
                    }
                }
            }

            // Commit transaction
            mysqli_commit($conn);

            $message = "All purchase order records deleted successfully! ";
            $message .= "Files deleted: $files_deleted";
            if ($files_failed > 0) {
                $message .= ", Files failed to delete: $files_failed";
            }
            $message_type = 'success';

        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $message = "Error occurred while deleting purchase orders: " . $e->getMessage();
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete All Purchase Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --background-color: #f8f9fa;
            --text-color: #212529;
            --accent-color: #007bff;
            --form-bg: #ffffff;
            --form-border: #dee2e6;
        }

        body.dark-theme {
            --background-color: rgb(133, 141, 148);
            --text-color: #e2e6ea;
            --accent-color: #66b3ff;
            --form-bg: #495057;
            --form-border: #6c757d;
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

        .delete-container {
            background: var(--form-bg);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            transition: background 0.3s ease, box-shadow 0.3s ease;
            max-width: 600px;
            margin: 0 auto;
        }

        .main-text {
            color: var(--text-color);
            transition: color 0.3s ease;
        }

        .navbar {
            background-color: var(--form-bg);
            border-bottom: 1px solid var(--form-border);
        }

        .navbar-brand,
        .nav-link {
            font-family: 'Segoe UI', Roboto, "Helvetica Neue", Arial, sans-serif;
            font-weight: bold;
            color: var(--text-color) !important;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--accent-color) !important;
        }

        .message-box {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }

        .message-box.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-box.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
    </style>
</head>
<body class="dark-theme">
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
                        <a class="nav-link" href="purchase_order.php">Purchase Order</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="materials.php">Materials</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transport.php">Transport</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="customer_history.php">Invoice History</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="purchase_order_history.php">PO History</a>
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

    <div class="container">
        <div class="delete-container">
            <h2 class="text-center mb-4 main-text">Delete All Purchase Orders</h2>

            <?php if ($message): ?>
                <div class="message-box <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (!isset($_POST['confirm_delete']) || $_POST['confirm_delete'] != 'yes' || $message_type == 'error'): ?>
            <div class="alert alert-warning text-center" role="alert">
                <p class="main-text"><strong>Warning:</strong> This action will permanently delete ALL purchase order records from the database and their corresponding PDF files.</p>
                <p class="main-text">This action cannot be undone. Please confirm to proceed.</p>
            </div>
            <form action="delete_purchase_orders.php" method="post" class="text-center">
                <input type="hidden" name="confirm_delete" value="yes">
                <button type="submit" class="btn btn-danger btn-lg me-2">Confirm Delete All</button>
                <a href="purchase_order_history.php" class="btn btn-secondary btn-lg">Cancel</a>
            </form>
            <?php else: ?>
            <div class="text-center">
                <a href="purchase_order_history.php" class="btn btn-primary btn-lg">Back to PO History</a>
                <a href="purchase_order.php" class="btn btn-success btn-lg ms-2">Generate New PO</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
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

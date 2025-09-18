<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

$error = '';
if(isset($_GET['error'])) {
    $error = $_GET['error'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Expenses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="includes/sidebar.css" rel="stylesheet">
    <style>
        :root {
            --background-color: #f8f9fa;
            --text-color: #212529;
            --accent-color: #007bff;
            --form-bg: #ffffff;
            --form-border: #dee2e6;
        }

        body.dark-theme {
            --background-color: #212529;
            --text-color: #e2e6ea;
            --accent-color: #66b3ff;
            --form-bg: #495057;
            --form-border: #6c757d;
        }

        body {
            background: var(--background-color);
            color: var(--text-color);
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
        }
        .main-text {
            color: var(--text-color);
        }
        .form-control, .form-select {
            background: var(--form-bg);
            border: 1px solid var(--form-border);
            color: var(--text-color);
        }
    </style>
</head>
<body class="dark-theme">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="billing-form">
                <h2 class="text-center mb-4 main-text">Daily Expenses</h2>

                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form id="expenseForm" action="generate_expense_pdf.php" method="post" target="_blank">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label main-text">Date</label>
                            <input type="date" name="expense_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label main-text">Purchase Order</label>
                            <input type="text" name="purchase_order" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label main-text">Salary</label>
                            <input type="number" step="0.01" name="salary" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label main-text">Printing/ Other services</label>
                            <input type="number" step="0.01" name="printing_services" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label main-text">Petrol Expense</label>
                            <input type="number" step="0.01" name="petrol_expense" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label main-text">Other Expense</label>
                            <textarea name="other_expense" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Generate Expense Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="includes/sidebar.js"></script>
    <script>
        $(document).ready(function() {
            initializeSidebar();
        });
    </script>
</body>
</html>

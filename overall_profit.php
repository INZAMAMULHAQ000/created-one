<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

// Initialize variables for date filtering
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : null;
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : null;
$expenses = [];
$invoices = [];
$total_expenses = 0;
$total_income = 0;

// Build WHERE clause for date filtering
$date_condition = "";
$date_params = [];
$date_types = "";

if ($start_date && $end_date) {
    $date_condition = " WHERE expense_date BETWEEN ? AND ?";
    $date_params = [$start_date, $end_date];
    $date_types = "ss";
}

// Fetch expenses from the database
$sql = "SELECT id, expense_date, purchase_order, salary, printing_services, petrol_expense, other_expense_1, other_expense_2, other_expense_3 FROM daily_expenses" . $date_condition . " ORDER BY expense_date DESC";

if ($date_params) {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $date_types, ...$date_params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);
}

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $expenses[] = $row;
        // Calculate total expenses
        $total_expenses += $row['purchase_order'] + $row['salary'] + $row['printing_services'] + $row['petrol_expense'] + $row['other_expense_1'] + $row['other_expense_2'] + $row['other_expense_3'];
    }
}

// Fetch invoice income data for the same date range
$invoice_date_condition = "";
if ($start_date && $end_date) {
    $invoice_date_condition = " WHERE invoice_date BETWEEN ? AND ?";
}

$income_sql = "SELECT invoice_number, customer_name, invoice_date, total_amount FROM invoices" . $invoice_date_condition . " ORDER BY invoice_date DESC";

if ($start_date && $end_date) {
    $income_stmt = mysqli_prepare($conn, $income_sql);
    mysqli_stmt_bind_param($income_stmt, "ss", $start_date, $end_date);
    mysqli_stmt_execute($income_stmt);
    $income_result = mysqli_stmt_get_result($income_stmt);
} else {
    $income_result = mysqli_query($conn, $income_sql);
}

if ($income_result) {
    while ($row = mysqli_fetch_assoc($income_result)) {
        $invoices[] = $row;
        $total_income += $row['total_amount'];
    }
}

// Calculate profit or loss
$profit_loss = $total_income - $total_expenses;
$is_profit = $profit_loss >= 0;

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overall Profit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="includes/sidebar.css" rel="stylesheet">
    <style>
        :root {
            --background-color: #f8f9fa;
            --text-color: #212529;
        }

        body.dark-theme {
            --background-color: #212529;
            --text-color: #e2e6ea;
        }

        body {
            background: var(--background-color);
            color: var(--text-color);
        }
    </style>
</head>
<body class="dark-theme">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container mt-5">
            <h2 class="text-center mb-4 main-text">Overall Profit & Loss Analysis</h2>

            <!-- Date Range Filter Form -->
            <div class="billing-form mb-4">
                <h4 class="main-text">Select Date Range</h4>
                <form method="post" action="">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="start_date" class="form-label main-text">Start Date:</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="end_date" class="form-label main-text">End Date:</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label main-text" style="opacity: 0;">Action</label>
                            <button type="submit" class="btn btn-primary d-block">Filter Data</button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if ($start_date && $end_date): ?>
            <!-- Income Table -->
            <div class="billing-form mb-4">
                <h4 class="main-text">Billing Income (<?php echo count($invoices); ?> invoices)</h4>
                <div class="table-responsive">
                    <table id="incomeTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Invoice Number</th>
                                <th>Customer Name</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((new DateTime($invoice['invoice_date']))->format('d-m-Y')); ?></td>
                                    <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                                    <td><?php echo htmlspecialchars($invoice['customer_name']); ?></td>
                                    <td>₹<?php echo number_format($invoice['total_amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($start_date && $end_date): ?>
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h5 class="card-title">Total Expenses</h5>
                            <h3>₹<?php echo number_format($total_expenses, 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Total Income</h5>
                            <h3>₹<?php echo number_format($total_income, 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white <?php echo $is_profit ? 'bg-success' : 'bg-warning'; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $is_profit ? 'Profit' : 'Loss'; ?></h5>
                            <h3>₹<?php echo number_format(abs($profit_loss), 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Date Range</h5>
                            <p class="mb-0"><?php echo date('d-m-Y', strtotime($start_date)); ?></p>
                            <p class="mb-0">to</p>
                            <p class="mb-0"><?php echo date('d-m-Y', strtotime($end_date)); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="billing-form">
                <h4 class="main-text">Daily Expenses (<?php echo count($expenses); ?> records)</h4>
                <div class="table-responsive">
                    <table id="expensesTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Purchase Order</th>
                                <th>Salary</th>
                                <th>Printing/Other Services</th>
                                <th>Petrol Expense</th>
                                <th>Other Expense 1</th>
                                <th>Other Expense 2</th>
                                <th>Other Expense 3</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expenses as $expense): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((new DateTime($expense['expense_date']))->format('d-m-Y')); ?></td>
                                    <td><?php echo htmlspecialchars($expense['purchase_order']); ?></td>
                                    <td><?php echo htmlspecialchars($expense['salary']); ?></td>
                                    <td><?php echo htmlspecialchars($expense['printing_services']); ?></td>
                                    <td><?php echo htmlspecialchars($expense['petrol_expense']); ?></td>
                                    <td><?php echo htmlspecialchars($expense['other_expense_1']); ?></td>
                                    <td><?php echo htmlspecialchars($expense['other_expense_2']); ?></td>
                                    <td><?php echo htmlspecialchars($expense['other_expense_3']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <?php if ($start_date && $end_date): ?>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generatePdfModal">
                        Generate PDF Report
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!$start_date || !$end_date): ?>
            <div class="alert alert-info text-center">
                <h5>Please select a date range to view profit/loss analysis</h5>
                <p>Use the date range filter above to analyze expenses and income for specific periods.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="generatePdfModal" tabindex="-1" aria-labelledby="generatePdfModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="generatePdfModalLabel">Generate PDF Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="generate_profit_report_pdf.php" method="post" target="_blank">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Generate PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="includes/sidebar.js"></script>
    <script>
        $(document).ready(function() {
            initializeSidebar();
            $('#expensesTable').DataTable();
            $('#incomeTable').DataTable();
        });
    </script>
</body>
</html>

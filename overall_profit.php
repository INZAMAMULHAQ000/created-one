<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

// Fetch all expenses from the database
$expenses = [];
$sql = "SELECT id, expense_date, purchase_order, salary, printing_services, petrol_expense, other_expense FROM daily_expenses ORDER BY expense_date DESC";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $expenses[] = $row;
    }
}

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
            <h2 class="text-center mb-4 main-text">Overall Profit</h2>

            <div class="billing-form">
                <h4 class="main-text">Daily Expenses</h4>
                <div class="table-responsive">
                    <table id="expensesTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Purchase Order</th>
                                <th>Salary</th>
                                <th>Printing/Other Services</th>
                                <th>Petrol Expense</th>
                                <th>Other Expense</th>
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
                                    <td><?php echo htmlspecialchars($expense['other_expense']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generatePdfModal">
                        Generate PDF Report
                    </button>
                </div>
            </div>
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
                            <input type="date" class="form-control" id="startDate" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate" name="end_date" required>
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
        });
    </script>
</body>
</html>

<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

// Fetch expenses from the database
$expenses = [];
$sql = "SELECT id, expense_date, pdf_path, created_at FROM daily_expenses ORDER BY expense_date DESC";
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
    <title>Daily Expense History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="includes/sidebar.css" rel="stylesheet">
    <style>
        :root {
            --background-color: #f8f9fa;
            --text-color: #212529;
            --accent-color: #007bff;
            --form-bg: #ffffff;
            --form-border: #dee2e6;
            --table-bg: #ffffff;
            --table-border: #dee2e6;
        }

        body.dark-theme {
            --background-color: #212529;
            --text-color: #e2e6ea;
            --accent-color: #66b3ff;
            --form-bg: #495057;
            --form-border: #6c757d;
            --table-bg: #495057;
            --table-border: #6c757d;
        }

        body {
            background: var(--background-color);
            color: var(--text-color);
            transition: background 0.3s ease, color 0.3s ease;
        }
        .container {
            padding: 2rem;
        }
        .table {
            color: var(--text-color);
        }
        .table th, .table td {
            background: var(--table-bg);
            border-color: var(--table-border);
            transition: background 0.3s ease, border-color 0.3s ease;
        }
    </style>
</head>
<body class="dark-theme">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container mt-5">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Expense History</li>
                </ol>
            </nav>
            <h2 class="text-center mb-4 main-text">Daily Expense History</h2>

            <?php if (isset($_GET['status']) && $_GET['status'] == 'success' && isset($_GET['date'])): ?>
                <div class="alert alert-success">
                    Expense report for <strong><?php echo htmlspecialchars($_GET['date']); ?></strong> generated and saved successfully!
                </div>
            <?php endif; ?>

            <?php if (empty($expenses)): ?>
                <p class="text-center">No expense reports generated yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Expense Date</th>
                                <th>Generated On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expenses as $expense): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((new DateTime($expense['expense_date']))->format('d-m-Y')); ?></td>
                                    <td><?php echo htmlspecialchars((new DateTime($expense['created_at']))->format('d-m-Y H:i:s')); ?></td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($expense['pdf_path']); ?>" class="btn btn-primary btn-sm" download>Download PDF</a>
                                        <form action="delete_expense.php" method="post" style="display:inline;">
                                            <input type="hidden" name="expense_id" value="<?php echo $expense['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this expense report?');">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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

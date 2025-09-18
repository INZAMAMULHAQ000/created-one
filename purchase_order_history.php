<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

// Fetch purchase orders from the database
$purchase_orders = [];
$sql = "SELECT id, po_number, po_date, seller_name, seller_company, total_amount, created_at, updated_at FROM purchase_orders ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $purchase_orders[] = $row;
    }
} else {
    error_log("Error fetching purchase orders: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="includes/sidebar.css" rel="stylesheet">
    <style>
        :root {
            --background-color: #f8f9fa;
            --text-color: #212529;
            --accent-color: #007bff;
            --form-bg: #ffffff;
            --form-border: #dee2e6;
            --form-focus-bg: #ffffff;
            --form-focus-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
            --table-bg: #ffffff;
            --table-border: #dee2e6;
            --table-hover-bg: #f8f9fa;
        }

        body.dark-theme {
            --background-color: rgb(133, 141, 148);
            --text-color: #e2e6ea;
            --accent-color: #66b3ff;
            --form-bg: #495057;
            --form-border: #6c757d;
            --form-focus-bg: #495057;
            --form-focus-shadow: 0 0 0 0.25rem rgba(102, 179, 255, 0.25);
            --table-bg: #495057;
            --table-border: #6c757d;
            --table-hover-bg: #5a6268;
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

        .history-container {
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

        .table {
            color: var(--text-color);
            background: var(--table-bg);
        }

        .table th,
        .table td {
            background: var(--table-bg);
            border-color: var(--table-border);
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        .table-hover tbody tr:hover {
            background: var(--table-hover-bg);
        }

        .btn-accent {
            background: var(--accent-color);
            border: 1px solid var(--accent-color);
            color: white;
            transition: all 0.3s ease;
        }

        .btn-accent:hover {
            background: var(--accent-color);
            color: white;
            opacity: 0.8;
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

        .search-container {
            margin-bottom: 20px;
        }

        .form-control {
            background: var(--form-bg);
            border: 1px solid var(--form-border);
            color: var(--text-color);
            transition: background 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }

        .form-control:focus {
            background: var(--form-focus-bg);
            border-color: var(--accent-color);
            box-shadow: var(--form-focus-shadow);
            color: var(--text-color);
        }

        .form-control::placeholder {
            color: var(--text-color);
            opacity: 0.7;
        }
    </style>
</head>
<body class="dark-theme">
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="history-container">
            <h2 class="text-center mb-4 main-text">Purchase Order History</h2>

            <?php if (isset($_GET['status'])): ?>
                <?php if ($_GET['status'] == 'success'): ?>
                    <div class="message-box success">
                        <strong>Success!</strong> Purchase Order <?php echo isset($_GET['po']) ? htmlspecialchars($_GET['po']) : ''; ?> generated successfully!
                    </div>
                <?php elseif ($_GET['status'] == 'error'): ?>
                    <div class="message-box error">
                        <strong>Error:</strong> <?php echo isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'An error occurred.'; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="search-container">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search by PO Number, Seller, or Buyer...">
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="purchase_order.php" class="btn btn-accent">
                            <i class="fas fa-plus"></i> Create New PO
                        </a>
                        <a href="delete_purchase_orders.php" class="btn btn-danger ms-2">
                            <i class="fas fa-trash"></i> Delete All POs
                        </a>
                    </div>
                </div>
            </div>

            <?php if (empty($purchase_orders)): ?>
                <div class="text-center">
                    <p class="main-text">No purchase orders found. <a href="purchase_order.php">Generate your first purchase order</a>.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="poTable">
                        <thead>
                            <tr>
                                <th>PO Number</th>
                                <th>Seller</th>
                                <th>PO Date</th>
                                <th>Total Amount</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($purchase_orders as $po): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($po['po_number']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($po['seller_name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($po['seller_company']); ?></small>
                                    </td>
                                    <td><?php echo date('d-m-Y', strtotime($po['po_date'])); ?></td>
                                    <td><strong>₹<?php echo number_format($po['total_amount'], 2); ?></strong></td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($po['created_at'])); ?></td>
                                    <td>
                                        <a href="view_purchase_order.php?id=<?php echo $po['id']; ?>" 
                                           class="btn btn-sm btn-success" 
                                           title="View Purchase Order Details">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <form method="post" action="delete_purchase_order.php" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this purchase order?');">
                                            <input type="hidden" name="po_id" value="<?php echo $po['id']; ?>">
                                            <input type="hidden" name="po_number" value="<?php echo htmlspecialchars($po['po_number']); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
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
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="includes/sidebar.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize sidebar
            initializeSidebar();

            // Search functionality
            $('#searchInput').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $('#poTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        });
    </script>
</body>
</html>

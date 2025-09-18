<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

// Get purchase order ID from URL
$po_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($po_id <= 0) {
    header("Location: purchase_order_history.php?status=error&message=" . urlencode("Invalid purchase order ID."));
    exit;
}

// Fetch purchase order details
$sql = "SELECT * FROM purchase_orders WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $po_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    header("Location: purchase_order_history.php?status=error&message=" . urlencode("Purchase order not found."));
    exit;
}

$po = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
mysqli_close($conn);

// Decode materials data
$materials_data = json_decode($po['materials_data'], true);
if (!$materials_data) {
    $materials_data = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order Details - <?php echo htmlspecialchars($po['po_number']); ?></title>
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
            --table-hover-bg: #f8f9fa;
            --section-bg: #f8f9fa;
        }

        body.dark-theme {
            --background-color: rgb(133, 141, 148);
            --text-color: #e2e6ea;
            --accent-color: #66b3ff;
            --form-bg: #495057;
            --form-border: #6c757d;
            --table-bg: #495057;
            --table-border: #6c757d;
            --table-hover-bg: #5a6268;
            --section-bg: #495057;
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

        .po-details-container {
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

        .section-header {
            background: var(--section-bg);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid var(--accent-color);
        }

        .info-row {
            margin-bottom: 0.5rem;
        }

        .info-label {
            font-weight: bold;
            color: var(--accent-color);
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

        .total-section {
            background: var(--section-bg);
            padding: 1.5rem;
            border-radius: 8px;
            border: 2px solid var(--accent-color);
        }

        .party-details {
            display: flex;
            gap: 2rem;
        }

        .party-section {
            flex: 1;
            background: var(--section-bg);
            padding: 1.5rem;
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            .party-details {
                flex-direction: column;
                gap: 1rem;
            }
        }

        .print-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }

        @media print {
            .print-button,
            .btn,
            .sidebar {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            body {
                background: white !important;
                color: black !important;
            }
            
            .po-details-container,
            .section-header,
            .party-section,
            .total-section {
                background: white !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="dark-theme">
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="po-details-container">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="main-text mb-0">Purchase Order Details</h2>
                    <div>
                        <a href="purchase_order_history.php" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left"></i> Back to History
                        </a>
                        <button onclick="window.print()" class="btn btn-accent">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>

                <!-- Basic PO Information -->
                <div class="section-header">
                    <h4 class="main-text mb-3">Purchase Order Information</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">PO Number:</span>
                                <span><?php echo htmlspecialchars($po['po_number']); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">PO Date:</span>
                                <span><?php echo date('d-m-Y', strtotime($po['po_date'])); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Created At:</span>
                                <span><?php echo date('d-m-Y H:i', strtotime($po['created_at'])); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Last Updated:</span>
                                <span><?php echo date('d-m-Y H:i', strtotime($po['updated_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seller Details -->
                <div class="section-header">
                    <h4 class="main-text mb-3">Seller Details</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Name:</span>
                                <span><?php echo htmlspecialchars($po['seller_name']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Company:</span>
                                <span><?php echo htmlspecialchars($po['seller_company']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Address:</span>
                                <span><?php echo nl2br(htmlspecialchars($po['seller_address'])); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Phone:</span>
                                <span><?php echo htmlspecialchars($po['seller_phone']); ?></span>
                            </div>
                            <?php if (!empty($po['seller_gst'])): ?>
                            <div class="info-row">
                                <span class="info-label">GST ID:</span>
                                <span><?php echo htmlspecialchars($po['seller_gst']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($po['seller_email'])): ?>
                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span><?php echo htmlspecialchars($po['seller_email']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Materials Section -->
                <div class="section-header">
                    <h4 class="main-text mb-3">Materials</h4>
                </div>
                
                <?php if (!empty($materials_data)): ?>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>SL.NO</th>
                                <th>Name/Description</th>
                                <th>HSN Code</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sl_no = 1;
                            foreach ($materials_data as $item): 
                                $item_total = floatval($item['price_per_unit']) * intval($item['quantity']);
                            ?>
                            <tr>
                                <td><?php echo $sl_no++; ?></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['hsn_code']); ?></td>
                                <td><?php echo intval($item['quantity']); ?></td>
                                <td>₹<?php echo number_format(floatval($item['price_per_unit']), 2); ?></td>
                                <td>₹<?php echo number_format($item_total, 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                    <strong>No materials data found.</strong>
                </div>
                <?php endif; ?>

                <!-- Total Section -->
                <div class="total-section">
                    <h4 class="main-text mb-3">Amount Details</h4>
                    <div class="row">
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Subtotal:</strong></td>
                                    <td class="text-end">₹<?php echo number_format($po['subtotal'], 2); ?></td>
                                </tr>
                                <?php if ($po['cgst_rate'] > 0): ?>
                                <tr>
                                    <td><strong>CGST (<?php echo $po['cgst_rate']; ?>%):</strong></td>
                                    <td class="text-end">₹<?php echo number_format($po['cgst_amount'], 2); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($po['sgst_rate'] > 0): ?>
                                <tr>
                                    <td><strong>SGST (<?php echo $po['sgst_rate']; ?>%):</strong></td>
                                    <td class="text-end">₹<?php echo number_format($po['sgst_amount'], 2); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($po['igst_rate'] > 0): ?>
                                <tr>
                                    <td><strong>IGST (<?php echo $po['igst_rate']; ?>%):</strong></td>
                                    <td class="text-end">₹<?php echo number_format($po['igst_amount'], 2); ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr style="background-color: var(--accent-color); color: white;">
                                    <td><strong>Total Amount:</strong></td>
                                    <td class="text-end"><strong>₹<?php echo number_format($po['total_amount'], 2); ?></strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="mt-4">
                    <h5 class="main-text mb-3">Terms and Conditions:</h5>
                    <ol class="main-text">
                        <li>Please supply the materials as per specifications mentioned above.</li>
                        <li>Delivery should be made within the agreed timeframe.</li>
                        <li>Payment terms as per agreement.</li>
                        <li>All disputes subject to Bangalore jurisdiction.</li>
                    </ol>
                </div>

                <!-- Signature Section -->
                <div class="text-end mt-4 pt-4" style="border-top: 1px solid var(--form-border);">
                    <div style="border-top: 1px solid var(--text-color); width: 200px; margin-left: auto; padding-top: 0.5rem;">
                        <p class="main-text mb-1"><strong>Authorized Signature</strong></p>
                        <p class="main-text"><strong>Madhu PaperBags</strong></p>
                    </div>
                </div>
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
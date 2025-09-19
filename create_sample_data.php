<?php
require_once "config/database.php";

echo "Creating Sample Data for Profit/Loss Testing\n";
echo "=============================================\n\n";

// Insert sample daily expenses
$expense_data = [
    ['2024-10-10', 1700, 5000, 1000, 500, 200, 300, 150],
    ['2024-10-11', 800, 5000, 1200, 600, 100, 250, 200],
    ['2024-10-12', 2070, 5000, 900, 400, 300, 150, 100]
];

$expense_sql = "INSERT INTO daily_expenses (expense_date, purchase_order, salary, printing_services, petrol_expense, other_expense_1, other_expense_2, other_expense_3) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$expense_stmt = mysqli_prepare($conn, $expense_sql);

echo "Inserting Daily Expenses:\n";
foreach ($expense_data as $expense) {
    mysqli_stmt_bind_param($expense_stmt, "sddddddd", $expense[0], $expense[1], $expense[2], $expense[3], $expense[4], $expense[5], $expense[6], $expense[7]);
    if (mysqli_stmt_execute($expense_stmt)) {
        $total = $expense[1] + $expense[2] + $expense[3] + $expense[4] + $expense[5] + $expense[6] + $expense[7];
        echo "✓ " . $expense[0] . " - Total Expense: ₹" . number_format($total, 2) . "\n";
    }
}
mysqli_stmt_close($expense_stmt);

// Insert sample invoices
$invoice_data = [
    ['INV001', 'Customer A', '2024-10-10', 'invoices/Invoice_INV001.pdf', 450.00, 9.00, 9.00, 0.00, 20.25, 20.25, 0.00, 500.00],
    ['INV002', 'Customer B', '2024-10-10', 'invoices/Invoice_INV002.pdf', 90.00, 9.00, 9.00, 0.00, 4.05, 4.05, 0.00, 100.00],
    ['INV003', 'Customer C', '2024-10-10', 'invoices/Invoice_INV003.pdf', 270.00, 9.00, 9.00, 0.00, 12.15, 12.15, 0.00, 300.00],
    ['INV004', 'Customer D', '2024-10-10', 'invoices/Invoice_INV004.pdf', 477.00, 9.00, 9.00, 0.00, 21.47, 21.47, 0.00, 530.00],
    ['INV005', 'Customer E', '2024-10-10', 'invoices/Invoice_INV005.pdf', 243.00, 9.00, 9.00, 0.00, 10.94, 10.94, 0.00, 270.00],
    
    ['INV006', 'Customer F', '2024-10-11', 'invoices/Invoice_INV006.pdf', 90.00, 9.00, 9.00, 0.00, 4.05, 4.05, 0.00, 100.00],
    ['INV007', 'Customer G', '2024-10-11', 'invoices/Invoice_INV007.pdf', 450.00, 9.00, 9.00, 0.00, 20.25, 20.25, 0.00, 500.00],
    ['INV008', 'Customer H', '2024-10-11', 'invoices/Invoice_INV008.pdf', 180.00, 9.00, 9.00, 0.00, 8.10, 8.10, 0.00, 200.00],
    
    ['INV009', 'Customer I', '2024-10-12', 'invoices/Invoice_INV009.pdf', 450.00, 9.00, 9.00, 0.00, 20.25, 20.25, 0.00, 500.00],
    ['INV010', 'Customer J', '2024-10-12', 'invoices/Invoice_INV010.pdf', 90.00, 9.00, 9.00, 0.00, 4.05, 4.05, 0.00, 100.00],
    ['INV011', 'Customer K', '2024-10-12', 'invoices/Invoice_INV011.pdf', 270.00, 9.00, 9.00, 0.00, 12.15, 12.15, 0.00, 300.00],
    ['INV012', 'Customer L', '2024-10-12', 'invoices/Invoice_INV012.pdf', 540.00, 9.00, 9.00, 0.00, 24.30, 24.30, 0.00, 600.00],
    ['INV013', 'Customer M', '2024-10-12', 'invoices/Invoice_INV013.pdf', 45.00, 9.00, 9.00, 0.00, 2.03, 2.03, 0.00, 50.00],
    ['INV014', 'Customer N', '2024-10-12', 'invoices/Invoice_INV014.pdf', 468.00, 9.00, 9.00, 0.00, 21.06, 21.06, 0.00, 520.00]
];

$invoice_sql = "INSERT INTO invoices (invoice_number, customer_name, invoice_date, pdf_path, subtotal, cgst_rate, sgst_rate, igst_rate, cgst_amount, sgst_amount, igst_amount, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$invoice_stmt = mysqli_prepare($conn, $invoice_sql);

echo "\nInserting Sample Invoices:\n";
$daily_totals = [];

foreach ($invoice_data as $invoice) {
    mysqli_stmt_bind_param($invoice_stmt, "ssssdddddddd", $invoice[0], $invoice[1], $invoice[2], $invoice[3], $invoice[4], $invoice[5], $invoice[6], $invoice[7], $invoice[8], $invoice[9], $invoice[10], $invoice[11]);
    if (mysqli_stmt_execute($invoice_stmt)) {
        $date = $invoice[2];
        if (!isset($daily_totals[$date])) {
            $daily_totals[$date] = 0;
        }
        $daily_totals[$date] += $invoice[11];
        echo "✓ " . $invoice[0] . " - " . $invoice[1] . " - ₹" . number_format($invoice[11], 2) . "\n";
    }
}
mysqli_stmt_close($invoice_stmt);

echo "\nDaily Income Totals:\n";
foreach ($daily_totals as $date => $total) {
    echo "✓ " . $date . " - Total Income: ₹" . number_format($total, 2) . "\n";
}

mysqli_close($conn);
echo "\nSample data created successfully! ✓\n";
echo "You can now test the profit/loss system with date range: 2024-10-10 to 2024-10-12\n";
?>
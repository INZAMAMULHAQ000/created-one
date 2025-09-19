<?php
require_once "config/database.php";

echo "Testing Profit/Loss Calculation System\n";
echo "=====================================\n\n";

// Test date range
$start_date = '2024-10-10';
$end_date = '2024-10-12';

echo "Testing with date range: $start_date to $end_date\n\n";

// Test expenses query
$expenses = [];
$total_expenses = 0;
$sql = "SELECT expense_date, purchase_order, salary, printing_services, petrol_expense, other_expense_1, other_expense_2, other_expense_3 FROM daily_expenses WHERE expense_date BETWEEN ? AND ? ORDER BY expense_date DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $expenses[] = $row;
            $total_expenses += $row['purchase_order'] + $row['salary'] + $row['printing_services'] + $row['petrol_expense'] + $row['other_expense_1'] + $row['other_expense_2'] + $row['other_expense_3'];
        }
    }
    mysqli_stmt_close($stmt);
    echo "✓ Expenses Query: Found " . count($expenses) . " expense records\n";
    echo "✓ Total Expenses: ₹" . number_format($total_expenses, 2) . "\n\n";
} else {
    echo "✗ Expenses Query: Failed to prepare statement\n";
}

// Test income query
$invoices = [];
$total_income = 0;
$income_sql = "SELECT invoice_number, customer_name, invoice_date, total_amount FROM invoices WHERE invoice_date BETWEEN ? AND ? ORDER BY invoice_date DESC";

$income_stmt = mysqli_prepare($conn, $income_sql);
if ($income_stmt) {
    mysqli_stmt_bind_param($income_stmt, "ss", $start_date, $end_date);
    mysqli_stmt_execute($income_stmt);
    $income_result = mysqli_stmt_get_result($income_stmt);
    
    if ($income_result) {
        while ($row = mysqli_fetch_assoc($income_result)) {
            $invoices[] = $row;
            $total_income += $row['total_amount'];
        }
    }
    mysqli_stmt_close($income_stmt);
    echo "✓ Income Query: Found " . count($invoices) . " invoice records\n";
    echo "✓ Total Income: ₹" . number_format($total_income, 2) . "\n\n";
} else {
    echo "✗ Income Query: Failed to prepare statement\n";
}

// Calculate profit/loss
$profit_loss = $total_income - $total_expenses;
$is_profit = $profit_loss >= 0;

echo "PROFIT/LOSS CALCULATION:\n";
echo "------------------------\n";
echo "Total Income:    ₹" . number_format($total_income, 2) . "\n";
echo "Total Expenses:  ₹" . number_format($total_expenses, 2) . "\n";
echo "Difference:      ₹" . number_format(abs($profit_loss), 2) . "\n";
echo "Status:          " . ($is_profit ? 'PROFIT' : 'LOSS') . "\n\n";

if (count($invoices) > 0) {
    echo "Sample Invoice Data:\n";
    echo "-------------------\n";
    foreach (array_slice($invoices, 0, 3) as $invoice) {
        echo "- " . $invoice['invoice_date'] . " | " . $invoice['invoice_number'] . " | " . $invoice['customer_name'] . " | ₹" . number_format($invoice['total_amount'], 2) . "\n";
    }
    if (count($invoices) > 3) {
        echo "... and " . (count($invoices) - 3) . " more\n";
    }
}

mysqli_close($conn);
echo "\nTest completed successfully! ✓\n";
?>
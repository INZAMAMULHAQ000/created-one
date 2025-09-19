<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "config/database.php";
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

if(!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

if (!isset($_POST['start_date']) || !isset($_POST['end_date'])) {
    die("Error: Please select a date range.");
}

$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

// Fetch expenses from the database within the date range
$expenses = [];
$total_expenses = 0;
$sql = "SELECT expense_date, purchase_order, salary, printing_services, petrol_expense, other_expense_1, other_expense_2, other_expense_3 FROM daily_expenses WHERE expense_date BETWEEN ? AND ? ORDER BY expense_date ASC";

$stmt = mysqli_prepare($conn, $sql);
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

// Fetch income from invoices within the date range
$invoices = [];
$total_income = 0;
$income_sql = "SELECT invoice_number, customer_name, invoice_date, total_amount FROM invoices WHERE invoice_date BETWEEN ? AND ? ORDER BY invoice_date ASC";

$income_stmt = mysqli_prepare($conn, $income_sql);
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

// Calculate profit or loss
$profit_loss = $total_income - $total_expenses;
$is_profit = $profit_loss >= 0;
mysqli_close($conn);

// Initialize Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$options->set('isFontSubsettingEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);

$html_expense_rows = '';
foreach ($expenses as $expense) {
    $html_expense_rows .= '
        <tr>
            <td>' . htmlspecialchars((new DateTime($expense['expense_date']))->format('d-m-Y')) . '</td>
            <td>' . htmlspecialchars($expense['purchase_order']) . '</td>
            <td>' . htmlspecialchars($expense['salary']) . '</td>
            <td>' . htmlspecialchars($expense['printing_services']) . '</td>
            <td>' . htmlspecialchars($expense['petrol_expense']) . '</td>
            <td>' . htmlspecialchars($expense['other_expense_1']) . '</td>
            <td>' . htmlspecialchars($expense['other_expense_2']) . '</td>
            <td>' . htmlspecialchars($expense['other_expense_3']) . '</td>
        </tr>';
}

$html_income_rows = '';
foreach ($invoices as $invoice) {
    $html_income_rows .= '
        <tr>
            <td>' . htmlspecialchars((new DateTime($invoice['invoice_date']))->format('d-m-Y')) . '</td>
            <td>' . htmlspecialchars($invoice['invoice_number']) . '</td>
            <td>' . htmlspecialchars($invoice['customer_name']) . '</td>
            <td>₹' . number_format($invoice['total_amount'], 2) . '</td>
        </tr>';
}

$display_total_expenses = number_format($total_expenses, 2);
$display_total_income = number_format($total_income, 2);
$display_profit_loss = number_format(abs($profit_loss), 2);
$profit_loss_text = $is_profit ? 'Profit' : 'Loss';
$profit_loss_color = $is_profit ? '#28a745' : '#dc3545';

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Profit & Loss Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
            font-size: 10pt;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total {
            text-align: right;
            font-weight: bold;
            font-size: 12pt;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Profit & Loss Report</h1>
        <p>Date Range: {$start_date} to {$end_date}</p>
        <div style="margin-top: 15px; background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
            <div style="display: flex; justify-content: space-around; margin-bottom: 10px;">
                <div><strong>Total Income:</strong> ₹{$display_total_income}</div>
                <div><strong>Total Expenses:</strong> ₹{$display_total_expenses}</div>
                <div style="color: {$profit_loss_color};"><strong>{$profit_loss_text}:</strong> ₹{$display_profit_loss}</div>
            </div>
        </div>
    </div>

    <h3>Income from Billing</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice Number</th>
                <th>Customer Name</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            {$html_income_rows}
        </tbody>
    </table>

    <h3>Daily Expenses</h3>
    <table>
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
            {$html_expense_rows}
        </tbody>
    </table>

    <div class="total">
        <p>Total Income: ₹{$display_total_income}</p>
        <p>Total Expenses: ₹{$display_total_expenses}</p>
        <hr style="margin: 10px 0;">
        <p style="color: {$profit_loss_color}; font-size: 14pt;">Net {$profit_loss_text}: ₹{$display_profit_loss}</p>
    </div>

</body>
</html>
HTML;

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$dompdf->stream("Profit_Loss_Report_{$start_date}_to_{$end_date}.pdf", ["Attachment" => true]);

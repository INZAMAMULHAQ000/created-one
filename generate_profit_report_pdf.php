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
$sql = "SELECT expense_date, purchase_order, salary, printing_services, petrol_expense, other_expense FROM daily_expenses WHERE expense_date BETWEEN ? AND ? ORDER BY expense_date ASC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $expenses[] = $row;
        $total_expenses += $row['salary'] + $row['printing_services'] + $row['petrol_expense'];
    }
}
mysqli_stmt_close($stmt);
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
            <td>' . htmlspecialchars($expense['other_expense']) . '</td>
        </tr>';
}

$display_total_expenses = number_format($total_expenses, 2);

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Overall Profit Report</title>
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
        <h1>Overall Profit Report</h1>
        <p>Date Range: {$start_date} to {$end_date}</p>
    </div>

    <table>
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
            {$html_expense_rows}
        </tbody>
    </table>

    <div class="total">
        <p>Grand Total Expenses: ₹{$display_total_expenses}</p>
    </div>

</body>
</html>
HTML;

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$dompdf->stream("Overall_Profit_Report_{$start_date}_to_{$end_date}.pdf", ["Attachment" => true]);

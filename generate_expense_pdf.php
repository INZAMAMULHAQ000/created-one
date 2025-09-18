<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "config/database.php";
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;
use mysqli_sql_exception;

if(!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

$expense_date = $_POST['expense_date'];

// Check if an expense report for this date already exists
$stmt = mysqli_prepare($conn, "SELECT id FROM daily_expenses WHERE expense_date = ?");
mysqli_stmt_bind_param($stmt, "s", $expense_date);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) > 0) {
    header("Location: daily_expenses.php?error=" . urlencode("An expense report for this date already exists."));
    exit();
}
mysqli_stmt_close($stmt);

$purchase_order = htmlspecialchars($_POST['purchase_order']);
$salary = floatval($_POST['salary']);
$printing_services = floatval($_POST['printing_services']);
$petrol_expense = floatval($_POST['petrol_expense']);
$other_expense = nl2br(htmlspecialchars($_POST['other_expense']));

$total_expense = $salary + $printing_services + $petrol_expense;

$display_total_expense = number_format($total_expense, 2);
$display_salary = number_format($salary, 2);
$display_printing_services = number_format($printing_services, 2);
$display_petrol_expense = number_format($petrol_expense, 2);

// Initialize Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$options->set('isFontSubsettingEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daily Expense Report</title>
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Expense Report</h1>
        <p>Date: {$expense_date}</p>
    </div>

    <table>
        <tr>
            <th>Expense Category</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Purchase Order</td>
            <td>{$purchase_order}</td>
        </tr>
        <tr>
            <td>Salary</td>
            <td>₹{$display_salary}</td>
        </tr>
        <tr>
            <td>Printing/ Other services</td>
            <td>₹{$display_printing_services}</td>
        </tr>
        <tr>
            <td>Petrol Expense</td>
            <td>₹{$display_petrol_expense}</td>
        </tr>
        <tr>
            <td>Other Expense</td>
            <td>{$other_expense}</td>
        </tr>
        <tr>
            <th>Total Expense</th>
            <th>₹{$display_total_expense}</th>
        </tr>
    </table>

</body>
</html>
HTML;

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

if (!file_exists('expenses')) {
    mkdir('expenses', 0777, true);
}

$pdf_filename = 'expenses/Expense_' . $expense_date . '.pdf';
$output_file = __DIR__ . '/' . $pdf_filename;

file_put_contents($output_file, $dompdf->output());

$stmt = mysqli_prepare($conn, "INSERT INTO daily_expenses (expense_date, purchase_order, salary, printing_services, petrol_expense, other_expense, pdf_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssddsss", $expense_date, $purchase_order, $salary, $printing_services, $petrol_expense, $_POST['other_expense'], $pdf_filename);

try {
    if (mysqli_stmt_execute($stmt)) {
        header("Location: expense_history.php?status=success&date=" . urlencode($expense_date));
        exit();
    }
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 1062) {
        header("Location: daily_expenses.php?error=" . urlencode("An expense report for this date already exists."));
        exit();
    } else {
        error_log("Error inserting expense into database: " . $e->getMessage());
        die("Error generating expense report. Please try again.");
    }
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

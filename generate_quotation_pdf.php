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

// Read QR code image and convert to base64
$qr_code_file = __DIR__ . '/QR.jpeg';
$qr_code_data = '';
if (file_exists($qr_code_file)) {
    $qr_code_type = pathinfo($qr_code_file, PATHINFO_EXTENSION);
    $qr_code_data = 'data:image/' . $qr_code_type . ';base64,' . base64_encode(file_get_contents($qr_code_file));
}

// Read Sun logo image and convert to base64
$sun_logo_file = __DIR__ . '/Sun.jpeg';
$sun_logo_data = '';
if (file_exists($sun_logo_file)) {
    $sun_logo_type = pathinfo($sun_logo_file, PATHINFO_EXTENSION);
    $sun_logo_data = 'data:image/' . $sun_logo_type . ';base64,' . base64_encode(file_get_contents($sun_logo_file));
}

$selected_materials_data = json_decode($_POST['selected_materials_data'], true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("Error: Invalid material data provided.");
}

$html_material_rows = '';
$item_count = 1;
$total_price = 0;

foreach ($selected_materials_data as $item) {
    $item_name = htmlspecialchars($item['name']);
    $item_hsn_code = htmlspecialchars($item['hsn_code']);
    $item_quantity = intval($item['quantity']);
    $item_price_per_unit = floatval($item['price_per_unit']);
    $item_subtotal = $item_price_per_unit * $item_quantity;
    $total_price += $item_subtotal;

    $display_price_per_unit = number_format($item_price_per_unit, 2);
    $display_item_subtotal = number_format($item_subtotal, 2);

    $html_material_rows .= '
            <tr>
                <td>' . $item_count++ . '</td>
                <td>' . $item_name . '</td>
                <td>' . $item_hsn_code . '</td>
                <td>' . $item_quantity . '</td>
                <td>₹' . $display_price_per_unit . '</td>
                <td>₹' . $display_item_subtotal . '</td>
            </tr>';
}

// Office Bio
$office_name = htmlspecialchars($_POST['office_name']);
$office_address = nl2br(htmlspecialchars($_POST['office_address']));
$office_phone = htmlspecialchars($_POST['office_phone']);
$office_gst = htmlspecialchars($_POST['office_gst']);
$office_owner = htmlspecialchars($_POST['office_owner']);

// Quotation Headlines
$quotation_number = htmlspecialchars($_POST['quotation_number']);
$date_issue = (new DateTime($_POST['date_issue']))->format('d-m-Y');
$date_submission = (new DateTime($_POST['date_submission']))->format('d-m-Y');
$contact_person = htmlspecialchars($_POST['contact_person']);

// Customer Details
$customer_name = htmlspecialchars($_POST['customer_name']);
$customer_company = htmlspecialchars($_POST['customer_company']);
$customer_address = nl2br(htmlspecialchars($_POST['customer_address']));
$customer_phone = htmlspecialchars($_POST['customer_phone']);

// Terms and Footer
$terms_conditions = nl2br(htmlspecialchars($_POST['terms_conditions']));
$quote_prepared_by = htmlspecialchars($_POST['quote_prepared_by']);
$quote_approved_by = htmlspecialchars($_POST['quote_approved_by']);

$display_total = number_format($total_price, 2);

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
    <title>Sales Quotation</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 10pt;
        }
        .header {
            text-align: center;
            color: #0066cc;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #0066cc;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{$office_name}</h1>
        <p>{$office_address}<br>
        Ph: {$office_phone} | Email: {$office_email}<br>
        GSTIN: {$office_gst}<br>
        Owner: {$office_owner}</p>
    </div>

    <h3>Sales Quotation</h3>
    <table>
        <tr>
            <td><strong>Quotation No:</strong> {$quotation_number}</td>
            <td><strong>Date of Issue:</strong> {$date_issue}</td>
        </tr>
        <tr>
            <td><strong>Contact Person:</strong> {$contact_person}</td>
            <td><strong>Date of Submission:</strong> {$date_submission}</td>
        </tr>
    </table>

    <h4>To:</h4>
    <p>
        <strong>Customer:</strong> {$customer_name}<br>
        <strong>Company:</strong> {$customer_company}<br>
        <strong>Address:</strong> {$customer_address}<br>
        <strong>Phone:</strong> {$customer_phone}
    </p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Material Description</th>
                <th>HSN Code</th>
                <th>Quantity</th>
                <th>Price/Unit</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            {$html_material_rows}
        </tbody>
    </table>

    <p style="text-align: right;"><strong>Total:</strong> ₹{$display_total}</p>

    <div>
        <h4>Terms & Conditions:</h4>
        <p>{$terms_conditions}</p>
    </div>

    <table style="margin-top: 50px; border: none;">
        <tr style="border: none;">
            <td style="border: none; text-align: left;">_________________________<br>Quote Prepared By: {$quote_prepared_by}</td>
            <td style="border: none; text-align: right;">_________________________<br>Quote Approved By: {$quote_approved_by}</td>
        </tr>
    </table>

</body>
</html>
HTML;

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

if (!file_exists('quotations')) {
    mkdir('quotations', 0777, true);
}

$pdf_filename = 'quotations/Quotation_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $quotation_number) . '.pdf';
$output_file = __DIR__ . '/' . $pdf_filename;

file_put_contents($output_file, $dompdf->output());

$stmt = mysqli_prepare($conn, "INSERT INTO sales_quotations (quotation_number, customer_name, quotation_date, pdf_path) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssss", $quotation_number, $customer_name, $_POST['date_issue'], $pdf_filename);

try {
    if (mysqli_stmt_execute($stmt)) {
        header("Location: quotation_history.php?status=success&quotation=" . urlencode($quotation_number));
        exit();
    }
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 1062) {
        header("Location: sales_quotation.php?status=error&message=" . urlencode("Quotation number '{$quotation_number}' already exists. Please use a different one."));
        exit();
    } else {
        error_log("Error inserting quotation into database: " . $e->getMessage());
        die("Error generating quotation. Please try again.");
    }
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

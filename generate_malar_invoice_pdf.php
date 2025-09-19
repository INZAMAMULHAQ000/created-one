<?php
error_reporting(E_ALL); // Display all errors for debugging
ini_set('display_errors', 1); // Make sure errors are displayed

session_start();
require_once "config/database.php";
require_once 'vendor/autoload.php';
require_once 'includes/number_to_words.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Read logo image and convert to base64
$logo_file = __DIR__ . '/logo.png';
$logo_data = '';
if (file_exists($logo_file)) {
    $logo_type = pathinfo($logo_file, PATHINFO_EXTENSION);
    $logo_data = 'data:image/' . $logo_type . ';base64,' . base64_encode(file_get_contents($logo_file));
} else {
    error_log("Logo image not found at: " . $logo_file);
}

// Read QR code image and convert to base64
$qr_code_file = __DIR__ . '/QR.jpg';
$qr_code_data = '';
if (file_exists($qr_code_file)) {
    $qr_code_type = pathinfo($qr_code_file, PATHINFO_EXTENSION);
    $qr_code_data = 'data:image/' . $qr_code_type . ';base64,' . base64_encode(file_get_contents($qr_code_file));
} else {
    error_log("QR code image not found at: " . $qr_code_file);
}

if(!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

// Validate required POST data
if (!isset($_POST['transport']) || empty($_POST['transport'])) {
    die("Error: Mode of Transport is required.");
}

if (!isset($_POST['selected_materials_data']) || empty($_POST['selected_materials_data'])) {
    die("Error: No materials selected.");
}

$selected_materials_data = json_decode($_POST['selected_materials_data'], true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("Error: Invalid material data provided.");
}

if (empty($selected_materials_data)) {
    die("Error: No materials selected for billing.");
}

// Fetch transport details
$transport_id = $_POST['transport'];
$transport_query = "SELECT name FROM transports WHERE id = '$transport_id'";
$transport_result = mysqli_query($conn, $transport_query);
$transport = mysqli_fetch_assoc($transport_result);

$total_price_before_gst = 0;
$html_material_rows = '';
$item_count = 1;

foreach ($selected_materials_data as $item) {
    $item_id = $item['id'];
    $item_name = htmlspecialchars($item['name']);
    $item_hsn_code = htmlspecialchars($item['hsn_code']);
    $item_price_per_unit = floatval($item['price_per_unit']);
    $item_quantity = intval($item['quantity']);
    $item_subtotal = $item_price_per_unit * $item_quantity;
    $total_price_before_gst += $item_subtotal;

    // Pre-format price and subtotal for display
    $display_price_per_unit = number_format($item_price_per_unit, 2);
    $display_item_subtotal = number_format($item_subtotal, 2);

    $html_material_rows .= '
            <tr>
                <td style="text-align: center;">' . $item_count++ . '</td>
                <td>' . $item_name . '</td>
                <td style="text-align: center;">' . $item_hsn_code . '</td>
                <td style="text-align: center;">' . $item_quantity . '</td>
                <td style="text-align: right;">₹' . $display_price_per_unit . '</td>
                <td style="text-align: right;">₹' . $display_item_subtotal . '</td>
            </tr>';
}

$price = $total_price_before_gst; // Rename for clarity, this is the total material price before any GST

$cgst_rate = isset($_POST['cgst_rate']) ? floatval($_POST['cgst_rate']) : 0;
$sgst_rate = isset($_POST['sgst_rate']) ? floatval($_POST['sgst_rate']) : 0;
$igst_rate = isset($_POST['igst_rate']) ? floatval($_POST['igst_rate']) : 0;

// Check if any GST is applicable
$has_gst = ($cgst_rate > 0 || $sgst_rate > 0 || $igst_rate > 0);

$cgst_amount = ($price * $cgst_rate) / 100;
$sgst_amount = ($price * $sgst_rate) / 100;
$igst_amount = ($price * $igst_rate) / 100;
$total = $price + $cgst_amount + $sgst_amount + $igst_amount;

// Pre-process GST amounts for display
$display_cgst_amount = number_format($cgst_amount, 2);
$display_sgst_amount = number_format($sgst_amount, 2);
$display_igst_amount = number_format($igst_amount, 2);

// New: Read customer address and phone number
$customer_name = $_POST['customer_name'];
$customer_address = $_POST['customer_address'];
$customer_phone = $_POST['customer_phone'];

// Pre-process customer details for display
$display_customer_name = htmlspecialchars($customer_name);
$display_customer_address = nl2br(htmlspecialchars($customer_address));
$display_customer_phone = htmlspecialchars($customer_phone);

// Pre-process total for display
$display_total = number_format($total, 2);
$display_subtotal = number_format($price, 2);

// Convert amount to words
$amount_in_words = amountInWords($total);

// Assign POST values to simpler variables for cleaner HTML embedding
$invoice_number = $_POST['invoice_number'];
$invoice_date_raw = $_POST['date'];
// Convert date to Indian standard (DD-MM-YYYY)
$invoice_date = (new DateTime($invoice_date_raw))->format('d-m-Y');
$party_gstin = $_POST['gstin'];
$po_number = isset($_POST['po_number']) ? htmlspecialchars($_POST['po_number']) : '';
$mode_of_transport = ($transport ? $transport['name'] : '');

// Initialize Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$options->set('isFontSubsettingEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);

// Determine invoice title based on GST
$invoice_title = $has_gst ? 'TAX INVOICE' : 'INVOICE';

// Generate HTML content
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>MALAR PAPER BAGS - Invoice</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 15px;
            font-size: 9pt;
            color: #333;
        }
        
        .header-container {
            width: 100%;
            position: relative;
            height: 100px;
            margin-bottom: 10px;
        }
        
        .logo {
            position: absolute;
            left: 0;
            top: 0;
            width: 80px;
            height: 80px;
        }
        
        .qr-code {
            position: absolute;
            right: 0;
            top: 0;
            width: 80px;
            height: 80px;
        }
        
        .company-address {
            text-align: center;
            margin: 0 100px;
            padding-top: 5px;
        }
        
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        
        .address-line {
            font-size: 9pt;
            line-height: 1.3;
            margin-bottom: 2px;
        }
        
        .website {
            font-size: 9pt;
            font-weight: bold;
            color: #3498db;
            margin-top: 5px;
        }
        
        .invoice-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            background-color: #34495e;
            color: white;
            padding: 8px;
            margin: 15px 0;
        }
        
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        
        .details-table td {
            border: 1px solid #bdc3c7;
            padding: 6px;
            background-color: #ecf0f1;
        }
        
        .details-table td:first-child {
            font-weight: bold;
            width: 20%;
            background-color: #d5dbdb;
        }
        
        .address-section {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .bill-to, .ship-to {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            border: 1px solid #34495e;
            padding: 10px;
            background-color: #f8f9fa;
        }
        
        .bill-to {
            margin-right: 2%;
        }
        
        .address-header {
            font-weight: bold;
            background-color: #34495e;
            color: white;
            padding: 5px;
            margin: -10px -10px 8px -10px;
            text-align: center;
            font-size: 10pt;
        }
        
        .address-content {
            font-size: 9pt;
            line-height: 1.4;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8pt;
        }
        
        .items-table th {
            background-color: #34495e;
            color: white;
            padding: 8px 4px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #2c3e50;
        }
        
        .items-table td {
            border: 1px solid #bdc3c7;
            padding: 6px 4px;
        }
        
        .items-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .totals-section {
            width: 100%;
            margin-bottom: 15px;
        }
        
        .totals-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
            font-size: 9pt;
        }
        
        .totals-table td {
            border: 1px solid #34495e;
            padding: 6px 8px;
        }
        
        .totals-table .label {
            background-color: #ecf0f1;
            font-weight: bold;
            width: 60%;
        }
        
        .totals-table .amount {
            text-align: right;
            width: 40%;
        }
        
        .grand-total {
            background-color: #34495e !important;
            color: white !important;
            font-weight: bold !important;
        }
        
        .amount-words {
            clear: both;
            border: 1px solid #34495e;
            padding: 8px;
            background-color: #f8f9fa;
            margin: 10px 0;
            font-weight: bold;
            font-size: 9pt;
        }
        
        .terms-signature {
            display: table;
            width: 100%;
            margin-top: 20px;
        }
        
        .terms {
            display: table-cell;
            width: 55%;
            vertical-align: top;
            padding-right: 20px;
        }
        
        .signature {
            display: table-cell;
            width: 45%;
            vertical-align: top;
            text-align: center;
        }
        
        .signature-box {
            border: 1px solid #34495e;
            height: 80px;
            margin-bottom: 10px;
            padding-top: 50px;
            background-color: #f8f9fa;
        }
        
        .signature-label {
            font-size: 8pt;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .terms h4 {
            color: #34495e;
            margin-bottom: 8px;
            font-size: 10pt;
        }
        
        .terms ol {
            font-size: 8pt;
            line-height: 1.4;
            padding-left: 15px;
        }
        
        .clearfix {
            clear: both;
        }
    </style>
</head>
<body>
    <!-- Header Section with Logo, Address, and QR Code -->
    <div class="header-container">
        <div class="logo">
            <img src="{$logo_data}" alt="Logo" style="width: 80px; height: 80px;">
        </div>
        
        <div class="company-address">
            <div class="company-name">MALAR PAPER BAGS</div>
            <div class="address-line">16/2, D.R.R Industrial Estate,</div>
            <div class="address-line">Near NCC Head Office,</div>
            <div class="address-line">Ondipudur,</div>
            <div class="address-line">Singanallur,</div>
            <div class="address-line">Coimbatore-641005</div>
            <div class="website">www.malarpaperbags.in</div>
        </div>
        
        <div class="qr-code">
            <img src="{$qr_code_data}" alt="QR Code" style="width: 80px; height: 80px;">
        </div>
    </div>

    <!-- Invoice Title -->
    <div class="invoice-title">{$invoice_title}</div>

    <!-- Invoice Details Table -->
    <table class="details-table">
        <tr>
            <td>Invoice No:</td>
            <td>{$invoice_number}</td>
            <td>Date:</td>
            <td>{$invoice_date}</td>
        </tr>
        <tr>
            <td>PO Number:</td>
            <td>{$po_number}</td>
            <td>Party GSTIN:</td>
            <td>{$party_gstin}</td>
        </tr>
        <tr>
            <td colspan="2">Mode of Transport:</td>
            <td colspan="2">{$mode_of_transport}</td>
        </tr>
    </table>

    <!-- Bill To and Ship To Section -->
    <div class="address-section">
        <div class="bill-to">
            <div class="address-header">BILL TO</div>
            <div class="address-content">
                <strong>{$display_customer_name}</strong><br>
                {$display_customer_address}<br>
                Phone: {$display_customer_phone}
            </div>
        </div>
        
        <div class="ship-to">
            <div class="address-header">SHIP TO</div>
            <div class="address-content">
                <strong>{$display_customer_name}</strong><br>
                {$display_customer_address}<br>
                Phone: {$display_customer_phone}
            </div>
        </div>
    </div>

    <!-- Invoice Details Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th width="6%">S.No</th>
                <th width="40%">Name/Description</th>
                <th width="12%">HSN Code</th>
                <th width="8%">Qty</th>
                <th width="17%">Rate</th>
                <th width="17%">Amount</th>
            </tr>
        </thead>
        <tbody>
            {$html_material_rows}
        </tbody>
    </table>

    <!-- Totals Section -->
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="label">Sub Total:</td>
                <td class="amount">₹{$display_subtotal}</td>
            </tr>
HTML;

// Add GST rows only if they have values greater than 0
if ($cgst_rate > 0) {
    $html .= "
            <tr>
                <td class=\"label\">CGST ({$cgst_rate}%):</td>
                <td class=\"amount\">₹{$display_cgst_amount}</td>
            </tr>";
}

if ($sgst_rate > 0) {
    $html .= "
            <tr>
                <td class=\"label\">SGST ({$sgst_rate}%):</td>
                <td class=\"amount\">₹{$display_sgst_amount}</td>
            </tr>";
}

if ($igst_rate > 0) {
    $html .= "
            <tr>
                <td class=\"label\">IGST ({$igst_rate}%):</td>
                <td class=\"amount\">₹{$display_igst_amount}</td>
            </tr>";
}

$html .= <<<HTML
            <tr class="grand-total">
                <td class="label grand-total">Grand Total:</td>
                <td class="amount grand-total">₹{$display_total}</td>
            </tr>
        </table>
    </div>

    <div class="clearfix"></div>

    <!-- Amount in Words -->
    <div class="amount-words">
        <strong>Amount in Words:</strong> {$amount_in_words}
    </div>

    <!-- Terms and Signature Section -->
    <div class="terms-signature">
        <div class="terms">
            <h4>Terms & Conditions:</h4>
            <ol>
                <li>Goods once sold cannot be taken back or exchanged.</li>
                <li>Our responsibility ceases immediately the goods are delivered or handed over to the carrier.</li>
                <li>Payment should be made within 30 days of invoice date.</li>
                <li>Subject to Coimbatore Jurisdiction.</li>
                <li>All disputes are subject to arbitration.</li>
            </ol>
        </div>
        
        <div class="signature">
            <div class="signature-box">
                <div style="margin-top: 20px;">For MALAR PAPER BAGS</div>
            </div>
            <div class="signature-label">Authorized Signatory</div>
        </div>
    </div>
</body>
</html>
HTML;

// Load HTML content
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render PDF
$dompdf->render();

// Output PDF
$pdf_filename = 'invoices/MALAR_Invoice_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $invoice_number) . '.pdf';
$output_file = __DIR__ . '/' . $pdf_filename;

// Correctly save the PDF to the file system
file_put_contents($output_file, $dompdf->output());

// Temporarily disable display errors to ensure redirect works without raw output
ini_set('display_errors', 0);
error_reporting(0);

// Store invoice details in the database with billing totals
$stmt = mysqli_prepare($conn, "INSERT INTO invoices (invoice_number, customer_name, invoice_date, pdf_path, subtotal, cgst_rate, sgst_rate, igst_rate, cgst_amount, sgst_amount, igst_amount, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssssdddddddd", $invoice_number, $display_customer_name, $invoice_date_raw, $pdf_filename, $price, $cgst_rate, $sgst_rate, $igst_rate, $cgst_amount, $sgst_amount, $igst_amount, $total);

try {
    if (mysqli_stmt_execute($stmt)) {
        // Redirect to customer history page on success
        header("Location: customer_history.php?status=success&invoice=" . urlencode($invoice_number));
        exit();
    }
} catch (mysqli_sql_exception $e) {
    // Check for duplicate entry error (MySQL error code 1062 for UNIQUE constraint violation)
    if ($e->getCode() == 1062) {
        header("Location: billing.php?status=error&message=" . urlencode("Invoice number '{$invoice_number}' already exists. Please use a different one."));
        exit();
    } else {
        error_log("Error inserting invoice into database: " . $e->getMessage());
        die("Error generating invoice. Please try again.");
    }
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
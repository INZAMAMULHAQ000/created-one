<?php
error_reporting(E_ALL); // Display all errors for debugging
ini_set('display_errors', 1); // Make sure errors are displayed

session_start();
require_once "config/database.php";
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;
use mysqli_sql_exception;

// Read QR code image and convert to base64
$qr_code_file = __DIR__ . '/QR.jpg';
$qr_code_data = '';
if (file_exists($qr_code_file)) {
    $qr_code_type = pathinfo($qr_code_file, PATHINFO_EXTENSION);
    $qr_code_data = 'data:image/' . $qr_code_type . ';base64,' . base64_encode(file_get_contents($qr_code_file));
} else {
    // Optionally, handle the case where the file does not exist (e.g., log error, use a placeholder)
    error_log("QR code image not found at: " . $qr_code_file);
}

// Read Sun logo image and convert to base64
$sun_logo_file = __DIR__ . '/Sun.jpeg';
$sun_logo_data = '';
if (file_exists($sun_logo_file)) {
    $sun_logo_type = pathinfo($sun_logo_file, PATHINFO_EXTENSION);
    $sun_logo_data = 'data:image/' . $sun_logo_type . ';base64,' . base64_encode(file_get_contents($sun_logo_file));
} else {
    error_log("Sun logo image not found at: " . $sun_logo_file);
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
                <td>' . $item_count++ . '</td>
                <td>' . $item_name . '</td>
                <td>' . $item_hsn_code . '</td>
                <td>' . $item_quantity . '</td>
                <td>₹' . $display_price_per_unit . '</td>
                <td>₹' . $display_item_subtotal . '</td>
            </tr>';
}

$price = $total_price_before_gst; // Rename for clarity, this is the total material price before any GST

$cgst_rate = isset($_POST['cgst_rate']) ? floatval($_POST['cgst_rate']) : 0;
$sgst_rate = isset($_POST['sgst_rate']) ? floatval($_POST['sgst_rate']) : 0;
$igst_rate = isset($_POST['igst_rate']) ? floatval($_POST['igst_rate']) : 0;

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

// Generate HTML content
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px; /* Restored body padding */
            font-size: 10pt;
        }
        h1 {
            font-size: 20pt;
            margin-bottom: 10px;
        }
        h3 {
            font-size: 14pt;
            margin-bottom: 5px;
        }
        p {
            margin-bottom: 5px;
        }
        .header {
            text-align: center;
            color: #0066cc;
            margin-bottom: 15px;
        }
        .company-details,
        .invoice-details,
        .customer-details,
        .gst-details,
        .total {
            margin-bottom: 15px;
        }
        .company-details {
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
        .terms {
            margin-top: 30px;
            font-size: 8pt;
        }
        .signature {
            text-align: right;
            margin-top: 30px;
            border-top: 1px solid #000;
            width: 200px;
            float: right;
        }
        .qr-code,
        .sun-logo {
            position: absolute;
            width: 80px;
            height: 80px;
            z-index: 10; /* Ensure logos are on top */
        }
        .qr-code {
            top: 20px; /* Absolute position from top */
            right: 20px; /* Absolute position from right */
            margin: 0; /* Remove old margins */
        }
        .sun-logo {
            top: 20px;
            left: 20px;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="sun-logo">
        <img src="{$sun_logo_data}" alt="Sun Logo" style="width: 70px; height: 80px;">
    </div>
    <div class="qr-code">
        <img src="{$qr_code_data}" alt="QR Code" style="width: 80px; height: 80px;">
    </div>

    <div class="header" style="margin-top: 20px;">
        <h1>Madhu PaperBags</h1>
    </div>

    <div class="company-details">
        <h3>INVOICE</h3>
        <p>16/2, D.R.R Industrial Estate, Near NCC Head Office Ondipudur, Singanallur<br>
        Coimbatore-641005<br>
        Ph: 6383148504<br>
        GSTIN: 33ETMPM5267A1ZO<br>
        Email: malarpaperbags@gmail.com</p>
    </div>

    <div class="invoice-details">
        <table>
            <tr>
                <td><strong>Invoice No:</strong> {$invoice_number}</td>
                <td><strong>Date:</strong> {$invoice_date}</td>
            </tr>
            <tr>
                <td><strong>PO Number:</strong> {$po_number}</td>
                <td><strong>Party GSTIN:</strong> {$party_gstin}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Mode of Transport:</strong> {$mode_of_transport}</td>
            </tr>
        </table>
    </div>

    <div class="customer-details">
        <strong>Customer Name:</strong> {$display_customer_name}<br>
        <strong>Address:</strong> {$display_customer_address}<br>
        <strong>Phone No:</strong> {$display_customer_phone}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Material</th>
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

    <div class="gst-details">
        <p style="text-align: right;">
            <strong>CGST ({$cgst_rate}%):</strong> ₹{$display_cgst_amount}<br>
            <strong>SGST ({$sgst_rate}%):</strong> ₹{$display_sgst_amount}<br>
            <strong>IGST ({$igst_rate}%):</strong> ₹{$display_igst_amount}<br>
        </p>
    </div>

    <div class="total">
        <p>Grand Total: ₹{$display_total}</p>
    </div>

    <div class="signature" style="float: right; width: 45%;">
        <p style="margin-bottom: 70px; border-bottom: 1px solid black; padding-bottom: 5px; width: 200px; text-align: center; margin-left: auto; margin-right: auto;">Receiver's Signature</p>
        <p style="border-bottom: 1px solid black; padding-bottom: 5px; width: 200px; text-align: center; margin-left: auto; margin-right: auto;">Authorized Signatory with Seal</p>
    </div>

    <div class="terms" style="float: left; width: 50%;">
        <h4>Terms &amp; Conditions:</h4>
        <ol>
            <li>Goods once sold cannot be take back or exchanged.</li>
            <li>Our responsibility ceases immediately the goods is delivery or handed over to the carrier.</li>
            <li>Subject to Bangalore Jurisdiction.</li>
        </ol>
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
$pdf_filename = 'invoices/Invoice_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $invoice_number) . '.pdf';
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
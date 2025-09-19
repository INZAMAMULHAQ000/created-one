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

// Validate required POST data
if (!isset($_POST['selected_materials_data']) || empty($_POST['selected_materials_data'])) {
    die("Error: No materials selected.");
}

$selected_materials_data = json_decode($_POST['selected_materials_data'], true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("Error: Invalid material data provided.");
}

if (empty($selected_materials_data)) {
    die("Error: No materials selected for quotation.");
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

// Read Sun logo image and convert to base64
$sun_logo_file = __DIR__ . '/Sun.jpeg';
$sun_logo_data = '';
if (file_exists($sun_logo_file)) {
    $sun_logo_type = pathinfo($sun_logo_file, PATHINFO_EXTENSION);
    $sun_logo_data = 'data:image/' . $sun_logo_type . ';base64,' . base64_encode(file_get_contents($sun_logo_file));
} else {
    error_log("Sun logo image not found at: " . $sun_logo_file);
}

// Read G-Pay image and convert to base64
$gpay_file = __DIR__ . '/g_pay.jpeg';
$gpay_data = '';
if (file_exists($gpay_file)) {
    $gpay_type = pathinfo($gpay_file, PATHINFO_EXTENSION);
    $gpay_data = 'data:image/' . $gpay_type . ';base64,' . base64_encode(file_get_contents($gpay_file));
} else {
    error_log("G-Pay image not found at: " . $gpay_file);
}

// Process materials data
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

// Quotation details from form
$quotation_number = htmlspecialchars($_POST['quotation_number']);
$quotation_date_raw = $_POST['date'];
// Convert date to Indian standard (DD-MM-YYYY)
$quotation_date = (new DateTime($quotation_date_raw))->format('d-m-Y');

// Customer details
$customer_name = htmlspecialchars($_POST['customer_name']);
$customer_company = isset($_POST['customer_company']) ? htmlspecialchars($_POST['customer_company']) : '';
$customer_address = nl2br(htmlspecialchars($_POST['customer_address']));
$customer_phone = htmlspecialchars($_POST['customer_phone']);
$contact_person = isset($_POST['contact_person']) ? htmlspecialchars($_POST['contact_person']) : '';
$valid_until = isset($_POST['valid_until']) ? (new DateTime($_POST['valid_until']))->format('d-m-Y') : '';

// Generate conditional content before HTML
$contact_row = '';
if ($valid_until && $contact_person) {
    $contact_row = "<tr><td><strong>Contact Person:</strong> {$contact_person}</td><td><strong>Valid Until:</strong> {$valid_until}</td></tr>";
} elseif ($contact_person) {
    $contact_row = "<tr><td colspan='2'><strong>Contact Person:</strong> {$contact_person}</td></tr>";
}

$company_info = $customer_company ? "<strong>Company:</strong> {$customer_company}<br>" : "";
$qr_code_html = $qr_code_data ? "<img src='{$qr_code_data}' style='width: 25px; height: 25px;'><br>" : "";
$sun_logo_html = $sun_logo_data ? "<img src='{$sun_logo_data}' alt='Sun Logo' style='width: 70px; height: 80px;'>" : "";
$qr_code_top_html = $qr_code_data ? "<img src='{$qr_code_data}' alt='QR Code' style='width: 80px; height: 80px;'>" : "";
$gpay_html = $gpay_data ? "<img src='{$gpay_data}' alt='G-Pay QR' style='width: 80px; height: 80px;'>" : "";

// Pre-process total for display
$display_total = number_format($total_price_before_gst, 2);

// Initialize Dompdf with simplified options
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', false); // Disable PHP in HTML
$options->set('isFontSubsettingEnabled', false); // Disable to avoid font issues
$options->set('defaultFont', 'Arial'); // Use simpler font
$options->set('isRemoteEnabled', false); // Disable remote loading
$dompdf = new Dompdf($options);

// Generate HTML content with professional structure matching invoice PDF
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Quotation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 10pt;
            line-height: 1.2;
        }
        h1 {
            font-size: 18pt;
            margin-bottom: 10px;
            color: #0066cc;
            text-align: center;
        }
        h3 {
            font-size: 14pt;
            margin-bottom: 5px;
        }
        p {
            margin-bottom: 5px;
        }
        .header {
            position: relative;
            text-align: center;
            color: #0066cc;
            margin-bottom: 20px;
            min-height: 100px;
        }
        .header-left {
            position: absolute;
            left: 0;
            top: 0;
            width: 80px;
        }
        .header-right {
            position: absolute;
            right: 0;
            top: 0;
            width: 80px;
        }
        .header-center {
            margin: 0 90px;
            padding-top: 10px;
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
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .total-section {
            text-align: right;
            font-weight: bold;
            font-size: 12pt;
            margin: 15px 0;
            background-color: #f0f0f0;
            padding: 8px;
        }
        .terms {
            margin-top: 30px;
            font-size: 8pt;
        }
        .signature {
            text-align: right;
            margin-top: 30px;
        }
        .banking-section {
            border: 1px solid #ddd;
            margin-top: 20px;
            background-color: #f9f9f9;
        }
        .banking-header {
            background-color: #4682B4;
            color: white;
            text-align: center;
            padding: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header with logos and company info -->
    <div class="header">
        <!-- Sun logo on left -->
        <div class="header-left">
            {$sun_logo_html}
        </div>
        
        <!-- QR code on right -->
        <div class="header-right">
            {$qr_code_top_html}
        </div>
        
        <!-- Company details in center -->
        <div class="header-center">
            <h1>MALAR PAPER BAGS</h1>
            <p style="font-size: 9pt;">16/2, D.R.R Industrial Estate,<br>
            Near NCC Head Office, Ondipudur, Singanallur,<br>
            Coimbatore-641005<br>
            Ph: 6383148504 | Email: malarpaperbags@gmail.com<br>
            GSTIN: 33ETMPM5267A1ZO</p>
        </div>
    </div>

    <!-- Quotation Title -->
    <h2 style="text-align: center; background-color: #f0f0f0; padding: 8px; margin: 20px 0;">
        SALES QUOTATION
    </h2>

    <!-- Quotation Details -->
    <table>
        <tr>
            <td style="background-color: #f0f0f0; font-weight: bold;">Quotation No:</td>
            <td>{$quotation_number}</td>
            <td style="background-color: #f0f0f0; font-weight: bold;">Date:</td>
            <td>{$quotation_date}</td>
        </tr>

        {$contact_row}
    </table>

    <!-- Customer Details -->
    <h4>Customer Details:</h4>
    <div style="border: 1px solid #ddd; padding: 10px; background-color: #f9f9f9; margin-bottom: 15px;">
        <strong>Customer:</strong> {$customer_name}<br>
        {$company_info}
        <strong>Address:</strong> {$customer_address}<br>
        <strong>Phone:</strong> {$customer_phone}
    </div>

    <!-- Materials Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">#</th>
                <th style="width: 40%;">Material Description</th>
                <th style="width: 15%;">HSN Code</th>
                <th style="width: 10%;">Quantity</th>
                <th style="width: 15%;">Price/Unit</th>
                <th style="width: 15%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            {$html_material_rows}
        </tbody>
    </table>

    <!-- Total Section -->
    <div class="total-section">
        TOTAL: ₹{$display_total}
    </div>

    <!-- Terms & Conditions -->
    <div class="terms">
        <h4>Terms & Conditions:</h4>
        <ul style="margin: 0; padding-left: 20px;">
            <li>Goods once sold cannot be taken back or exchanged</li>
            <li>Our responsibility ceases immediately the goods are delivered or handed over to the carrier</li>
            <li>Subject to Coimbatore jurisdiction</li>
            <li>Payment: 50% advance, balance on delivery</li>
            <li>Delivery: 15-20 working days from receipt of order</li>
            <li>GST extra as applicable</li>
        </ul>
    </div>

    <!-- Banking Details with G-Pay -->
    <div class="banking-section">
        <div class="banking-header">
            Banking Details
        </div>
        <table style="border: none; margin: 0;">
            <tr style="border: none;">
                <td style="border: none; padding: 8px; width: 60%; vertical-align: top;">
                    <strong>ACCOUNT NAME:</strong> MALAR PAPER BAGS<br>
                    <strong>ACCOUNT NUMBER:</strong> 50200090346107<br>
                    <strong>BANK NAME:</strong> HDFC BANK, KALAPATTI BRANCH<br>
                    <strong>IFSC CODE:</strong> HDFC0001068
                </td>
                <td style="border: none; padding: 8px; text-align: center; width: 40%; vertical-align: middle;">
                    {$gpay_html}<br>
                    <span style="font-size: 8pt; font-weight: bold;">Scan to Pay via G-Pay</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Signature -->
    <div class="signature">
        <p style="margin-top: 40px;">For MALAR PAPER BAGS</p>
        <br><br>
        <p style="border-top: 1px solid black; padding-top: 5px; width: 200px;">Authorized Signatory</p>
    </div>

</body>
</html>
HTML;

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Save PDF file
if (!file_exists('quotations')) {
    mkdir('quotations', 0777, true);
}

$pdf_filename = 'quotations/Quotation_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $quotation_number) . '.pdf';
$output_file = __DIR__ . '/' . $pdf_filename;

file_put_contents($output_file, $dompdf->output());

// Insert into database
$stmt = mysqli_prepare($conn, "INSERT INTO sales_quotations (quotation_number, customer_name, quotation_date, pdf_path) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssss", $quotation_number, $customer_name, $quotation_date_raw, $pdf_filename);

try {
    if (mysqli_stmt_execute($stmt)) {
        // Output the PDF to browser
        $dompdf->stream("Quotation_{$quotation_number}.pdf", array("Attachment" => false));
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

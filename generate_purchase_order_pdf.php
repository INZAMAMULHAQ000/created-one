<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "config/database.php";
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

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
    die("Error: No materials selected for purchase order.");
}

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

$price = $total_price_before_gst;

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

// Get form data
$po_number = $_POST['po_number'];
$po_date_raw = $_POST['date'];
$po_date = (new DateTime($po_date_raw))->format('d-m-Y');

// Seller details
$seller_name = htmlspecialchars($_POST['seller_name']);
$seller_company = htmlspecialchars($_POST['seller_company']);
$seller_address = nl2br(htmlspecialchars($_POST['seller_address']));
$seller_phone = htmlspecialchars($_POST['seller_phone']);
$seller_gst = htmlspecialchars($_POST['seller_gst']);
$seller_email = htmlspecialchars($_POST['seller_email']);

// Buyer details
$buyer_name = htmlspecialchars($_POST['buyer_name']);
$buyer_company = htmlspecialchars($_POST['buyer_company']);
$buyer_address = nl2br(htmlspecialchars($_POST['buyer_address']));
$buyer_phone = htmlspecialchars($_POST['buyer_phone']);
$buyer_gst = htmlspecialchars($_POST['buyer_gst']);
$buyer_email = htmlspecialchars($_POST['buyer_email']);

$display_total = number_format($total, 2);

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
    <title>Purchase Order</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
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
        .po-details,
        .seller-details,
        .buyer-details,
        .gst-details,
        .total {
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
            z-index: 10;
        }
        .qr-code {
            top: 20px;
            right: 20px;
            margin: 0;
        }
        .sun-logo {
            top: 20px;
            left: 20px;
            margin: 0;
        }
        .party-details {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .seller-section, .buyer-section {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .seller-section {
            border-right: none;
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
        <h3>PURCHASE ORDER</h3>
        <p>No : 206, Byraveshwara Badavane, Laggere, 1st Main, 4th Cross,<br>
        Near Sharada School, Bangalore - 560 058<br>
        Mob : 9900868607<br>
        GST-ID : 29BKTPR4159P1ZU<br>
        Email : krnathan5374@gmail.com <br>
        State : Karnataka</p>
    </div>

    <div class="po-details">
        <table>
            <tr>
                <td><strong>PO No:</strong> {$po_number}</td>
                <td><strong>Date:</strong> {$po_date}</td>
            </tr>
        </table>
    </div>

    <div class="party-details">
        <div class="seller-section">
            <h4>SELLER DETAILS</h4>
            <p><strong>Name:</strong> {$seller_name}</p>
            <p><strong>Company:</strong> {$seller_company}</p>
            <p><strong>Address:</strong><br>{$seller_address}</p>
            <p><strong>Phone:</strong> {$seller_phone}</p>
            <p><strong>GST ID:</strong> {$seller_gst}</p>
            <p><strong>Email:</strong> {$seller_email}</p>
        </div>
        <div class="buyer-section">
            <h4>BUYER DETAILS</h4>
            <p><strong>Name:</strong> {$buyer_name}</p>
            <p><strong>Company:</strong> {$buyer_company}</p>
            <p><strong>Address:</strong><br>{$buyer_address}</p>
            <p><strong>Phone:</strong> {$buyer_phone}</p>
            <p><strong>GST ID:</strong> {$buyer_gst}</p>
            <p><strong>Email:</strong> {$buyer_email}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>SL.NO</th>
                <th>Name/Description of Material</th>
                <th>HSN Code</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            {$html_material_rows}
        </tbody>
    </table>

    <div class="gst-details">
        <table style="width: 50%; float: right;">
            <tr>
                <td><strong>Subtotal:</strong></td>
                <td>₹{$price}</td>
            </tr>
HTML;

// Add GST rows only if they have values
if ($cgst_rate > 0) {
    $html .= "<tr><td><strong>CGST ({$cgst_rate}%):</strong></td><td>₹{$display_cgst_amount}</td></tr>";
}
if ($sgst_rate > 0) {
    $html .= "<tr><td><strong>SGST ({$sgst_rate}%):</strong></td><td>₹{$display_sgst_amount}</td></tr>";
}
if ($igst_rate > 0) {
    $html .= "<tr><td><strong>IGST ({$igst_rate}%):</strong></td><td>₹{$display_igst_amount}</td></tr>";
}

$html .= <<<HTML
            <tr style="background-color: #f0f0f0;">
                <td><strong>Total:</strong></td>
                <td><strong>₹{$display_total}</strong></td>
            </tr>
        </table>
        <div style="clear: both;"></div>
    </div>

    <div class="terms">
        <h4>Terms and Conditions:</h4>
        <p>1. Please supply the materials as per specifications mentioned above.</p>
        <p>2. Delivery should be made within the agreed timeframe.</p>
        <p>3. Payment terms as per agreement.</p>
        <p>4. All disputes subject to Bangalore jurisdiction.</p>
    </div>

    <div class="signature">
        <p>Authorized Signature</p>
        <br><br>
        <p>Madhu PaperBags</p>
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

// Create invoices directory if it doesn't exist
$invoices_dir = __DIR__ . '/purchase_orders';
if (!is_dir($invoices_dir)) {
    mkdir($invoices_dir, 0755, true);
}

// Generate filename
$filename = 'PO_' . $po_number . '.pdf';
$filepath = $invoices_dir . '/' . $filename;

// Save PDF to file
file_put_contents($filepath, $dompdf->output());

// Store purchase order details in database
$stmt = mysqli_prepare($conn, "INSERT INTO purchase_orders (po_number, seller_name, seller_company, buyer_name, buyer_company, po_date, pdf_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sssssss", $po_number, $seller_name, $seller_company, $buyer_name, $buyer_company, $po_date_raw, $filepath);

try {
    if (mysqli_stmt_execute($stmt)) {
        // Redirect to purchase order history page on success
        header("Location: purchase_order_history.php?status=success&po=" . urlencode($po_number));
        exit();
    }
} catch (mysqli_sql_exception $e) {
    // Check for duplicate entry error
    if ($e->getCode() == 1062) {
        header("Location: purchase_order.php?status=error&message=" . urlencode("PO number '{$po_number}' already exists. Please use a different one."));
        exit();
    } else {
        error_log("Error inserting purchase order into database: " . $e->getMessage());
        die("Error generating purchase order. Please try again.");
    }
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

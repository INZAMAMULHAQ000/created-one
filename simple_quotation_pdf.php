<?php
// Simplified quotation PDF generation for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "config/database.php";
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Simple login check
if(!isset($_SESSION['loggedin'])) {
    $_SESSION['loggedin'] = true; // Auto-login for testing
}

// Validate POST data or create test data
if (!isset($_POST['quotation_number'])) {
    $_POST = [
        'quotation_number' => 'SIMPLE-001',
        'date' => date('Y-m-d'),
        'customer_name' => 'Test Customer',
        'customer_address' => "Test Address\nTest City",
        'customer_phone' => '1234567890',
        'selected_materials_data' => json_encode([
            [
                'id' => 1,
                'name' => 'Test Material',
                'hsn_code' => '12345678',
                'price_per_unit' => 100.00,
                'quantity' => 2
            ]
        ])
    ];
}

$selected_materials_data = json_decode($_POST['selected_materials_data'], true);
$quotation_number = $_POST['quotation_number'];
$quotation_date = (new DateTime($_POST['date']))->format('d-m-Y');
$customer_name = $_POST['customer_name'];
$customer_address = $_POST['customer_address'];
$customer_phone = $_POST['customer_phone'];

// Process materials
$total = 0;
$materials_html = '';
$count = 1;
foreach ($selected_materials_data as $item) {
    $subtotal = $item['price_per_unit'] * $item['quantity'];
    $total += $subtotal;
    $materials_html .= '<tr>
        <td>' . $count++ . '</td>
        <td>' . htmlspecialchars($item['name']) . '</td>
        <td>' . htmlspecialchars($item['hsn_code']) . '</td>
        <td>' . $item['quantity'] . '</td>
        <td>₹' . number_format($item['price_per_unit'], 2) . '</td>
        <td>₹' . number_format($subtotal, 2) . '</td>
    </tr>';
}

// Simple HTML without complex styling
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Quotation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; color: #0066cc; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { text-align: right; font-weight: bold; font-size: 14pt; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>MALAR PAPER BAGS</h1>
        <p>16/2, D.R.R Industrial Estate, Near NCC Head Office Ondipudur, Singanallur<br>
        Coimbatore-641005<br>
        Ph: 6383148504 | Email: malarpaperbags@gmail.com<br>
        GSTIN: 33ETMPM5267A1ZO</p>
    </div>
    
    <h2 style="text-align: center; color: #0066cc;">SALES QUOTATION</h2>
    
    <table>
        <tr>
            <td><strong>Quotation No:</strong> {$quotation_number}</td>
            <td><strong>Date:</strong> {$quotation_date}</td>
        </tr>
    </table>
    
    <h4>Customer Details:</h4>
    <p><strong>Name:</strong> {$customer_name}<br>
    <strong>Address:</strong> {$customer_address}<br>
    <strong>Phone:</strong> {$customer_phone}</p>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Material</th>
                <th>HSN Code</th>
                <th>Qty</th>
                <th>Rate</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            {$materials_html}
        </tbody>
    </table>
    
    <div class="total">
        Total: ₹{total_formatted}
    </div>
    
    <div style="margin-top: 50px;">
        <h4>Terms & Conditions:</h4>
        <ul>
            <li>Payment: 50% advance, balance on delivery</li>
            <li>Delivery: 15-20 working days</li>
            <li>Validity: 30 days from quotation date</li>
        </ul>
    </div>
    
    <div style="text-align: right; margin-top: 50px;">
        <p>For MALAR PAPER BAGS</p>
        <br><br>
        <p>Authorized Signatory</p>
    </div>
    
</body>
</html>
HTML;

// Replace placeholder
$html = str_replace('{total_formatted}', number_format($total, 2), $html);

try {
    // Initialize DomPDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'Arial');
    $dompdf = new Dompdf($options);
    
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    // Output PDF to browser
    $dompdf->stream("Simple_Quotation_{$quotation_number}.pdf", array("Attachment" => false));
    
} catch (Exception $e) {
    echo "Error generating PDF: " . $e->getMessage();
    echo "<br><br>HTML content:<br><pre>" . htmlspecialchars($html) . "</pre>";
}
?>
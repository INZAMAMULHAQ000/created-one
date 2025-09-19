<?php
// Debug version of quotation PDF generation
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

echo "<h2>Debug Quotation PDF Generation</h2>";

try {
    session_start();
    echo "✓ Session started<br>";
    
    require_once "config/database.php";
    echo "✓ Database config loaded<br>";
    
    require_once 'vendor/autoload.php';
    echo "✓ Composer autoload included<br>";
    
    use Dompdf\Dompdf;
    use Dompdf\Options;
    use mysqli_sql_exception;
    echo "✓ DomPDF classes loaded<br>";
    
    if(!isset($_SESSION['loggedin'])) {
        $_SESSION['loggedin'] = true; // Skip for debug
        echo "⚠ Login session created for debug<br>";
    } else {
        echo "✓ Session validated<br>";
    }
    
    // Check for POST data
    if (!isset($_POST['quotation_number'])) {
        echo "<h3>No POST data found. Creating test data...</h3>";
        $_POST = [
            'quotation_number' => 'DEBUG-001',
            'date' => date('Y-m-d'),
            'customer_name' => 'Debug Customer',
            'customer_company' => 'Debug Company Ltd',
            'customer_address' => "123 Debug Street\nDebug City, Debug State\nPIN: 123456",
            'customer_phone' => '+91 9876543210',
            'contact_person' => 'Debug Contact',
            'valid_until' => date('Y-m-d', strtotime('+30 days')),
            'selected_materials_data' => json_encode([
                [
                    'id' => 1,
                    'name' => 'Debug Material 1',
                    'hsn_code' => '12345678',
                    'price_per_unit' => 100.00,
                    'quantity' => 5
                ],
                [
                    'id' => 2,
                    'name' => 'Debug Material 2',
                    'hsn_code' => '87654321',
                    'price_per_unit' => 250.00,
                    'quantity' => 2
                ]
            ])
        ];
    }
    
    echo "✓ POST data available<br>";
    
    // Validate required POST data
    if (!isset($_POST['selected_materials_data']) || empty($_POST['selected_materials_data'])) {
        throw new Exception("No materials selected.");
    }
    echo "✓ Materials data found<br>";
    
    $selected_materials_data = json_decode($_POST['selected_materials_data'], true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid material data provided: " . json_last_error_msg());
    }
    echo "✓ Materials data parsed<br>";
    
    if (empty($selected_materials_data)) {
        throw new Exception("No materials selected for quotation.");
    }
    echo "✓ Materials validation passed<br>";
    
    // Check image files
    $qr_code_file = __DIR__ . '/QR.jpg';
    $sun_logo_file = __DIR__ . '/Sun.jpeg';
    
    echo "QR Code file: " . ($qr_code_file) . " - " . (file_exists($qr_code_file) ? "✓ Found" : "⚠ Missing") . "<br>";
    echo "Sun Logo file: " . ($sun_logo_file) . " - " . (file_exists($sun_logo_file) ? "✓ Found" : "⚠ Missing") . "<br>";
    
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
    echo "✓ Materials processed. Total: ₹" . number_format($total_price_before_gst, 2) . "<br>";
    
    // Process form data
    $quotation_number = htmlspecialchars($_POST['quotation_number']);
    $quotation_date_raw = $_POST['date'];
    $quotation_date = (new DateTime($quotation_date_raw))->format('d-m-Y');
    
    $customer_name = htmlspecialchars($_POST['customer_name']);
    $customer_company = isset($_POST['customer_company']) ? htmlspecialchars($_POST['customer_company']) : '';
    $customer_address = nl2br(htmlspecialchars($_POST['customer_address']));
    $customer_phone = htmlspecialchars($_POST['customer_phone']);
    $contact_person = isset($_POST['contact_person']) ? htmlspecialchars($_POST['contact_person']) : '';
    $valid_until = isset($_POST['valid_until']) ? (new DateTime($_POST['valid_until']))->format('d-m-Y') : '';
    
    echo "✓ Form data processed<br>";
    
    // Initialize DomPDF
    echo "<h3>Initializing DomPDF...</h3>";
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('isFontSubsettingEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $dompdf = new Dompdf($options);
    echo "✓ DomPDF initialized<br>";
    
    // Create simple HTML for testing
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Debug Quotation</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { color: #0066cc; }
            table { width: 100%; border-collapse: collapse; margin: 10px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
        </style>
    </head>
    <body>
        <h1>MALAR PAPER BAGS</h1>
        <p>16/2, D.R.R Industrial Estate, Near NCC Head Office Ondipudur, Singanallur<br>
        Coimbatore-641005 | Ph: 6383148504 | GSTIN: 33ETMPM5267A1ZO</p>
        <h2>SALES QUOTATION</h2>
        <p><strong>Quotation No:</strong> ' . $quotation_number . '</p>
        <p><strong>Date:</strong> ' . $quotation_date . '</p>
        <p><strong>Customer:</strong> ' . $customer_name . '</p>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Material</th>
                    <th>HSN</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                ' . $html_material_rows . '
            </tbody>
        </table>
        <p><strong>Total: ₹' . number_format($total_price_before_gst, 2) . '</strong></p>
    </body>
    </html>';
    
    echo "✓ HTML generated<br>";
    
    $dompdf->loadHtml($html);
    echo "✓ HTML loaded into DomPDF<br>";
    
    $dompdf->setPaper('A4', 'portrait');
    echo "✓ Paper size set<br>";
    
    $dompdf->render();
    echo "✓ PDF rendered successfully<br>";
    
    // Save PDF for debugging
    $pdf_content = $dompdf->output();
    $debug_filename = 'debug_quotation_' . date('Y-m-d_H-i-s') . '.pdf';
    file_put_contents($debug_filename, $pdf_content);
    echo "✓ PDF saved as: <a href='{$debug_filename}' target='_blank'>{$debug_filename}</a><br>";
    
    echo "<h3>✅ PDF Generation Successful!</h3>";
    echo "<p>The PDF generation is working. The issue might be with the original file.</p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error Found:</h3>";
    echo "<div style='background: #ffe6e6; border: 1px solid #ff0000; padding: 10px; margin: 10px 0;'>";
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
    
    echo "<h4>Stack Trace:</h4>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<h3>❌ Fatal Error Found:</h3>";
    echo "<div style='background: #ffe6e6; border: 1px solid #ff0000; padding: 10px; margin: 10px 0;'>";
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
}

echo "<br><h3>PHP Info:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";

// Check GD extension
if (extension_loaded('gd')) {
    echo "✓ GD Extension: Available<br>";
} else {
    echo "⚠ GD Extension: Missing (required for DomPDF images)<br>";
}

echo "<br><p><a href='sales_quotation.php'>← Back to Quotation Form</a></p>";
?>
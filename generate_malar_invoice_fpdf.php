<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "config/database.php";
require_once 'fpdf/fpdf184/fpdf.php';
require_once 'includes/number_to_words.php';

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
$item_count = 1;
$materials_for_pdf = [];

foreach ($selected_materials_data as $item) {
    $item_id = $item['id'];
    $item_name = $item['name'];
    $item_hsn_code = $item['hsn_code'];
    $item_price_per_unit = floatval($item['price_per_unit']);
    $item_quantity = intval($item['quantity']);
    $item_subtotal = $item_price_per_unit * $item_quantity;
    $total_price_before_gst += $item_subtotal;

    $materials_for_pdf[] = [
        'sl_no' => $item_count++,
        'name' => $item_name,
        'hsn_code' => $item_hsn_code,
        'quantity' => $item_quantity,
        'price_per_unit' => $item_price_per_unit,
        'subtotal' => $item_subtotal
    ];
}

$price = $total_price_before_gst;

$cgst_rate = isset($_POST['cgst_rate']) ? floatval($_POST['cgst_rate']) : 0;
$sgst_rate = isset($_POST['sgst_rate']) ? floatval($_POST['sgst_rate']) : 0;
$igst_rate = isset($_POST['igst_rate']) ? floatval($_POST['igst_rate']) : 0;

$cgst_amount = ($price * $cgst_rate) / 100;
$sgst_amount = ($price * $sgst_rate) / 100;
$igst_amount = ($price * $igst_rate) / 100;
$total = $price + $cgst_amount + $sgst_amount + $igst_amount;

// Process customer details
$customer_name = $_POST['customer_name'];
$customer_address = $_POST['customer_address'];
$customer_phone = $_POST['customer_phone'];

// Convert amount to words
$amount_in_words = amountInWords($total);

// Assign POST values
$invoice_number = $_POST['invoice_number'];
$invoice_date_raw = $_POST['date'];
$invoice_date = (new DateTime($invoice_date_raw))->format('d-m-Y');
$party_gstin = $_POST['gstin'];
$po_number = isset($_POST['po_number']) ? $_POST['po_number'] : '';
$mode_of_transport = ($transport ? $transport['name'] : '');

// Create PDF class extending FPDF
class MalarInvoicePDF extends FPDF
{
    private $logo_path;
    private $qr_path;
    
    function __construct($logo_path = '', $qr_path = '') {
        parent::__construct();
        $this->logo_path = $logo_path;
        $this->qr_path = $qr_path;
    }
    
    function Header() {
        // Header with logo, company info, and QR code
        
        // Logo on the left
        if (file_exists($this->logo_path)) {
            $this->Image($this->logo_path, 10, 10, 40, 30);
        }
        
        // QR Code on the right
        if (file_exists($this->qr_path)) {
            $this->Image($this->qr_path, 175, 10, 25, 25);
        }
        
        // Company information aligned to left with right positioning
        $this->SetX(55); // Move to the right to avoid logo overlap
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 8, 'MALAR PAPER BAGS', 0, 1, 'L');
        
        $this->SetX(55); // Move to the right to avoid logo overlap
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, '16/2, D.R.R Industrial Estate,', 0, 1, 'L');
        $this->SetX(55);
        $this->Cell(0, 5, 'Near NCC Head Office,', 0, 1, 'L');
        $this->SetX(55);
        $this->Cell(0, 5, 'Ondipudur, Singanallur,', 0, 1, 'L');
        $this->SetX(55);
        $this->Cell(0, 5, 'Coimbatore-641005', 0, 1, 'L');
        
        $this->SetX(55);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 5, 'www.malarpaperbags.in', 0, 1, 'L');
        
        $this->Ln(5);
        
        // TAX INVOICE title
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(220, 220, 220); // Light gray background
        $this->SetTextColor(0, 0, 0); // Black text
        $this->Cell(0, 10, 'TAX INVOICE', 1, 1, 'C', true);
        $this->SetTextColor(0, 0, 0); // Reset to black text
        
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
    
    function InvoiceDetails($invoice_number, $invoice_date, $po_number, $party_gstin, $mode_of_transport) {
        $this->SetFont('Arial', '', 10);
        
        // Set light gray background for labels
        $this->SetFillColor(220, 220, 220);
        
        // Invoice details table
        $this->Cell(45, 8, 'Invoice No:', 1, 0, 'L', true);
        $this->Cell(45, 8, $invoice_number, 1, 0, 'L');
        $this->Cell(45, 8, 'Date:', 1, 0, 'L', true);
        $this->Cell(55, 8, $invoice_date, 1, 1, 'L');
        
        $this->Cell(45, 8, 'PO Number:', 1, 0, 'L', true);
        $this->Cell(45, 8, $po_number, 1, 0, 'L');
        $this->Cell(45, 8, 'Party GSTIN:', 1, 0, 'L', true);
        $this->Cell(55, 8, $party_gstin, 1, 1, 'L');
        
        $this->Cell(90, 8, 'Mode of Transport:', 1, 0, 'L', true);
        $this->Cell(100, 8, $mode_of_transport, 1, 1, 'L');
        
        $this->Ln(5);
    }
    
    function AddressSection($customer_name, $customer_address, $customer_phone) {
        $this->SetFont('Arial', 'B', 8); // Reduced font size
        
        // Set light gray background for Bill To and Ship To headers
        $this->SetFillColor(220, 220, 220); // Light gray background
        $this->SetTextColor(0, 0, 0); // Black text
        
        // Bill To section
        $this->Cell(95, 6, 'BILL TO', 1, 0, 'C', true); // Reduced height
        $this->Cell(95, 6, 'SHIP TO', 1, 1, 'C', true);
        
        // Reset to normal background for content
        $this->SetFillColor(255, 255, 255); // White background
        $this->SetFont('Arial', '', 7); // Smaller content font
        
        // Split address into lines
        $address_lines = explode("\n", $customer_address);
        $max_lines = max(count($address_lines) + 2, 4); // +2 for name and phone
        
        for ($i = 0; $i < $max_lines; $i++) {
            $left_content = '';
            $right_content = '';
            
            if ($i == 0) {
                $left_content = $customer_name;
                $right_content = $customer_name;
            } elseif ($i <= count($address_lines)) {
                $line_index = $i - 1;
                if (isset($address_lines[$line_index])) {
                    $left_content = $address_lines[$line_index];
                    $right_content = $address_lines[$line_index];
                }
            } elseif ($i == $max_lines - 1) {
                $left_content = 'Phone: ' . $customer_phone;
                $right_content = 'Phone: ' . $customer_phone;
            }
            
            $this->Cell(95, 4, $left_content, 1, 0, 'L'); // Reduced height
            $this->Cell(95, 4, $right_content, 1, 1, 'L');
        }
        
        $this->Ln(3); // Reduced spacing
    }
    
    function MaterialsTable($materials) {
        $this->SetFont('Arial', 'B', 7); // Reduced font size
        
        // Set light gray background for table headers
        $this->SetFillColor(200, 200, 200); // Light gray background
        $this->SetTextColor(0, 0, 0); // Black text
        
        // Table headers
        $this->Cell(15, 6, 'S.No', 1, 0, 'C', true); // Reduced height
        $this->Cell(70, 6, 'Name/Description', 1, 0, 'C', true);
        $this->Cell(25, 6, 'HSN Code', 1, 0, 'C', true);
        $this->Cell(20, 6, 'Qty', 1, 0, 'C', true);
        $this->Cell(30, 6, 'Rate', 1, 0, 'C', true);
        $this->Cell(30, 6, 'Amount', 1, 1, 'C', true);
        
        // Reset colors for table content
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 6); // Smaller content font
        
        // Table rows
        foreach ($materials as $material) {
            $this->Cell(15, 4, $material['sl_no'], 1, 0, 'C'); // Reduced height
            $this->Cell(70, 4, $material['name'], 1, 0, 'L');
            $this->Cell(25, 4, $material['hsn_code'], 1, 0, 'C');
            $this->Cell(20, 4, $material['quantity'], 1, 0, 'C');
            $this->Cell(30, 4, 'Rs. ' . number_format($material['price_per_unit'], 2), 1, 0, 'R');
            $this->Cell(30, 4, 'Rs. ' . number_format($material['subtotal'], 2), 1, 1, 'R');
        }
        
        $this->Ln(2); // Reduced spacing
    }
    
    function TotalsSection($subtotal, $cgst_rate, $cgst_amount, $sgst_rate, $sgst_amount, $igst_rate, $igst_amount, $total) {
        $this->SetFont('Arial', 'B', 7); // Reduced font size
        
        $x_start = 130;
        $this->SetX($x_start);
        
        // Subtotal
        $this->Cell(30, 4, 'Sub Total:', 1, 0, 'L'); // Reduced height
        $this->Cell(30, 4, 'Rs. ' . number_format($subtotal, 2), 1, 1, 'R');
        
        // GST rows (only if > 0)
        if ($cgst_rate > 0) {
            $this->SetX($x_start);
            $this->Cell(30, 4, "CGST ({$cgst_rate}%):", 1, 0, 'L');
            $this->Cell(30, 4, 'Rs. ' . number_format($cgst_amount, 2), 1, 1, 'R');
        }
        
        if ($sgst_rate > 0) {
            $this->SetX($x_start);
            $this->Cell(30, 4, "SGST ({$sgst_rate}%):", 1, 0, 'L');
            $this->Cell(30, 4, 'Rs. ' . number_format($sgst_amount, 2), 1, 1, 'R');
        }
        
        if ($igst_rate > 0) {
            $this->SetX($x_start);
            $this->Cell(30, 4, "IGST ({$igst_rate}%):", 1, 0, 'L');
            $this->Cell(30, 4, 'Rs. ' . number_format($igst_amount, 2), 1, 1, 'R');
        }
        
        // Grand Total
        $this->SetX($x_start);
        $this->SetFillColor(180, 180, 180); // Gray background
        $this->SetTextColor(0, 0, 0); // Black text
        $this->Cell(30, 6, 'Grand Total:', 1, 0, 'L', true); // Slightly taller for emphasis
        $this->Cell(30, 6, 'Rs. ' . number_format($total, 2), 1, 1, 'R', true);
        $this->SetTextColor(0, 0, 0); // Reset to black text
        
        $this->Ln(3); // Reduced spacing
    }
    
    function AmountInWords($amount_words) {
        $this->SetFont('Arial', 'B', 8); // Reduced font size
        $this->Cell(0, 6, 'Amount in Words: ' . $amount_words, 1, 1, 'L'); // Reduced height
        $this->Ln(2); // Reduced spacing
    }
    
    function TermsAndSignature() {
        $this->SetFont('Arial', 'B', 7); // Reduced font size
        $this->Cell(110, 4, '', 0, 0, 'L'); // Empty space for terms
        $this->Cell(80, 4, 'For MALAR PAPER BAGS', 0, 1, 'C');
        
        // Only signature box without terms
        $this->Cell(110, 15, '', 0, 0, 'L'); // Empty space
        $this->Cell(80, 15, '', 1, 1, 'C'); // Signature box
        
        $this->SetFont('Arial', 'B', 6); // Smaller font for signature label
        $this->Cell(110, 3, '', 0, 0, 'L');
        $this->Cell(80, 3, 'Authorized Signatory', 0, 1, 'C');
    }
    
    function BankDetailsSection($gpay_path = '') {
        // Push content to the absolute bottom of page, just above footer
        $this->Ln(22); // Maximum spacing to move content to absolute bottom
        
        // Save current Y position for all three columns
        $start_y = $this->GetY();
        
        // LEFT COLUMN - Bank Details (leftmost part)
        $this->SetXY(10, $start_y);
        $this->SetFont('Arial', 'B', 12); // Increased from 6 to 8
        $this->Cell(60, 5, 'BANK DETAILS:', 0, 1, 'L'); // Increased height from 3 to 5
        
        $this->SetX(10);
        $this->SetFont('Arial', 'B', 11); // Increased from 5 to 7
        $this->Cell(60, 4, 'ACCOUNT NAME:', 0, 1, 'L'); // Increased height from 2 to 4
        $this->SetX(10);
        $this->SetFont('Arial', '', 11); // Increased from 5 to 7
        $this->Cell(60, 4, 'PROPACK SOURCING INDIA', 0, 1, 'L'); // Increased height from 2 to 4
        
        $this->SetX(10);
        $this->SetFont('Arial', 'B', 11); // Increased from 5 to 7
        $this->Cell(60, 4, 'ACCOUNT NUMBER:', 0, 1, 'L'); // Increased height from 2 to 4
        $this->SetX(10);
        $this->SetFont('Arial', '', 11); // Increased from 5 to 7
        $this->Cell(60, 4, '50200090346107', 0, 1, 'L'); // Increased height from 2 to 4
        
        $this->SetX(10);
        $this->SetFont('Arial', 'B', 11); // Increased from 5 to 7
        $this->Cell(60, 4, 'BANK NAME:', 0, 1, 'L'); // Increased height from 2 to 4
        $this->SetX(10);
        $this->SetFont('Arial', '', 11); // Increased from 5 to 7
        $this->Cell(60, 4, 'HDFC BANK, KALAPATTI BRANCH', 0, 1, 'L'); // Increased height from 2 to 4
        
        $this->SetX(10);
        $this->SetFont('Arial', 'B', 11); // Increased from 5 to 7
        $this->Cell(60, 4, 'IFSC CODE:', 0, 1, 'L'); // Increased height from 2 to 4
        $this->SetX(10);
        $this->SetFont('Arial', '', 11); // Increased from 5 to 7
        $this->Cell(60, 4, 'HDFC0001068', 0, 1, 'L'); // Increased height from 2 to 4
        
        // MIDDLE COLUMN - G-Pay QR Code
        if (file_exists($gpay_path)) {
            // Position G-Pay in the middle
            $this->SetXY(90, $start_y);
            $this->SetFont('Arial', 'B', 5);
            $this->Cell(40, 2, 'Scan to Pay via G-Pay', 0, 1, 'C');
            
            // Add G-Pay QR code image in middle
            $this->Image($gpay_path, 95, $start_y + 3, 30, 30);
        } else {
            // If G-Pay image doesn't exist, show placeholder in middle
            $this->SetXY(90, $start_y);
            $this->SetFont('Arial', 'B', 5);
            $this->Cell(40, 2, 'G-Pay Payment', 0, 1, 'C');
            $this->SetXY(90, $start_y + 3);
            $this->Cell(40, 30, 'G-Pay QR Code\n(Image not found)', 1, 1, 'C');
        }
        
        // RIGHT COLUMN - Terms & Conditions
        $this->SetXY(140, $start_y);
        $this->SetFont('Arial', 'B', 11); // Increased from 5 to 7
        $this->Cell(60, 5, 'Terms & Conditions:', 0, 1, 'L'); // Increased height from 3 to 5
        
        $this->SetFont('Arial', '', 10); // Increased from 4 to 6
        $terms = [
            '1. Goods once sold cannot be taken',
            '   back or exchanged.',
            '2. Our responsibility ceases immediately',
            '   the goods are delivered.',
            '3. Payment within 30 days of invoice.',
            '4. Subject to Coimbatore Jurisdiction.',
            '5. All disputes subject to arbitration.'
        ];
        
        foreach ($terms as $term) {
            $this->SetX(140);
            $this->Cell(60, 4, $term, 0, 1, 'L'); // Increased height from 2 to 4 for better inline spacing
        }
    }
}

// Create PDF
$logo_path = __DIR__ . '/Sun.jpeg';
$qr_path = __DIR__ . '/QR.jpg';
$gpay_path = __DIR__ . '/g_pay.jpeg';

$pdf = new MalarInvoicePDF($logo_path, $qr_path);
$pdf->AddPage();

// Add content
$pdf->InvoiceDetails($invoice_number, $invoice_date, $po_number, $party_gstin, $mode_of_transport);
$pdf->AddressSection($customer_name, $customer_address, $customer_phone);
$pdf->MaterialsTable($materials_for_pdf);
$pdf->TotalsSection($price, $cgst_rate, $cgst_amount, $sgst_rate, $sgst_amount, $igst_rate, $igst_amount, $total);
$pdf->AmountInWords($amount_in_words);
$pdf->TermsAndSignature();
$pdf->BankDetailsSection($gpay_path);

// Output PDF
$pdf_filename = 'invoices/MALAR_Invoice_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $invoice_number) . '.pdf';
$output_file = __DIR__ . '/' . $pdf_filename;

$pdf->Output('F', $output_file);

// Store invoice details in the database
$stmt = mysqli_prepare($conn, "INSERT INTO invoices (invoice_number, customer_name, invoice_date, pdf_path, subtotal, cgst_rate, sgst_rate, igst_rate, cgst_amount, sgst_amount, igst_amount, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssssdddddddd", $invoice_number, $customer_name, $invoice_date_raw, $pdf_filename, $price, $cgst_rate, $sgst_rate, $igst_rate, $cgst_amount, $sgst_amount, $igst_amount, $total);

try {
    if (mysqli_stmt_execute($stmt)) {
        header("Location: customer_history.php?status=success&invoice=" . urlencode($invoice_number));
        exit();
    }
} catch (mysqli_sql_exception $e) {
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
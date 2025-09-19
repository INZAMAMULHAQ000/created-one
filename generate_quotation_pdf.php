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

// Process materials data
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

// Process quotation details
$quotation_number = $_POST['quotation_number'];
$quotation_date_raw = $_POST['date'];
$quotation_date = (new DateTime($quotation_date_raw))->format('d-m-Y');

// Process customer details
$customer_name = $_POST['customer_name'];
$customer_company = isset($_POST['customer_company']) ? $_POST['customer_company'] : '';
$customer_address = $_POST['customer_address'];
$customer_phone = $_POST['customer_phone'];
$contact_person = isset($_POST['contact_person']) ? $_POST['contact_person'] : '';
$valid_until = isset($_POST['valid_until']) ? (new DateTime($_POST['valid_until']))->format('d-m-Y') : '';

// Convert amount to words
$amount_in_words = amountInWords($price);

// Create PDF class extending FPDF for Sales Quotation
class MalarQuotationPDF extends FPDF
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
        
        // SALES QUOTATION title
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(220, 220, 220); // Light gray background
        $this->SetTextColor(0, 0, 0); // Black text
        $this->Cell(0, 10, 'SALES QUOTATION', 1, 1, 'C', true);
        $this->SetTextColor(0, 0, 0); // Reset to black text
        
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
    
    function QuotationDetails($quotation_number, $quotation_date, $contact_person, $valid_until) {
        $this->SetFont('Arial', '', 10);
        
        // Set light gray background for labels
        $this->SetFillColor(220, 220, 220);
        
        // Quotation details table
        $this->Cell(45, 8, 'Quotation No:', 1, 0, 'L', true);
        $this->Cell(45, 8, $quotation_number, 1, 0, 'L');
        $this->Cell(45, 8, 'Date:', 1, 0, 'L', true);
        $this->Cell(55, 8, $quotation_date, 1, 1, 'L');
        
        if ($contact_person || $valid_until) {
            $this->Cell(45, 8, 'Contact Person:', 1, 0, 'L', true);
            $this->Cell(45, 8, $contact_person, 1, 0, 'L');
            $this->Cell(45, 8, 'Valid Until:', 1, 0, 'L', true);
            $this->Cell(55, 8, $valid_until, 1, 1, 'L');
        }
        
        $this->Ln(5);
    }
    
    function AddressSection($customer_name, $customer_address, $customer_phone, $customer_company = '') {
        $this->SetFont('Arial', 'B', 8); // Reduced font size
        
        // Set light gray background for Customer Details header
        $this->SetFillColor(220, 220, 220); // Light gray background
        $this->SetTextColor(0, 0, 0); // Black text
        
        // Customer Details section
        $this->Cell(190, 6, 'CUSTOMER DETAILS', 1, 1, 'C', true); // Full width header
        
        // Reset to normal background for content
        $this->SetFillColor(255, 255, 255); // White background
        $this->SetFont('Arial', '', 7); // Smaller content font
        
        // Customer information
        $customer_info = $customer_name;
        if ($customer_company) {
            $customer_info .= "\nCompany: " . $customer_company;
        }
        $customer_info .= "\nAddress: " . $customer_address;
        $customer_info .= "\nPhone: " . $customer_phone;
        
        // Split customer info into lines
        $info_lines = explode("\n", $customer_info);
        
        foreach ($info_lines as $line) {
            $this->Cell(190, 4, $line, 1, 1, 'L'); // Full width for each line
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
    
    function TotalsSection($total) {
        $this->SetFont('Arial', 'B', 8); // Slightly larger font size for quotation total
        
        $x_start = 130;
        $this->SetX($x_start);
        
        // Grand Total
        $this->SetFillColor(180, 180, 180); // Gray background
        $this->SetTextColor(0, 0, 0); // Black text
        $this->Cell(30, 6, 'TOTAL:', 1, 0, 'L', true); // Slightly taller for emphasis
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
        
        // Terms & Conditions Section
        $this->SetFillColor(220, 220, 220); // Light gray background
        $this->Cell(190, 6, 'Terms & Conditions:', 1, 1, 'L', true);
        
        $this->SetFont('Arial', '', 6); // Smaller font for terms
        $terms = [
            '1. Goods once sold cannot be taken back or exchanged.',
            '2. Our responsibility ceases immediately the goods are delivered.',
            '3. Payment: 50% advance, balance on delivery.',
            '4. Delivery: 15-20 working days from receipt of order.',
            '5. GST extra as applicable.',
            '6. Subject to Coimbatore Jurisdiction.'
        ];
        
        foreach ($terms as $term) {
            $this->Cell(190, 3, $term, 1, 1, 'L');
        }
        
        $this->Ln(5);
        
        // Signature section
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(110, 4, '', 0, 0, 'L'); // Empty space for terms
        $this->Cell(80, 4, 'For MALAR PAPER BAGS', 0, 1, 'C');
        
        // Only signature box
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
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(60, 5, 'BANK DETAILS:', 0, 1, 'L');
        
        $this->SetX(10);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(60, 4, 'ACCOUNT NAME:', 0, 1, 'L');
        $this->SetX(10);
        $this->SetFont('Arial', '', 7);
        $this->Cell(60, 4, 'MALAR PAPER BAGS', 0, 1, 'L');
        
        $this->SetX(10);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(60, 4, 'ACCOUNT NUMBER:', 0, 1, 'L');
        $this->SetX(10);
        $this->SetFont('Arial', '', 7);
        $this->Cell(60, 4, '50200090346107', 0, 1, 'L');
        
        $this->SetX(10);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(60, 4, 'BANK NAME:', 0, 1, 'L');
        $this->SetX(10);
        $this->SetFont('Arial', '', 7);
        $this->Cell(60, 4, 'HDFC BANK, KALAPATTI BRANCH', 0, 1, 'L');
        
        $this->SetX(10);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(60, 4, 'IFSC CODE:', 0, 1, 'L');
        $this->SetX(10);
        $this->SetFont('Arial', '', 7);
        $this->Cell(60, 4, 'HDFC0001068', 0, 1, 'L');
        
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
        
        // RIGHT COLUMN - Note about GST
        $this->SetXY(140, $start_y);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(60, 5, 'Note:', 0, 1, 'L');
        
        $this->SetFont('Arial', '', 6);
        $note_text = [
            'GST will be added as per',
            'applicable rates at the time',
            'of final billing.',
            '',
            'This quotation is valid for',
            'the period mentioned above.'
        ];
        
        foreach ($note_text as $note) {
            $this->SetX(140);
            $this->Cell(60, 4, $note, 0, 1, 'L');
        }
    }
}

// Create PDF
$logo_path = __DIR__ . '/Sun.jpeg';
$qr_path = __DIR__ . '/QR.jpg';
$gpay_path = __DIR__ . '/g_pay.jpeg';

$pdf = new MalarQuotationPDF($logo_path, $qr_path);
$pdf->AddPage();

// Add content
$pdf->QuotationDetails($quotation_number, $quotation_date, $contact_person, $valid_until);
$pdf->AddressSection($customer_name, $customer_address, $customer_phone, $customer_company);
$pdf->MaterialsTable($materials_for_pdf);
$pdf->TotalsSection($price);
$pdf->AmountInWords($amount_in_words);
$pdf->TermsAndSignature();
$pdf->BankDetailsSection($gpay_path);

// Create quotations directory if it doesn't exist
if (!file_exists('quotations')) {
    mkdir('quotations', 0777, true);
}

// Output PDF
$pdf_filename = 'quotations/MALAR_Quotation_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $quotation_number) . '.pdf';
$output_file = __DIR__ . '/' . $pdf_filename;

$pdf->Output('F', $output_file);

// Store quotation details in the database
$stmt = mysqli_prepare($conn, "INSERT INTO sales_quotations (quotation_number, customer_name, quotation_date, pdf_path) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssss", $quotation_number, $customer_name, $quotation_date_raw, $pdf_filename);

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
?>

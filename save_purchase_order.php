<?php
session_start();
require_once "config/database.php";

if(!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

// Validate required POST data
if (!isset($_POST['selected_materials_data']) || empty($_POST['selected_materials_data'])) {
    header("Location: purchase_order.php?status=error&message=" . urlencode("No materials selected."));
    exit;
}

// Decode and validate materials data
$selected_materials_data = json_decode($_POST['selected_materials_data'], true);

if (json_last_error() !== JSON_ERROR_NONE) {
    header("Location: purchase_order.php?status=error&message=" . urlencode("Invalid material data provided."));
    exit;
}

if (empty($selected_materials_data)) {
    header("Location: purchase_order.php?status=error&message=" . urlencode("No materials selected for purchase order."));
    exit;
}

// Get and validate form data
$po_number = trim($_POST['po_number']);
if (empty($po_number)) {
    header("Location: purchase_order.php?status=error&message=" . urlencode("PO Number is required."));
    exit;
}

$po_date_raw = $_POST['date'];
$po_date = date('Y-m-d', strtotime($po_date_raw));

// Seller details
$seller_name = trim($_POST['seller_name']);
$seller_company = trim($_POST['seller_company']);
$seller_address = trim($_POST['seller_address']);
$seller_phone = trim($_POST['seller_phone']);
$seller_gst = trim($_POST['seller_gst']);
$seller_email = trim($_POST['seller_email']);

// Validate required fields
$required_fields = [
    'seller_name' => $seller_name,
    'seller_company' => $seller_company,
    'seller_address' => $seller_address,
    'seller_phone' => $seller_phone
];

foreach ($required_fields as $field => $value) {
    if (empty($value)) {
        $field_name = str_replace('_', ' ', ucfirst($field));
        header("Location: purchase_order.php?status=error&message=" . urlencode("{$field_name} is required."));
        exit;
    }
}

// Calculate totals
$subtotal = 0;
foreach ($selected_materials_data as $item) {
    $item_price_per_unit = floatval($item['price_per_unit']);
    $item_quantity = intval($item['quantity']);
    $item_subtotal = $item_price_per_unit * $item_quantity;
    $subtotal += $item_subtotal;
}

// GST calculations
$cgst_rate = isset($_POST['cgst_rate']) ? floatval($_POST['cgst_rate']) : 0;
$sgst_rate = isset($_POST['sgst_rate']) ? floatval($_POST['sgst_rate']) : 0;
$igst_rate = isset($_POST['igst_rate']) ? floatval($_POST['igst_rate']) : 0;

$cgst_amount = ($subtotal * $cgst_rate) / 100;
$sgst_amount = ($subtotal * $sgst_rate) / 100;
$igst_amount = ($subtotal * $igst_rate) / 100;
$total_amount = $subtotal + $cgst_amount + $sgst_amount + $igst_amount;

// Convert materials data to JSON for storage
$materials_json = json_encode($selected_materials_data, JSON_UNESCAPED_UNICODE);

// Validate JSON encoding
if ($materials_json === false) {
    error_log("JSON encoding failed: " . json_last_error_msg());
    header("Location: purchase_order.php?status=error&message=" . urlencode("Error processing materials data."));
    exit;
}

// Escape the JSON string for direct insertion
$escaped_materials_json = mysqli_real_escape_string($conn, $materials_json);

// Use direct SQL query for better JSON handling
$sql = "INSERT INTO purchase_orders (
    po_number, po_date,
    seller_name, seller_company, seller_address, seller_phone, seller_gst, seller_email,
    materials_data, subtotal,
    cgst_rate, sgst_rate, igst_rate, cgst_amount, sgst_amount, igst_amount,
    total_amount
) VALUES (
    '" . mysqli_real_escape_string($conn, $po_number) . "', 
    '" . $po_date . "',
    '" . mysqli_real_escape_string($conn, $seller_name) . "', 
    '" . mysqli_real_escape_string($conn, $seller_company) . "', 
    '" . mysqli_real_escape_string($conn, $seller_address) . "', 
    '" . mysqli_real_escape_string($conn, $seller_phone) . "', 
    '" . mysqli_real_escape_string($conn, $seller_gst) . "', 
    '" . mysqli_real_escape_string($conn, $seller_email) . "',
    '$escaped_materials_json', 
    $subtotal,
    $cgst_rate, $sgst_rate, $igst_rate, $cgst_amount, $sgst_amount, $igst_amount,
    $total_amount
)";

// Execute the query
try {
    if (mysqli_query($conn, $sql)) {
        $po_id = mysqli_insert_id($conn);
        mysqli_close($conn);
        
        // Redirect to purchase order history with success message
        header("Location: purchase_order_history.php?status=success&po=" . urlencode($po_number));
        exit();
    } else {
        throw new Exception("Failed to execute query: " . mysqli_error($conn));
    }
} catch (mysqli_sql_exception $e) {
    mysqli_close($conn);
    
    // Check for duplicate entry error
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        header("Location: purchase_order.php?status=error&message=" . urlencode("PO number '{$po_number}' already exists. Please use a different one."));
        exit();
    } else {
        error_log("Error inserting purchase order into database: " . $e->getMessage());
        header("Location: purchase_order.php?status=error&message=" . urlencode("Database error occurred. Please try again."));
        exit();
    }
} catch (Exception $e) {
    mysqli_close($conn);
    
    error_log("General error saving purchase order: " . $e->getMessage());
    header("Location: purchase_order.php?status=error&message=" . urlencode("An error occurred while saving the purchase order."));
    exit();
}
?>
<?php
// Test script to validate quotation system
session_start();
require_once "config/database.php";

// Simulate login session
$_SESSION['loggedin'] = true;

echo "<h2>Testing Quotation System</h2>";

// Test 1: Check if materials table exists and has data
echo "<h3>Test 1: Materials Data</h3>";
$materials_query = "SELECT COUNT(*) as count FROM materials";
$materials_result = mysqli_query($conn, $materials_query);
$materials_count = mysqli_fetch_assoc($materials_result)['count'];
echo "Materials in database: " . $materials_count . "<br>";

// Test 2: Check if sales_quotations table exists
echo "<h3>Test 2: Sales Quotations Table</h3>";
$table_check = "SHOW TABLES LIKE 'sales_quotations'";
$table_result = mysqli_query($conn, $table_check);
if (mysqli_num_rows($table_result) > 0) {
    echo "✓ sales_quotations table exists<br>";
    
    // Check table structure
    $structure_query = "DESCRIBE sales_quotations";
    $structure_result = mysqli_query($conn, $structure_query);
    echo "Table structure:<br>";
    while ($row = mysqli_fetch_assoc($structure_result)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
    }
} else {
    echo "✗ sales_quotations table does not exist<br>";
}

// Test 3: Check if required files exist
echo "<h3>Test 3: Required Files</h3>";
$required_files = [
    'sales_quotation.php',
    'generate_quotation_pdf.php',
    'js/customer-dropdown.js',
    'includes/sidebar.js',
    'includes/sidebar.css'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✓ {$file} exists<br>";
    } else {
        echo "✗ {$file} missing<br>";
    }
}

// Test 4: Check if QR code and logo files exist
echo "<h3>Test 4: Image Assets</h3>";
$image_files = ['QR.jpg', 'Sun.jpeg'];
foreach ($image_files as $file) {
    if (file_exists($file)) {
        echo "✓ {$file} exists<br>";
    } else {
        echo "✗ {$file} missing (PDF will work without images)<br>";
    }
}

echo "<h3>Test Complete</h3>";
echo "<p><a href='sales_quotation.php'>→ Go to Sales Quotation Form</a></p>";
?>
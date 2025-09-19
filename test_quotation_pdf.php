<?php
// Test the new quotation PDF styling
session_start();
require_once "config/database.php";

// Simulate login session for testing
$_SESSION['loggedin'] = true;

// Test data
$test_data = [
    'quotation_number' => 'TEST-001',
    'date' => date('Y-m-d'),
    'customer_name' => 'Test Customer Ltd',
    'customer_company' => 'Test Company Pvt Ltd',
    'customer_address' => "123 Test Street\nTest City, Test State\nPIN: 123456",
    'customer_phone' => '+91 9876543210',
    'contact_person' => 'John Doe',
    'valid_until' => date('Y-m-d', strtotime('+30 days')),
    'selected_materials_data' => json_encode([
        [
            'id' => 1,
            'name' => 'Test Material 1',
            'hsn_code' => '12345678',
            'price_per_unit' => 100.00,
            'quantity' => 5
        ],
        [
            'id' => 2,
            'name' => 'Test Material 2',
            'hsn_code' => '87654321',
            'price_per_unit' => 250.00,
            'quantity' => 2
        ]
    ])
];

echo "<h2>Testing New Quotation PDF Structure</h2>";
echo "<h3>Test Data:</h3>";
echo "<pre>" . print_r($test_data, true) . "</pre>";

echo "<h3>Actions:</h3>";
echo "<form method='post' action='generate_quotation_pdf.php' target='_blank'>";
foreach ($test_data as $key => $value) {
    echo "<input type='hidden' name='{$key}' value='" . htmlspecialchars($value) . "'>";
}
echo "<button type='submit' style='padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;'>Generate Test Quotation PDF</button>";
echo "</form>";

echo "<p><a href='sales_quotation.php'>→ Go to Sales Quotation Form</a></p>";
echo "<p><a href='test_quotation_system.php'>→ Run System Tests</a></p>";

// Check if images exist
echo "<h3>Image Assets Status:</h3>";
$images = ['QR.jpg' => 'QR Code', 'Sun.jpeg' => 'Company Logo'];
foreach ($images as $file => $desc) {
    if (file_exists($file)) {
        echo "✓ {$desc} ({$file}) - Available<br>";
    } else {
        echo "⚠ {$desc} ({$file}) - Missing (PDF will work without it)<br>";
    }
}
?>
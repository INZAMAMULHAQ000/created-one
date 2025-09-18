<?php
require_once "config/database.php";

echo "<h2>Updating Purchase Orders Table Structure</h2>";

// First, let's check if the table exists and what columns it has
$check_table = "SHOW TABLES LIKE 'purchase_orders'";
$result = mysqli_query($conn, $check_table);

if (mysqli_num_rows($result) > 0) {
    echo "Purchase orders table exists. Checking current structure...<br>";
    
    // Get current columns
    $columns_query = "SHOW COLUMNS FROM purchase_orders";
    $columns_result = mysqli_query($conn, $columns_query);
    $existing_columns = [];
    
    while ($column = mysqli_fetch_assoc($columns_result)) {
        $existing_columns[] = $column['Field'];
    }
    
    echo "Existing columns: " . implode(', ', $existing_columns) . "<br><br>";
    
    // Disable foreign key checks temporarily
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
    
    // Drop the table and recreate with new structure
    echo "Dropping existing table to recreate with new structure...<br>";
    $drop_sql = "DROP TABLE purchase_orders";
    if (mysqli_query($conn, $drop_sql)) {
        echo "Table dropped successfully.<br>";
    } else {
        echo "Error dropping table: " . mysqli_error($conn) . "<br>";
    }
    
    // Re-enable foreign key checks
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");
}

// Create the new purchase_orders table with comprehensive structure
$sql = "CREATE TABLE purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(255) NOT NULL UNIQUE,
    po_date DATE NOT NULL,
    
    -- Seller Details
    seller_name VARCHAR(255) NOT NULL,
    seller_company VARCHAR(255) NOT NULL,
    seller_address TEXT NOT NULL,
    seller_phone VARCHAR(20) NOT NULL,
    seller_gst VARCHAR(50),
    seller_email VARCHAR(255),
    
    -- Buyer Details
    buyer_name VARCHAR(255) NOT NULL,
    buyer_company VARCHAR(255) NOT NULL,
    buyer_address TEXT NOT NULL,
    buyer_phone VARCHAR(20) NOT NULL,
    buyer_gst VARCHAR(50),
    buyer_email VARCHAR(255),
    
    -- Materials and Pricing
    materials_data JSON NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    
    -- GST Details
    cgst_rate DECIMAL(5,2) DEFAULT 0.00,
    sgst_rate DECIMAL(5,2) DEFAULT 0.00,
    igst_rate DECIMAL(5,2) DEFAULT 0.00,
    cgst_amount DECIMAL(15,2) DEFAULT 0.00,
    sgst_amount DECIMAL(15,2) DEFAULT 0.00,
    igst_amount DECIMAL(15,2) DEFAULT 0.00,
    
    -- Total
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    
    -- System fields
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $sql)) {
    echo "New purchase_orders table created successfully with comprehensive structure.<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

// Create index on po_number for better performance
$index_sql = "CREATE INDEX idx_po_number ON purchase_orders(po_number)";
if (mysqli_query($conn, $index_sql)) {
    echo "Index on po_number created successfully.<br>";
} else {
    echo "Error creating index: " . mysqli_error($conn) . "<br>";
}

// Create index on po_date for better performance in date-based queries
$date_index_sql = "CREATE INDEX idx_po_date ON purchase_orders(po_date)";
if (mysqli_query($conn, $date_index_sql)) {
    echo "Index on po_date created successfully.<br>";
} else {
    echo "Error creating date index: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
echo "<br><h3>Table structure update completed!</h3>";
echo "<a href='purchase_order.php'>Go to Purchase Order</a> | ";
echo "<a href='purchase_order_history.php'>Go to Purchase Order History</a>";
?>
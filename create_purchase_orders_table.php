<?php
require_once "config/database.php";

$sql = "CREATE TABLE IF NOT EXISTS purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(255) NOT NULL UNIQUE,
    seller_name VARCHAR(255) NOT NULL,
    seller_company VARCHAR(255) NOT NULL,
    buyer_name VARCHAR(255) NOT NULL,
    buyer_company VARCHAR(255) NOT NULL,
    po_date DATE NOT NULL,
    pdf_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($conn, $sql)) {
    echo "Table purchase_orders created successfully or already exists.<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

// Also create the purchase_orders directory if it doesn't exist
$purchase_orders_dir = __DIR__ . '/purchase_orders';
if (!is_dir($purchase_orders_dir)) {
    if (mkdir($purchase_orders_dir, 0755, true)) {
        echo "Directory 'purchase_orders' created successfully.<br>";
    } else {
        echo "Error creating directory 'purchase_orders'.<br>";
    }
} else {
    echo "Directory 'purchase_orders' already exists.<br>";
}

mysqli_close($conn);
echo "<br><a href='purchase_order.php'>Go to Purchase Order</a>";
?>

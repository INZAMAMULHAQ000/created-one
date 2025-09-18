<?php
require_once "config/database.php";

$sql = "CREATE TABLE IF NOT EXISTS sales_quotations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_number VARCHAR(255) NOT NULL UNIQUE,
    customer_name VARCHAR(255) NOT NULL,
    quotation_date DATE NOT NULL,
    pdf_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

if (mysqli_query($conn, $sql)) {
    echo "Table 'sales_quotations' created successfully.";
} else {
    echo "Error creating table: " . mysqli_error($conn);
}

mysqli_close($conn);
?>

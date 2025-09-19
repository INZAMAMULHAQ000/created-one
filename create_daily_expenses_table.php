<?php
require_once "config/database.php";

$sql = "CREATE TABLE IF NOT EXISTS daily_expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_date DATE NOT NULL UNIQUE,
    purchase_order DECIMAL(10, 2) DEFAULT 0.00,
    salary DECIMAL(10, 2),
    printing_services DECIMAL(10, 2),
    petrol_expense DECIMAL(10, 2),
    other_expense_1 DECIMAL(10, 2),
    other_expense_2 DECIMAL(10, 2),
    other_expense_3 DECIMAL(10, 2),
    pdf_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

if (mysqli_query($conn, $sql)) {
    echo "Table 'daily_expenses' created successfully.";
} else {
    echo "Error creating table: " . mysqli_error($conn);
}

mysqli_close($conn);
?>

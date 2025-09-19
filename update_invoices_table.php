<?php
require_once "config/database.php";

echo "Updating invoices table to include billing amounts...\n";

// Add new columns to store billing totals and GST details
$alterSql = "ALTER TABLE invoices 
ADD COLUMN IF NOT EXISTS subtotal DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS cgst_rate DECIMAL(5,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS sgst_rate DECIMAL(5,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS igst_rate DECIMAL(5,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS cgst_amount DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS sgst_amount DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS igst_amount DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS total_amount DECIMAL(10,2) DEFAULT 0.00";

if (mysqli_query($conn, $alterSql)) {
    echo "✓ Successfully added new columns to invoices table.\n";
} else {
    echo "✗ Error adding columns: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
echo "Migration completed.\n";
?>
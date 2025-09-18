<?php
require_once "config/database.php";

echo "Updating daily_expenses table structure...<br>";

// First, check if the table exists
$check_table = "SHOW TABLES LIKE 'daily_expenses'";
$result = mysqli_query($conn, $check_table);

if (mysqli_num_rows($result) > 0) {
    // Check current column type
    $check_column = "DESCRIBE daily_expenses purchase_order";
    $column_result = mysqli_query($conn, $check_column);
    
    if ($column_result && mysqli_num_rows($column_result) > 0) {
        $column_info = mysqli_fetch_assoc($column_result);
        echo "Current purchase_order column type: " . $column_info['Type'] . "<br>";
        
        // If it's not already DECIMAL, update it
        if (strpos(strtolower($column_info['Type']), 'decimal') === false) {
            echo "Updating purchase_order column to DECIMAL(10,2)...<br>";
            
            $update_sql = "ALTER TABLE daily_expenses MODIFY COLUMN purchase_order DECIMAL(10, 2) DEFAULT 0.00";
            
            if (mysqli_query($conn, $update_sql)) {
                echo "Successfully updated purchase_order column to DECIMAL(10,2)<br>";
            } else {
                echo "Error updating column: " . mysqli_error($conn) . "<br>";
            }
        } else {
            echo "Column is already DECIMAL type, no update needed.<br>";
        }
    } else {
        echo "Column purchase_order not found, adding it...<br>";
        $add_column = "ALTER TABLE daily_expenses ADD COLUMN purchase_order DECIMAL(10, 2) DEFAULT 0.00";
        
        if (mysqli_query($conn, $add_column)) {
            echo "Successfully added purchase_order column<br>";
        } else {
            echo "Error adding column: " . mysqli_error($conn) . "<br>";
        }
    }
} else {
    echo "Table daily_expenses does not exist. Please run create_daily_expenses_table.php first.<br>";
}

mysqli_close($conn);
echo "<br><a href='daily_expenses.php'>Go to Daily Expenses</a>";
?>
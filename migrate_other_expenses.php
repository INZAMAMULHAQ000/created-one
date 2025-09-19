<?php
require_once "config/database.php";

echo "Updating daily_expenses table to add other expense fields...<br>";

// First, check if the table exists
$check_table = "SHOW TABLES LIKE 'daily_expenses'";
$result = mysqli_query($conn, $check_table);

if (mysqli_num_rows($result) > 0) {
    // Check if the new columns exist
    $check_columns = "DESCRIBE daily_expenses";
    $columns_result = mysqli_query($conn, $check_columns);
    
    $existing_columns = array();
    if ($columns_result) {
        while ($row = mysqli_fetch_assoc($columns_result)) {
            $existing_columns[] = $row['Field'];
        }
    }
    
    // Add other_expense_1 if it doesn't exist
    if (!in_array('other_expense_1', $existing_columns)) {
        echo "Adding other_expense_1 column...<br>";
        $add_column1 = "ALTER TABLE daily_expenses ADD COLUMN other_expense_1 DECIMAL(10, 2) DEFAULT 0.00";
        if (mysqli_query($conn, $add_column1)) {
            echo "Successfully added other_expense_1 column<br>";
        } else {
            echo "Error adding other_expense_1 column: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "other_expense_1 column already exists<br>";
    }
    
    // Add other_expense_2 if it doesn't exist
    if (!in_array('other_expense_2', $existing_columns)) {
        echo "Adding other_expense_2 column...<br>";
        $add_column2 = "ALTER TABLE daily_expenses ADD COLUMN other_expense_2 DECIMAL(10, 2) DEFAULT 0.00";
        if (mysqli_query($conn, $add_column2)) {
            echo "Successfully added other_expense_2 column<br>";
        } else {
            echo "Error adding other_expense_2 column: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "other_expense_2 column already exists<br>";
    }
    
    // Add other_expense_3 if it doesn't exist
    if (!in_array('other_expense_3', $existing_columns)) {
        echo "Adding other_expense_3 column...<br>";
        $add_column3 = "ALTER TABLE daily_expenses ADD COLUMN other_expense_3 DECIMAL(10, 2) DEFAULT 0.00";
        if (mysqli_query($conn, $add_column3)) {
            echo "Successfully added other_expense_3 column<br>";
        } else {
            echo "Error adding other_expense_3 column: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "other_expense_3 column already exists<br>";
    }
    
    // Remove old other_expense TEXT column if it exists
    if (in_array('other_expense', $existing_columns)) {
        echo "Removing old other_expense TEXT column...<br>";
        $drop_column = "ALTER TABLE daily_expenses DROP COLUMN other_expense";
        if (mysqli_query($conn, $drop_column)) {
            echo "Successfully removed old other_expense column<br>";
        } else {
            echo "Error removing old other_expense column: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "Old other_expense column doesn't exist<br>";
    }
    
} else {
    echo "Table daily_expenses does not exist. Please run create_daily_expenses_table.php first.<br>";
}

mysqli_close($conn);
echo "<br><a href='daily_expenses.php'>Go to Daily Expenses</a>";
?>
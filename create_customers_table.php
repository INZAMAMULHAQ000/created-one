<?php
require_once "config/database.php";

// Drop existing table if exists and create new one
$drop_sql = "DROP TABLE IF EXISTS customers";
mysqli_query($conn, $drop_sql);

// Create customers table
$sql = "CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_company VARCHAR(255) DEFAULT NULL,
    phone_no VARCHAR(20) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_name (customer_name),
    INDEX idx_phone_no (phone_no),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($conn, $sql)) {
    echo "Customers table created successfully!\n";
    
    // Check if table exists and has correct structure
    $check_sql = "DESCRIBE customers";
    $result = mysqli_query($conn, $check_sql);
    if ($result) {
        echo "Table structure verified!\n";
        
        // Insert some sample data for testing
        $sample_data = "INSERT INTO customers (customer_name, customer_company, phone_no, email, address) VALUES
            ('John Doe', 'ABC Corporation', '9876543210', 'john@abc.com', '123 Business Street, City - 560001'),
            ('Jane Smith', 'XYZ Enterprises', '8765432109', 'jane@xyz.com', '456 Commercial Road, City - 560002'),
            ('Raj Kumar', 'Kumar Industries', '7654321098', 'raj@kumar.com', '789 Industrial Area, City - 560003'),
            ('Priya Sharma', NULL, '6543210987', 'priya@gmail.com', '321 Residential Area, City - 560004'),
            ('Michael Johnson', 'Global Solutions', '5432109876', 'michael@global.com', '654 Tech Park, City - 560005')";
        
        if (mysqli_query($conn, $sample_data)) {
            echo "Sample customer data inserted successfully!\n";
        } else {
            echo "Error inserting sample data: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "Error checking table structure: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "Error creating customers table: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
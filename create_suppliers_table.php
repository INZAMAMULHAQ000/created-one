<?php
require_once "config/database.php";

// Drop existing table if exists and create new one
$drop_sql = "DROP TABLE IF EXISTS suppliers";
mysqli_query($conn, $drop_sql);

// Create suppliers table
$sql = "CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(255) NOT NULL,
    supplier_company VARCHAR(255) DEFAULT NULL,
    phone_no VARCHAR(20) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    address TEXT NOT NULL,
    gst_id VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_supplier_name (supplier_name),
    INDEX idx_phone_no (phone_no),
    INDEX idx_email (email),
    INDEX idx_gst_id (gst_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($conn, $sql)) {
    echo "Suppliers table created successfully!\n";
    
    // Check if table exists and has correct structure
    $check_sql = "DESCRIBE suppliers";
    $result = mysqli_query($conn, $check_sql);
    if ($result) {
        echo "Table structure verified!\n";
        
        // Insert some sample data for testing
        $sample_data = "INSERT INTO suppliers (supplier_name, supplier_company, phone_no, email, address, gst_id) VALUES
            ('Raj Materials', 'Raj Industries Ltd', '9876543210', 'raj@rajindustries.com', '123 Industrial Area, Bangalore - 560001', '29RAJIN1234A1Z5'),
            ('ABC Suppliers', 'ABC Corporation', '8765432109', 'info@abcsuppliers.com', '456 Commercial Complex, Bangalore - 560002', '29ABCCO5678B2Y6'),
            ('Kumar Enterprises', 'Kumar Group', '7654321098', 'kumar@kumargroup.com', '789 Business Park, Bangalore - 560003', '29KUMAR9012C3X7'),
            ('Global Materials', 'Global Solutions Pvt Ltd', '6543210987', 'sales@globalmaterials.com', '321 Export House, Bangalore - 560004', '29GLOBAL3456D4W8'),
            ('Prime Suppliers', NULL, '5432109876', 'prime@gmail.com', '654 Main Road, Bangalore - 560005', '29PRIME7890E5V9')";
        
        if (mysqli_query($conn, $sample_data)) {
            echo "Sample supplier data inserted successfully!\n";
        } else {
            echo "Error inserting sample data: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "Error checking table structure: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "Error creating suppliers table: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
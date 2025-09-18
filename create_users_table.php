<?php
require_once "config/database.php";

$sql = "CREATE TABLE users (\n    id INT AUTO_INCREMENT PRIMARY KEY,\n    username VARCHAR(255) NOT NULL UNIQUE,\n    password VARCHAR(255) NOT NULL\n);";

if (mysqli_query($conn, $sql)) {
    echo "Table users created successfully.\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}

// Add a default admin user with a hashed password
$default_username = 'admin';
$default_password = 'admin123'; // This will be hashed
$hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

// Check if default admin user already exists
$check_user_sql = "SELECT id FROM users WHERE username = '$default_username'";
$result = mysqli_query($conn, $check_user_sql);

if (mysqli_num_rows($result) == 0) {
    $insert_admin_sql = "INSERT INTO users (username, password) VALUES ('$default_username', '$hashed_password')";
    if (mysqli_query($conn, $insert_admin_sql)) {
        echo "Default admin user inserted successfully.\n";
    } else {
        echo "Error inserting default admin user: " . mysqli_error($conn) . "\n";
    }
} else {
    echo "Default admin user already exists. Skipping insertion.\n";
}

mysqli_close($conn);
?> 
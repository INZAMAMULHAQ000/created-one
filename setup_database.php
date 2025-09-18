<?php
// Database configuration for XAMPP
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Create connection without database
    $conn = new mysqli($host, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Read SQL file
    $sql = file_get_contents('database.sql');

    // Execute multi query
    if ($conn->multi_query($sql)) {
        do {
            // Store first result set
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
    }

    echo "Database setup completed successfully! <br>";
    echo "You can now access the system at: <a href='index.php'>Login Page</a>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 
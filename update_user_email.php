<?php
require_once "config/database.php";

// Update the email for the 'admin' user
$sql = "UPDATE users SET email = 'ssenterpriseserp@gmail.com' WHERE username = 'admin'";

if (mysqli_query($conn, $sql)) {
    echo "Email updated successfully for user 'admin'.";
} else {
    echo "Error updating email: " . mysqli_error($conn);
}

mysqli_close($conn);
?> 
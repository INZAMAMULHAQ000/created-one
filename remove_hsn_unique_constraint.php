<?php
require_once "config/database.php";

$sql = "ALTER TABLE materials DROP INDEX hsn_code;";

if (mysqli_query($conn, $sql)) {
    echo "UNIQUE constraint on hsn_code removed successfully from materials table.";
} else {
    echo "Error removing UNIQUE constraint: " . mysqli_error($conn);
}

mysqli_close($conn);
?> 
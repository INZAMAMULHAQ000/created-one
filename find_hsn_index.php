<?php
require_once "config/database.php";

$query = "SHOW INDEX FROM materials WHERE Column_name = 'hsn_code';";
$result = mysqli_query($conn, $query);

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        echo "<pre>Found index for hsn_code:\n";
        while ($row = mysqli_fetch_assoc($result)) {
            print_r($row);
        }
        echo "</pre>";
    } else {
        echo "No index found for hsn_code on materials table. It might have already been removed or never existed as a unique index.";
    }
} else {
    echo "Error querying database: " . mysqli_error($conn);
}

mysqli_close($conn);
?> 
<?php
session_start();
require_once "config/database.php";

header('Content-Type: application/json');

$response = ['valid' => false];

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['username'])) {
    echo json_encode(['valid' => false, 'message' => 'Not logged in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['old_password'])) {
    $username = $_SESSION['username'];
    $old_password_attempt = $_POST['old_password'];

    $sql = "SELECT password FROM users WHERE username = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $param_username);
        $param_username = $username;

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $hashed_password);
                mysqli_stmt_fetch($stmt);

                if (password_verify($old_password_attempt, $hashed_password)) {
                    $response['valid'] = true;
                }
            }
        }
        mysqli_stmt_close($stmt);
    }
}
mysqli_close($conn);
echo json_encode($response);
?> 
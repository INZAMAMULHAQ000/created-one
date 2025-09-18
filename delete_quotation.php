<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quotation_number'])) {
    $quotation_number = $_POST['quotation_number'];
    
    // Fetch the PDF path for this quotation
    $stmt = mysqli_prepare($conn, "SELECT pdf_path FROM sales_quotations WHERE quotation_number = ?");
    mysqli_stmt_bind_param($stmt, "s", $quotation_number);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $pdf_path);
    
    if (mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        
        // Delete the quotation record
        $stmt_del = mysqli_prepare($conn, "DELETE FROM sales_quotations WHERE quotation_number = ?");
        mysqli_stmt_bind_param($stmt_del, "s", $quotation_number);
        $success = mysqli_stmt_execute($stmt_del);
        mysqli_stmt_close($stmt_del);
        
        // Delete the PDF file
        if ($success && $pdf_path && file_exists($pdf_path)) {
            unlink($pdf_path);
        }
        
        header("Location: quotation_history.php?status=deleted");
        exit;
    } else {
        mysqli_stmt_close($stmt);
        header("Location: quotation_history.php?status=error");
        exit;
    }
} else {
    header("Location: quotation_history.php");
    exit;
}
?>

<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['expense_id'])) {
    $expense_id = $_POST['expense_id'];
    
    // Fetch the PDF path for this expense
    $stmt = mysqli_prepare($conn, "SELECT pdf_path FROM daily_expenses WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $expense_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $pdf_path);
    
    if (mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        
        // Delete the expense record
        $stmt_del = mysqli_prepare($conn, "DELETE FROM daily_expenses WHERE id = ?");
        mysqli_stmt_bind_param($stmt_del, "i", $expense_id);
        $success = mysqli_stmt_execute($stmt_del);
        mysqli_stmt_close($stmt_del);
        
        // Delete the PDF file
        if ($success && $pdf_path && file_exists($pdf_path)) {
            unlink($pdf_path);
        }
        
        header("Location: expense_history.php?status=deleted");
        exit;
    } else {
        mysqli_stmt_close($stmt);
        header("Location: expense_history.php?status=error");
        exit;
    }
} else {
    header("Location: expense_history.php");
    exit;
}
?>

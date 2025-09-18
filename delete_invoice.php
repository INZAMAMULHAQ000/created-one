<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invoice_number'])) {
    $invoice_number = $_POST['invoice_number'];
    // Fetch the PDF path for this invoice
    $stmt = mysqli_prepare($conn, "SELECT pdf_path FROM invoices WHERE invoice_number = ?");
    mysqli_stmt_bind_param($stmt, "s", $invoice_number);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $pdf_path);
    if (mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        // Delete the invoice record
        $stmt_del = mysqli_prepare($conn, "DELETE FROM invoices WHERE invoice_number = ?");
        mysqli_stmt_bind_param($stmt_del, "s", $invoice_number);
        $success = mysqli_stmt_execute($stmt_del);
        mysqli_stmt_close($stmt_del);
        // Delete the PDF file
        $file_deleted = false;
        if ($success && $pdf_path && file_exists($pdf_path)) {
            $file_deleted = unlink($pdf_path);
        } elseif ($success && $pdf_path && file_exists(__DIR__ . '/' . $pdf_path)) {
            $file_deleted = unlink(__DIR__ . '/' . $pdf_path);
        }
        $status = $success ? 'success' : 'error';
        $msg = $success ? 'Invoice deleted successfully.' : 'Failed to delete invoice.';
        if ($success && !$file_deleted) {
            $msg .= ' (PDF file not found or could not be deleted)';
        }
        header("Location: customer_history.php?status=$status&message=" . urlencode($msg));
        exit;
    } else {
        mysqli_stmt_close($stmt);
        header("Location: customer_history.php?status=error&message=" . urlencode('Invoice not found.'));
        exit;
    }
} else {
    header("Location: customer_history.php?status=error&message=" . urlencode('Invalid request.'));
    exit;
} 
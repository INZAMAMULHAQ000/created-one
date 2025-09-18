<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION['loggedin'])) {
    header("location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['po_id']) && isset($_POST['po_number'])) {
    $po_id = intval($_POST['po_id']);
    $po_number = $_POST['po_number'];
    
    // Delete the purchase order record by ID
    $stmt = mysqli_prepare($conn, "DELETE FROM purchase_orders WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $po_id);
    $success = mysqli_stmt_execute($stmt);
    $affected_rows = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
    if ($success && $affected_rows > 0) {
        header("Location: purchase_order_history.php?status=success&message=" . urlencode("Purchase Order '{$po_number}' deleted successfully."));
    } else {
        header("Location: purchase_order_history.php?status=error&message=" . urlencode("Failed to delete purchase order or purchase order not found."));
    }
    exit;
} else {
    header("Location: purchase_order_history.php?status=error&message=" . urlencode('Invalid request.'));
    exit;
}
?>

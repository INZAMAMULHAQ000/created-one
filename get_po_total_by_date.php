<?php
header('Content-Type: application/json');
require_once "config/database.php";

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$response = array('success' => false, 'total' => 0, 'count' => 0, 'details' => array());

if (isset($_GET['date']) && !empty($_GET['date'])) {
    $date = $_GET['date'];
    
    // Validate date format
    if (DateTime::createFromFormat('Y-m-d', $date) !== false) {
        // Query to get all purchase orders for the specific date
        $sql = "SELECT po_number, total_amount FROM purchase_orders WHERE po_date = ? ORDER BY po_number";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $date);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $total_amount = 0;
            $po_count = 0;
            $po_details = array();
            
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $total_amount += floatval($row['total_amount']);
                    $po_count++;
                    $po_details[] = array(
                        'po_number' => $row['po_number'],
                        'amount' => floatval($row['total_amount'])
                    );
                }
                
                $response['success'] = true;
                $response['total'] = $total_amount;
                $response['count'] = $po_count;
                $response['details'] = $po_details;
                $response['message'] = "Found {$po_count} purchase orders for {$date} with total amount: ₹" . number_format($total_amount, 2);
            }
            
            mysqli_stmt_close($stmt);
        } else {
            $response['error'] = 'Database query preparation failed';
        }
    } else {
        $response['error'] = 'Invalid date format. Please use YYYY-MM-DD format.';
    }
} else {
    $response['error'] = 'Date parameter is required';
}

mysqli_close($conn);
echo json_encode($response);
?>
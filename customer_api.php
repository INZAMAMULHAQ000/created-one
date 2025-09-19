<?php
// Check if session is needed (for authentication)
session_start();

header('Content-Type: application/json');
require_once "config/database.php";

// Allow both authenticated and unauthenticated access for now (can be restricted later)
// if (!isset($_SESSION['loggedin'])) {
//     echo json_encode(['success' => false, 'message' => 'Not authenticated']);
//     exit;
// }

$response = ['success' => false];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get all customers for dropdown
        if (isset($_GET['action']) && $_GET['action'] === 'get_all') {
            $sql = "SELECT id, customer_name, customer_company, phone_no, email, gst, address FROM customers ORDER BY customer_name ASC";
            $result = mysqli_query($conn, $sql);
            
            if ($result) {
                $customers = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $customers[] = $row;
                }
                $response['success'] = true;
                $response['customers'] = $customers;
            } else {
                $response['message'] = 'Error fetching customers: ' . mysqli_error($conn);
            }
        }
        // Get customer by ID
        elseif (isset($_GET['action']) && $_GET['action'] === 'get_by_id' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $sql = "SELECT id, customer_name, customer_company, phone_no, email, gst, address FROM customers WHERE id = $id";
            $result = mysqli_query($conn, $sql);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $customer = mysqli_fetch_assoc($result);
                $response['success'] = true;
                $response['customer'] = $customer;
            } else {
                $response['message'] = 'Customer not found';
            }
        }
        // Search customers by name or company
        elseif (isset($_GET['action']) && $_GET['action'] === 'search' && isset($_GET['term'])) {
            $term = mysqli_real_escape_string($conn, $_GET['term']);
            $sql = "SELECT id, customer_name, customer_company, phone_no, email, gst, address 
                    FROM customers 
                    WHERE customer_name LIKE '%$term%' 
                       OR customer_company LIKE '%$term%' 
                       OR phone_no LIKE '%$term%'
                    ORDER BY customer_name ASC 
                    LIMIT 20";
            $result = mysqli_query($conn, $sql);
            
            if ($result) {
                $customers = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $customers[] = $row;
                }
                $response['success'] = true;
                $response['customers'] = $customers;
            } else {
                $response['message'] = 'Error searching customers: ' . mysqli_error($conn);
            }
        } else {
            $response['message'] = 'Invalid action or missing parameters';
        }
    } else {
        $response['message'] = 'Only GET requests are allowed';
    }
} catch (Exception $e) {
    $response['message'] = 'Server error: ' . $e->getMessage();
}

mysqli_close($conn);
echo json_encode($response);
?>
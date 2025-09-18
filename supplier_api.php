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
        // Get all suppliers for dropdown
        if (isset($_GET['action']) && $_GET['action'] === 'get_all') {
            $sql = "SELECT id, supplier_name, supplier_company, phone_no, email, address, gst_id FROM suppliers ORDER BY supplier_name ASC";
            $result = mysqli_query($conn, $sql);
            
            if ($result) {
                $suppliers = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $suppliers[] = $row;
                }
                $response['success'] = true;
                $response['suppliers'] = $suppliers;
            } else {
                $response['message'] = 'Error fetching suppliers: ' . mysqli_error($conn);
            }
        }
        // Get supplier by ID
        elseif (isset($_GET['action']) && $_GET['action'] === 'get_by_id' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $sql = "SELECT id, supplier_name, supplier_company, phone_no, email, address, gst_id FROM suppliers WHERE id = $id";
            $result = mysqli_query($conn, $sql);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $supplier = mysqli_fetch_assoc($result);
                $response['success'] = true;
                $response['supplier'] = $supplier;
            } else {
                $response['message'] = 'Supplier not found';
            }
        }
        // Search suppliers by name, company, or phone
        elseif (isset($_GET['action']) && $_GET['action'] === 'search' && isset($_GET['term'])) {
            $term = mysqli_real_escape_string($conn, $_GET['term']);
            $sql = "SELECT id, supplier_name, supplier_company, phone_no, email, address, gst_id 
                    FROM suppliers 
                    WHERE supplier_name LIKE '%$term%' 
                       OR supplier_company LIKE '%$term%' 
                       OR phone_no LIKE '%$term%'
                    ORDER BY supplier_name ASC 
                    LIMIT 20";
            $result = mysqli_query($conn, $sql);
            
            if ($result) {
                $suppliers = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $suppliers[] = $row;
                }
                $response['success'] = true;
                $response['suppliers'] = $suppliers;
            } else {
                $response['message'] = 'Error searching suppliers: ' . mysqli_error($conn);
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
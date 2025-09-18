<?php
if (!isset($_GET['file'])) {
    die('Error: No file specified.');
}

$file = basename($_GET['file']); // Sanitize input
$filepath = __DIR__ . '/quotations/' . $file;

if (file_exists($filepath)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $file . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    exit;
} else {
    die('Error: File not found.');
}
?>

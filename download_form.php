<?php 
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Path to the converted PDF file
$file = 'form/helping_heart_organization_form.pdf'; // Make sure this is the correct path

// Absolute path banaya
// $file = $_SERVER['DOCUMENT_ROOT'] . '/eduhelp/form/helping_heart_organization_form.pdf';

if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    flush();
    readfile($file);
    exit();
} else {
    echo "Form not found.";
}
?>

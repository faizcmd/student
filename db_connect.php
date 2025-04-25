<?php
// Database credentials
$host = 'localhost';
$user = 'root';
$pass = 'root';  // Replace with the actual password if necessary
$db = 'student_donation';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    // Log the error to a file (optional)
    error_log("DB connection failed: " . $conn->connect_error, 3, 'db_errors.log');
    die("Database connection failed: " . $conn->connect_error);
} else {
    // echo "Connected successfully"; // Optional: You can remove this line for production
}
?>

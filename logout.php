<?php
session_start(); // Start the session

// Unset all of the session variables
session_unset(); 

// Destroy the session
session_destroy(); 

// Redirect to the login page
header("Location: login.php");
exit(); // Stop further script execution
?>

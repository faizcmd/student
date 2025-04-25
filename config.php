<?php
// Razorpay API Credentials
$keyId = "rzp_test_5yKTiH9si8pZlM"; // Replace with your actual Razorpay Key ID
$keySecret = "DYyNBj4vRM1Nfze7XFINLX4v"; // Replace with your actual Razorpay Secret Key

// Include Composer's autoloader (This is required for Razorpay SDK)
require_once 'vendor/autoload.php';  // Update the path if necessary

use Razorpay\Api\Api;

$api = new Api($keyId, $keySecret); // Initialize Razorpay API instance

?>

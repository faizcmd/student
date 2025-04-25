<?php
// Enable errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// RazorpayX credentials
$keyId = 'rzp_test_5yKTiH9si8pZlM';        // Replace with your RazorpayX Key ID
$keySecret = 'DYyNBj4vRM1Nfze7XFINLX4v';         // Replace with your RazorpayX Key Secret
$accountNumber = 'acc_QMWG7D2FXtih4B'; // Replace with your RazorpayX Account Number

// Payout details
$amount = 10000; // Amount in paise (e.g. ₹100.00 = 10000 paise)
$fundAccountId = 'fa_QMqarXQlXlNjJv'; // Replace with actual fund_account_id from DB
$schoolName = 'ABC High School';     // For narration

// Create the payout payload
$data = [
    "account_number" => $accountNumber,
    "fund_account_id" => $fundAccountId,
    "amount" => $amount,
    "currency" => "INR",
    "mode" => "UPI",
    "purpose" => "payout",
    "queue_if_low_balance" => true,
    "narration" => "Fee payment to $schoolName",
    "reference_id" => "payout_" . uniqid(),
    "notes" => [
        "school" => $schoolName
    ]
];

// Send the request via cURL
$ch = curl_init('https://api.razorpay.com/v1/payouts');
curl_setopt($ch, CURLOPT_USERPWD, "$keyId:$keySecret");
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($http_status == 200 || $http_status == 201) {
    echo "✅ Payout created successfully!<br><pre>" . $response . "</pre>";
} else {
    echo "❌ Failed to create payout! Status: $http_status<br><pre>" . $response . "</pre>";
}

curl_close($ch);
?>

<?php
session_start(); // 🧠 Always start session

header('Content-Type: application/json');

// ✅ DB & OTP sending utilities
include 'db_connection.php';
require 'send_email_otp.php';
require 'send_sms_otp.php';

// ✅ Check if session contains email & mobile
if (!isset($_SESSION['user_email']) || !isset($_SESSION['user_mobile'])) {
    echo json_encode(["message" => "Session expired. Please login again."]);
    exit();
}

$email  = $_SESSION['user_email'];
$mobile = $_SESSION['user_mobile'];

// ✅ Generate new OTPs
$otp_email  = rand(100000, 999999);
$otp_mobile = rand(100000, 999999);

// ✅ Update new OTPs in DB
$stmt = $conn->prepare("UPDATE users SET otp_email = ?, otp_mobile = ? WHERE email = ?");
$stmt->bind_param("iis", $otp_email, $otp_mobile, $email);

if ($stmt->execute()) {
    $emailSent = sendEmailOTP($email, $otp_email);
    $smsSent   = sendSMSOTP($mobile, $otp_mobile);

    if ($emailSent && $smsSent) {
        echo json_encode(["message" => "OTP resent to your email and mobile."]);
    } elseif ($emailSent && !$smsSent) {
        echo json_encode(["message" => "Email OTP sent. Failed to send SMS."]);
    } elseif (!$emailSent && $smsSent) {
        echo json_encode(["message" => "SMS OTP sent. Failed to send email."]);
    } else {
        echo json_encode(["message" => "Failed to send OTP. Try again."]);
    }
} else {
    echo json_encode(["message" => "Something went wrong while updating OTP."]);
}

$stmt->close();
$conn->close();

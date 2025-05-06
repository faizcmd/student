<?php
session_start();
include 'db_connect.php';
require 'send_email_otp.php';
require 'send_sms_otp.php';

if (!isset($_SESSION['email']) || !isset($_SESSION['mobile'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['email'];
$mobile = $_SESSION['mobile'];

$new_otp_email = rand(100000, 999999);
$new_otp_mobile = rand(100000, 999999);

// Update in DB
$update = $conn->prepare("UPDATE students SET otp_email = ?, otp_mobile = ? WHERE email = ? AND mobile = ?");
$update->bind_param("iiss", $new_otp_email, $new_otp_mobile, $email, $mobile);
$update->execute();

// Resend OTPs
$emailSent = sendEmailOTP($email, $new_otp_email);
$smsSent = sendSMSOTP($mobile, $new_otp_mobile);

if ($emailSent && $smsSent) {
    echo "<script>alert('OTPs resent successfully.'); window.location.href='otp_verification.php';</script>";
} else {
    echo "<script>alert('Failed to resend OTPs.'); window.location.href='otp_verification.php';</script>";
}
?>

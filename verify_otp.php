<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['email']) || !isset($_SESSION['mobile'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['email'];
$mobile = $_SESSION['mobile'];
$otp_email_input = $_POST['otp_email'];
$otp_mobile_input = $_POST['otp_mobile'];

$stmt = $conn->prepare("SELECT id, otp_email, otp_mobile FROM students WHERE email = ? AND mobile = ?");
$stmt->bind_param("ss", $email, $mobile);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if ($row['otp_email'] == $otp_email_input && $row['otp_mobile'] == $otp_mobile_input) {
        // Mark student as verified
        $update = $conn->prepare("UPDATE students SET is_verified = 1 WHERE id = ?");
        $update->bind_param("i", $row['id']);
        $update->execute();

        unset($_SESSION['email'], $_SESSION['mobile']); // clear session

        echo "<script>alert('OTP verified successfully.'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Invalid OTPs. Please try again.'); window.location.href='otp_verification.php';</script>";
    }
} else {
    echo "<script>alert('Student record not found.'); window.location.href='register.php';</script>";
}
?>

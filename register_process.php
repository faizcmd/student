<?php 
session_start();
include 'db_connect.php';
require 'send_email_otp.php'; 
require 'send_sms_otp.php';   

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name           = $_POST['name'];
    $email          = $_POST['email'];
    $mobile         = $_POST['mobile'];
    $password       = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $aadhaar_number = $_POST['aadhaar_number'];
    $gender         = $_POST['gender'];
    $zakat          = $_POST['zakat'] ?? 'No';
    $fee            = $_POST['fee'];
    $school_id      = $_POST['school_id'];

    $otp_email  = rand(100000, 999999);
    $otp_mobile = rand(100000, 999999);
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Check duplicates individually
    $errors = [];

    $check_email = $conn->prepare("SELECT id FROM students WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();
    if ($check_email->num_rows > 0) $errors[] = "Email";

    $check_mobile = $conn->prepare("SELECT id FROM students WHERE mobile = ?");
    $check_mobile->bind_param("s", $mobile);
    $check_mobile->execute();
    $check_mobile->store_result();
    if ($check_mobile->num_rows > 0) $errors[] = "Mobile";

    $check_aadhaar = $conn->prepare("SELECT id FROM students WHERE aadhaar_number = ?");
    $check_aadhaar->bind_param("s", $aadhaar_number);
    $check_aadhaar->execute();
    $check_aadhaar->store_result();
    if ($check_aadhaar->num_rows > 0) $errors[] = "Aadhaar";

    

    if (!empty($errors)) {
        $duplicateFields = implode(", ", $errors);
        echo "<script>alert('Already registered: $duplicateFields'); window.history.back();</script>";
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO students 
        (name, email, mobile, password, aadhaar_number, gender, zakat, fee_amount, school_id, otp_email, otp_mobile, registered_ip) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssisis",
        $name, $email, $mobile, $password, $aadhaar_number, $gender, $zakat, $fee, $school_id, $otp_email, $otp_mobile, $ip_address);

    if ($stmt->execute()) {
        $_SESSION['email']  = $email;
        $_SESSION['mobile'] = $mobile;

        $emailSent = sendEmailOTP($email, $otp_email);
        $smsSent   = sendSMSOTP($mobile, $otp_mobile);

        if ($emailSent && $smsSent) {
            header("Location: login.php");
            exit();
        } else {
            echo "<script>alert('Failed to send OTPs.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Something went wrong. Please try again.'); window.history.back();</script>";
    }
}
?>


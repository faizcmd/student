<?php 
session_start();  // Start a session

include 'db_connect.php'; // Make sure this sets $conn

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp_email = $_POST['otp_email'];
    $otp_mobile = $_POST['otp_mobile'];

    // Check OTPs
    $stmt = $conn->prepare("SELECT * FROM students WHERE otp_email = ? AND otp_mobile = ?");
    if ($stmt === false) {
        die("Prepare failed (SELECT): " . $conn->error);
    }

    $stmt->bind_param("ss", $otp_email, $otp_mobile);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['is_verified'] = 1;

        // Mark as verified
        $stmt = $conn->prepare("UPDATE students SET is_verified = 1 WHERE id = ?");
        if ($stmt === false) {
            die("Prepare failed (UPDATE): " . $conn->error);
        }

        $stmt->bind_param("i", $user['id']);
        $stmt->execute();

        echo "<script>
            alert('OTP verified successfully! You can now log in.');
            window.location.href = 'login.php';
        </script>";
        exit();
    } else {
        echo "<script>
            alert('Invalid OTPs. Please try again.');
            window.history.back();
        </script>";
        exit();
    }
}
?>

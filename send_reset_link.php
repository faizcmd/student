<?php 
// Database connection
include 'db_connect.php'; // Make sure this file connects $conn

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader (Make sure you've run `composer require phpmailer/phpmailer`)
require 'vendor/autoload.php';

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$alertClass = '';

// Only process form if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Set timezone to match your DB/server
    date_default_timezone_set('Asia/Kolkata');

    // Generate token and expiry
    $token = bin2hex(random_bytes(32));
    $expiry = date("Y-m-d H:i:s", strtotime("+30 minutes"));

    // Update reset_token and token_expiry
    $stmt = $conn->prepare("UPDATE students SET reset_token = ?, token_expiry = ? WHERE email = ?");
    $stmt->bind_param("sss", $token, $expiry, $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $reset_link = "http://localhost/student/reset_password.php?token=$token";

        // Email setup
        $mail = new PHPMailer(true);
        try {
            // Mailtrap SMTP settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.mailtrap.io'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'ab87fc12becfe1'; // <-- replace this
            $mail->Password   = '8906720d6c5151'; // <-- replace this
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('no-reply@eduhelp.com', 'EduHelp Support');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password - EduHelp';
            $mail->Body    = "Hi,<br><br>Click the link below to reset your password:<br><br>
                             <a href='$reset_link'>$reset_link</a><br><br>
                             <small>This link expires at: $expiry</small>";

            $mail->send();
            $message = "A password reset link has been sent to your email address.";
            $alertClass = "alert-success";

        } catch (Exception $e) {
            $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $alertClass = "alert-danger";
        }
    } else {
        $message = "No user found with this email address.";
        $alertClass = "alert-danger";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<style>
    .top-nav {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background-color: #ffffff;
    border-bottom: 1px solid #dee2e6;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 1000;
    
}

.top-nav .title {
    font-weight: bold;
    font-size: 1.2rem;
}

.top-nav .nav-links a {
    margin-left: 15px;
    font-size: 1rem;
    text-decoration: none;
    color: #006400;
}

.top-nav .nav-links a:hover {
    color: #006400;
}

    </style>
</head>
<body class="bg-light">
<div class="top-nav" >
    <div class="title">🎓 EduHelp</div>
    <div class="nav-links">
       
        <!-- <a href="donor_view.php">🙏 Donor View</a> -->
       <a href="Login.php">🔐 Login</a>
        </div>
</div>

    <!-- <div class="container mt-5" style="max-width: 500px;margin-top: 80px;"> -->
    <div class="container" style="max-width: 500px; margin-top: 80px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Forgot Password</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($message)): ?>
                    <div class="alert <?= $alertClass ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>
                <?php if ($message !== "A password reset link has been sent to your email address."): ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="email">Enter your email address</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">Send Reset Link</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

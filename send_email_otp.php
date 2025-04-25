<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // PHPMailer autoload

function sendEmailOTP($toEmail, $otp) {
    $mail = new PHPMailer(true);
    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'faizan.ppc@gmail.com';        // ✅ your Gmail
        $mail->Password   = 'vcdv tvmi kkop akgo';           // ✅ your App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Sender and receiver
        $mail->setFrom('faizan.ppc@gmail.com', 'EduHelp');
        $mail->addAddress($toEmail);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Your Email OTP';
        $mail->Body    = "Your OTP for email verification is <b>$otp</b>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // 🔍 Show detailed error
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

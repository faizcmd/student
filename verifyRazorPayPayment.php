<?php 
require_once 'config.php'; // Razorpay keys
require_once 'db_connect.php'; // Database connection
require_once 'vendor/autoload.php'; // Composer autoloader (includes PHPMailer)

use Razorpay\Api\Api;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check for required parameters
if (isset($_GET['payment_id']) && isset($_GET['order_id']) && isset($_GET['student_id']) && isset($_GET['fee_amount'])) {
    $payment_id = $_GET['payment_id'];
    $order_id = $_GET['order_id'];
    $student_id = $_GET['student_id'];
    $fee_amount = $_GET['fee_amount'];

    // Verify the payment with Razorpay API
    $api = new Api($keyId, $keySecret);
    $payment = $api->payment->fetch($payment_id);

    if ($payment->status == 'captured') {
        // Payment successfully captured, update the database
        $sql = "UPDATE students SET is_helped = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();

        // Insert payment data into the payments table
        $stmt = $conn->prepare("INSERT INTO payments (student_id, razorpay_payment_id, razorpay_order_id, fee_amount, payment_time) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("issd", $student_id, $payment_id, $order_id, $fee_amount);
        $stmt->execute();
        $payment_id = $conn->insert_id; // Get the inserted payment ID

        // 🔧 Simulate successful payout response (for testing flow without RazorpayX)
        $payout_id = 'mock_payout_' . time();
        $payout_status = 'processed';

        // Update payments table with mock data
        $stmt = $conn->prepare("UPDATE payments SET razorpay_payout_id = ?, payout_status = ? WHERE id = ?");
        $stmt->bind_param("ssi", $payout_id, $payout_status, $payment_id);
        $stmt->execute();

        /*
        // 🔐 Uncomment below for actual RazorpayX payout once account is KYC verified
        $payout_data = [
            'account_number' => '029082479979', // Example account number of college
            'ifsc' => 'ESFB0009014', // Example IFSC code of college bank
            'amount' => $fee_amount * 100, // Convert to paise
            'currency' => 'INR',
            'purpose' => 'Fee Payment',
            'fund_account' => 'fa_QMrzBzsyrMOTW5', // Razorpay Fund Account ID (Use actual fund account ID)
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/payouts');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payout_data));
        $response = curl_exec($ch);

        if(curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }

        curl_close($ch);
        $payout_response = json_decode($response, true);

        if (isset($payout_response['id'])) {
            $payout_id = $payout_response['id'];
            $payout_status = $payout_response['status'];

            $stmt = $conn->prepare("UPDATE payments SET razorpay_payout_id = ?, payout_status = ? WHERE id = ?");
            $stmt->bind_param("ssi", $payout_id, $payout_status, $payment_id);
            $stmt->execute();
        } else {
            echo "Error processing payout: " . $payout_response['error']['description'];
        }
        */

        // Get student's email from the database
        $stmt = $conn->prepare("SELECT email, name, fee_amount, school_id FROM students WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $student_email = $student['email'];
        $student_name = $student['name'];
        $fee_amount = $student['fee_amount'];

        // Get school details
        $stmt = $conn->prepare("SELECT school_name FROM schools WHERE id = ?");
        $stmt->bind_param("i", $student['school_id']);
        $stmt->execute();
        $school_result = $stmt->get_result();
        $school = $school_result->fetch_assoc();
        $school_name = $school['school_name'];

        // Send email notification using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Replace with your SMTP host
            $mail->SMTPAuth = true;
            $mail->Username = 'faizan.ppc@gmail.com'; // Replace with your email
            $mail->Password = 'vcdv tvmi kkop akgo'; // Replace with your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('faizan.ppc@gmail.com', 'EduHelp');
            $mail->addAddress($student_email, $student_name);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "Fee Payment Confirmation - EduHelp";
            
            $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #006400; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Fee Payment Confirmation</h2>
                    </div>
                    <div class='content'>
                        <p>Dear {$student_name},</p>
                        <p>We are pleased to inform you that your fee payment has been successfully processed.</p>
                        <p><strong>Payment Details:</strong></p>
                        <ul>
                            <li>Amount Paid: ₹{$fee_amount}</li>
                            <li>School/College: {$school_name}</li>
                            <li>Transaction ID: {$payment_id}</li>
                            <li>Date: " . date('d-m-Y H:i:s') . "</li>
                        </ul>
                        <p>Please confirm with your institution that they have received the payment.</p>
                        <p>If you have any questions, please don't hesitate to contact us.</p>
                        <p>Best regards,<br>EduHelp Team</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated message, please do not reply to this email.</p>
                    </div>
                </div>
            </body>
            </html>";

            $mail->send();
            
            // Update payment status to include email sent
            $stmt = $conn->prepare("UPDATE payments SET email_sent = 1 WHERE id = ?");
            $stmt->bind_param("i", $payment_id);
            $stmt->execute();
            
            // Log successful email
            error_log("Payment confirmation email sent successfully to: " . $student_email);
            
        } catch (Exception $e) {
            // Log the error
            error_log("Failed to send payment confirmation email to: " . $student_email . ". Error: " . $mail->ErrorInfo);
            
            // Update payment status to indicate email failure
            $stmt = $conn->prepare("UPDATE payments SET email_sent = 0, email_error = ? WHERE id = ?");
            $error_message = $mail->ErrorInfo;
            $stmt->bind_param("si", $error_message, $payment_id);
            $stmt->execute();
        }

        // Redirect to success page with student_id in URL
        header("Location: payment_success.php?payment_id=$payment_id&order_id=$order_id&fee_amount=$fee_amount&student_id=$student_id");
        exit;

    } else {
        echo "Payment verification failed.";
    }
} else {
    echo "Invalid parameters.";
}
?>

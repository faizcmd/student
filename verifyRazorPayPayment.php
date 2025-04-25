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
        $stmt = $conn->prepare("SELECT email FROM students WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $student_email = $student['email'];

        // Send email to the student using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->setFrom('faizan.ppc@gmail.com', 'EduHelp');
            $mail->addAddress($student_email, 'Student Name'); // Use student's email
            $mail->Subject = 'Donation Received';
            $mail->Body    = 'Dear Student, your donation has been successfully received. Your payment is being processed and will be transferred to the college account. Thank you for your support!';
            $mail->send();
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
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

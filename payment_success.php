<?php
require_once 'config.php';
require_once 'db_connect.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$student_id = $_GET['student_id'] ?? '';
$payment_id = $_GET['payment_id'] ?? '';
$order_id = $_GET['order_id'] ?? '';
$fee_amount = $_GET['fee_amount'] ?? '';

// Fetch student name
$student_name = "Student";
if ($student_id) {
    $stmt = $conn->prepare("SELECT name FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $student_name = htmlspecialchars($row['name']);
    }
}

// Get payment details
if (isset($_GET['payment_id'])) {
    $payment_id = $_GET['payment_id'];
    
    // Fetch payment details with student and school information
    $stmt = $conn->prepare("
        SELECT p.*, s.name as student_name, s.email as student_email, 
               sch.school_name, sch.account_name, sch.account_number, 
               sch.ifsc_code, sch.bank_name
        FROM payments p
        JOIN students s ON p.student_id = s.id
        JOIN schools sch ON s.school_id = sch.id
        WHERE p.id = ?
    ");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();

    // Generate PDF receipt
    if (isset($_GET['download']) && $_GET['download'] == 'receipt') {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);
        
        $html = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .header { text-align: center; margin-bottom: 30px; }
                .logo { font-size: 24px; color: #006400; font-weight: bold; }
                .title { font-size: 20px; margin: 20px 0; }
                .details { margin: 20px 0; }
                .details p { margin: 5px 0; }
                .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
                .amount { font-size: 18px; font-weight: bold; color: #006400; }
                .thank-you { margin-top: 30px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='header'>
                <div class='logo'>EduHelp</div>
                <div class='title'>Payment Receipt</div>
            </div>
            
            <div class='details'>
                <p><strong>Receipt No:</strong> {$payment['id']}</p>
                <p><strong>Date:</strong> " . date('d-m-Y H:i:s', strtotime($payment['payment_time'])) . "</p>
                <p><strong>Transaction ID:</strong> {$payment['razorpay_payment_id']}</p>
                <p><strong>Student Name:</strong> {$payment['student_name']}</p>
                <p><strong>Student Email:</strong> {$payment['student_email']}</p>
                <p><strong>School/College:</strong> {$payment['school_name']}</p>
                <p class='amount'>Amount Paid: &#8377;{$payment['fee_amount']}</p>
            </div>

            <div class='thank-you'>
                <p>Thank you for your generous contribution!</p>
                <p>This receipt serves as proof of your donation.</p>
            </div>

            <div class='footer'>
                <p>This is a computer-generated receipt and does not require a signature.</p>
                <p>For any queries, please contact us at support@eduhelp.com</p>
            </div>
        </body>
        </html>";

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Generate filename
        $filename = "receipt_" . $payment['id'] . "_" . date('Ymd') . ".pdf";
        
        // Output PDF
        $dompdf->stream($filename, array("Attachment" => true));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - EduHelp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .success-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .success-header {
            text-align: center;
            color: #006400;
            margin-bottom: 30px;
        }
        .success-icon {
            font-size: 48px;
            color: #006400;
            margin-bottom: 20px;
        }
        .details-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .download-btn {
            background-color: #006400;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .download-btn:hover {
            background-color: #004d00;
            color: white;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="success-container">
            <div class="success-header">
                <div class="success-icon">✓</div>
                <h1>Payment Successful!</h1>
                <p class="lead">Thank you for your generous contribution.</p>
            </div>

            <?php if (isset($payment)): ?>
            <div class="details-box">
                <h3>Payment Details</h3>
                <p><strong>Receipt No:</strong> <?php echo $payment['id']; ?></p>
                <p><strong>Date:</strong> <?php echo date('d-m-Y H:i:s', strtotime($payment['payment_time'])); ?></p>
                <p><strong>Transaction ID:</strong> <?php echo $payment['razorpay_payment_id']; ?></p>
                <p><strong>Student Name:</strong> <?php echo $payment['student_name']; ?></p>
                <p><strong>School/College:</strong> <?php echo $payment['school_name']; ?></p>
                <p><strong>Amount Paid:</strong> &#8377;<?php echo $payment['fee_amount']; ?></p>
            </div>

            <div class="text-center">
                <a href="?payment_id=<?php echo $payment_id; ?>&download=receipt" class="download-btn">
                    <i class="bi bi-download"></i> Download Receipt
                </a>
            </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="donor_view.php" class="btn btn-outline-success">Back to Donor View</a>
            </div>
        </div>
    </div>
</body>
</html>

<?php
require('config.php'); // Razorpay API credentials
require('vendor/autoload.php'); // Include Composer's autoloader

use Razorpay\Api\Api;

// Ensure Razorpay credentials are initialized in config.php
$api = new Api($keyId, $keySecret);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $student_name = $_POST['student_name'];
    $student_email = $_POST['student_email'];
    $fee_amount = $_POST['fee_amount'];

    // Razorpay amount is in paise
    $amount_paise = $fee_amount * 100;

    // Create order using Razorpay API
    $order = $api->order->create([
        'receipt'         => 'order_rcpt_' . $student_id,
        'amount'          => $amount_paise,
        'currency'        => 'INR',
        'payment_capture' => 1
    ]);

    $order_id = $order['id'];
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Redirecting to Razorpay...</title>
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
<script>
var options = {
    "key": "<?= $keyId ?>", // Razorpay Key ID
    "amount": "<?= $amount_paise ?>",
    "currency": "INR",
    "name": "EduHelp Donation",
    "description": "Fee Payment for <?= $student_name ?>",
    "image": "https://yourdomain.com/logo.png",
    "order_id": "<?= $order_id ?>",
    "handler": function (response){
        window.location.href = "verifyRazorPayPayment.php?payment_id=" + response.razorpay_payment_id + 
                       "&order_id=" + response.razorpay_order_id + 
                       "&student_id=<?= $student_id ?>" +
                       "&fee_amount=<?= $fee_amount ?>";

    },
    "prefill": {
        "name": "<?= $student_name ?>",
        "email": "<?= $student_email ?>"
    },
    "theme": {
        "color": "#3399cc"
    }
};
var rzp1 = new Razorpay(options);
rzp1.open();
</script>
</body>
</html>

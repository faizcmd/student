<?php
include 'db_connect.php';
include 'razorpay_config.php'; // This is where your Razorpay credentials go

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $fee_amount = $_POST['fee_amount'];

    // Fetch student details to get the email for sending confirmation email
    $query = "SELECT * FROM students WHERE id = '$student_id'";
    $result = $conn->query($query);
    $student = $result->fetch_assoc();

    // Set Razorpay order details
    $order_data = [
        'receipt'         => rand(1000, 9999),
        'amount'          => $fee_amount * 100, // Amount in paise
        'currency'        => 'INR',
        'payment_capture' => 1
    ];

    $razorpayOrder = $api->order->create($order_data); // Create an order in Razorpay
    $razorpay_order_id = $razorpayOrder['id'];
    $razorpay_signature = $razorpayOrder['signature'];

    // Redirect to Razorpay payment page
    echo "<script>
        var options = {
            key: '$keyId', // Your Razorpay key
            amount: '$fee_amount' * 100, // Amount in paise
            currency: 'INR',
            name: 'Student Donation',
            description: 'Donate to student',
            order_id: '$razorpay_order_id',
            handler: function (response) {
                alert('Payment Success');
                window.location.href = 'payment_success.php?id=$student_id'; // Redirect to success page
            },
            prefill: {
                name: '$student[name]',
                email: '$student[email]',
                contact: '$student[mobile]',
            },
            theme: {
                color: '#F37254'
            }
        };
        var rzp1 = new Razorpay(options);
        rzp1.open();
    </script>";
}
?>

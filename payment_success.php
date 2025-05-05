<?php
require_once 'db_connect.php';

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
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Successful</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: #f8f9fa;
        }
        .success-box {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .emoji {
            font-size: 3rem;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 success-box text-center">
                <div class="emoji text-success">🎉</div>
                <h1 class="text-success mt-3">Thank You, For supporting <?= $student_name ?>!</h1>
                <br>
                <p>Your payment was successful.</p>
                

                <!-- Displaying Transaction Details -->
                <h5>Transaction Details:</h5>
                <p><strong>Payment ID:</strong> <?= htmlspecialchars($payment_id) ?></p>
                <p><strong>Order ID:</strong> <?= htmlspecialchars($order_id) ?></p>
                <p><strong>Fee Amount:</strong> ₹<?= htmlspecialchars($fee_amount) ?></p>
                <p>Dear Angel May God Bless You 🙏</p>
                <a href="donor_view.php" class="btn btn-primary mt-3">⬅ Back to Donation Page</a>
            </div>
        </div>
    </div>
</body>
</html>

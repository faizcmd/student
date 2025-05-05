<?php
session_start();
// Optional: Check if admin is logged in
include 'db_connect.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add School Bank Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container my-5">
    <h2>Add School and Bank Details</h2>
    <form action="save_school.php" method="POST">
        <div class="mb-3">
            <label>School Name</label>
            <input type="text" name="school_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Account Holder Name</label>
            <input type="text" name="account_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Account Number</label>
            <input type="text" name="account_number" class="form-control">
        </div>
        <div class="mb-3">
            <label>IFSC Code</label>
            <input type="text" name="ifsc_code" class="form-control">
        </div>
        <div class="mb-3">
            <label>Bank Name</label>
            <input type="text" name="bank_name" class="form-control">
        </div>
        <div class="mb-3">
            <label>UPI Name</label>
            <input type="text" name="upi_name" class="form-control">
        </div>
        <div class="mb-3">
            <label>UPI ID (Required for Razorpay)</label>
            <input type="text" name="upi_id" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Add School</button>
    </form>
</body>
</html>

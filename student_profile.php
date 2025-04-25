<?php
include 'db_connect.php';

if (!isset($_GET['id'])) {
    die("Student ID not provided.");
}

$student_id = $_GET['id'];

$sql = "SELECT students.*, 
               schools.school_name, 
               schools.account_name, 
               schools.account_number, 
               schools.ifsc_code, 
               schools.bank_name, 
               schools.upi_name, 
               schools.upi_id 
        FROM students 
        JOIN schools ON students.school_id = schools.id 
        WHERE students.id = $student_id";

$result = $conn->query($sql);
if ($result->num_rows === 0) {
    die("Student not found.");
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Profile</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-5">
  <h2 class="text-center mb-4">Student Profile</h2>

  <!-- Student Basic Info -->
  <div class="card shadow mb-4">
    <div class="card-header bg-primary text-white">
      Basic Details
    </div>
    <div class="card-body">
      <p><strong>Name:</strong> <?= htmlspecialchars($row['name']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
      <p><strong>Mobile:</strong> <?= htmlspecialchars($row['mobile']) ?></p>
      <p><strong>Zakat Eligible:</strong> <?= htmlspecialchars($row['zakat']) ?></p>
      <p><strong>Fee Amount:</strong> ₹<?= htmlspecialchars($row['fee_amount']) ?></p>
      <p><strong>School/College:</strong> <?= htmlspecialchars($row['school_name']) ?></p>
    </div>
  </div>

  <!-- School Bank Info -->
  <div class="card shadow">
    <div class="card-header bg-success text-white">
      School Bank Details
    </div>
    <div class="card-body">
      <p><strong>Account Name:</strong> <?= htmlspecialchars($row['account_name']) ?></p>
      <p><strong>Account Number:</strong> <?= htmlspecialchars($row['account_number']) ?></p>
      <p><strong>IFSC Code:</strong> <?= htmlspecialchars($row['ifsc_code']) ?></p>
      <p><strong>Bank Name:</strong> <?= htmlspecialchars($row['bank_name']) ?></p>
      <p><strong>UPI Name:</strong> <?= htmlspecialchars($row['upi_name']) ?></p>
      <p><strong>UPI ID:</strong> <?= htmlspecialchars($row['upi_id']) ?></p>
    </div>
  </div>
</div>

</body>
</html>

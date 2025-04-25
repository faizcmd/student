<?php
include 'db_connect.php';

if (isset($_GET['school_id'])) {
    $school_id = $_GET['school_id'];
    $result = $conn->query("SELECT * FROM schools WHERE id = $school_id");

    if ($result && $row = $result->fetch_assoc()) {
        echo "<p><strong>Account Name:</strong> {$row['account_name']}</p>";
        echo "<p><strong>Account No:</strong> {$row['account_number']}</p>";
        echo "<p><strong>IFSC:</strong> {$row['ifsc_code']}</p>";
        echo "<p><strong>Bank Name:</strong> {$row['bank_name']}</p>";
        echo "<p><strong>UPI Name:</strong> {$row['upi_name']}</p>";
        echo "<p><strong>UPI ID:</strong> {$row['upi_id']}</p>";
    } else {
        echo "<p class='text-danger'>No bank details found.</p>";
    }
}
?>




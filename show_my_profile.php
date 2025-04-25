<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h2 class="text-center mb-4">Welcome, <?= htmlspecialchars($user['name']) ?>!</h2>

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                My Profile
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Mobile:</strong> <?= htmlspecialchars($user['mobile']) ?></p>
                <p><strong>School:</strong> <?= htmlspecialchars($user['school_name']) ?></p>
                <p><strong>Zakat Eligible:</strong> <?= htmlspecialchars($user['zakat']) ?></p>
                <p><strong>Fee Amunt:</strong> <?= htmlspecialchars($user['fee_amount']) ?></p>
                <p><strong>Aadhaar_number:</strong> <?= htmlspecialchars($user['aadhaar_number']) ?></p>
            </div>
        </div>

       
    <div class="button-group">
            <a href="donor_view.php" class="btn btn-secondary" style="margin-left: 10px;">Go to Donor View</a>
            <a href="scholarShipForm.php" class="btn btn-primary" style="margin-left: 10px;">Dashboard</a>
            <form action="logout.php" method="POST" style="display:inline-block;">
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
            
        </div>
</body>
</html>

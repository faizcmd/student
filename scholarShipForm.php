<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f2f4f6;
        }
        .dashboard-card {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }
        .welcome-message {
            font-size: 1.8rem;
            font-weight: bold;
            color: #343a40;
        }
        .btn-custom {
            margin: 12px 0;
            padding: 12px;
            font-size: 1rem;
        }
        .info-box {
            background-color: #e9f7ef;
            border-left: 5px solid #28a745;
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 0.95rem;
            margin-bottom: 20px;
            color: #155724;
        }
        .icon {
            font-size: 18px;
            margin-right: 6px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 dashboard-card">
            <p class="welcome-message">👋 Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>

            <!-- Download Button -->
            <a href="download_form.php" class="btn btn-primary btn-block btn-custom">
                📥 Download Scholarship Form
            </a>
            <div class="info-box">
                <span class="icon">ℹ️</span>
                To apply for a scholarship, please download the form and get it filled and stamped by your school or college.
            </div>

            <!-- Upload Button -->
            <a href="upload_form.php" class="btn btn-success btn-block btn-custom">
                📤 Upload Filled Form
            </a>
            <div class="info-box">
                <span class="icon">✅</span>
                Upload your filled and stamped scholarship form here to complete your application and appear to donors.
            </div>

            <!-- Profile Button -->
            <a href="show_my_profile.php" class="btn btn-info btn-block btn-custom">
                🧾 Show My Profile
            </a>
            <div class="info-box">
    <span class="icon">📌</span>
    To check your profile click on above show my profile button.
</div>

            <!-- Logout Button -->
            <a href="logout.php" class="btn btn-danger btn-block btn-custom">
                🔒 Logout
            </a>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

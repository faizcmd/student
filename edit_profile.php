<?php
session_start();
include 'db_connect.php';

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch current user data
$sql = "SELECT name, email, mobile, aadhaar_number FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $aadhaar = trim($_POST['aadhaar_number']);

    if (empty($name) || empty($email) || empty($mobile) || empty($aadhaar)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } 
    $mobile = trim($_POST['mobile']);
if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
    $error = "Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.";
}

    
    elseif (!preg_match('/^\d{12}$/', $aadhaar)) {
        $error = "Aadhaar number must be 12 digits.";
    } else {
        // Update user data
        $update_sql = "UPDATE students SET name = ?, email = ?, mobile = ?, aadhaar_number = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssi", $name, $email, $mobile, $aadhaar, $user_id);
        if ($update_stmt->execute()) {
            $success = "Profile updated successfully.";
            // Refresh user data
            $user['name'] = $name;
            $user['email'] = $email;
            $user['mobile'] = $mobile;
            $user['aadhaar_number'] = $aadhaar;
        } else {
            $error = "Error updating profile. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f2f4f6;
            padding-top: 70px; /* Space for fixed navbar */
        }
        .top-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background-color: #ffffff;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
        }
        .top-nav .title {
            font-weight: bold;
        }
        .top-nav .nav-links a {
            margin-left: 15px;
            font-size: 1rem;
            text-decoration: none;
            color: #007bff;
        }
        .top-nav .nav-links a:hover {
            color: #0056b3;
        }
        .form-container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<!-- Top Navigation Bar -->
<div class="top-nav">
    <div class="title">🎓 EduHelp Dashboard</div>
    <div class="nav-links">
        <a href="scholarShipForm.php">🏠 Dashboard</a>
        <a href="donor_view.php">🙏 Donor View</a>
        <a href="login.php">🔑 Login</a>
        <a href="logout.php" style="color: #dc3545;">🔓 Logout</a>
    </div>
</div>

<div class="container mt-5">
    <div class="form-container">
        <h2 class="mb-4">Edit Profile</h2>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="edit_profile.php">
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="mobile" class="form-label">Mobile Number</label>
                <input type="text" class="form-control" id="mobile" name="mobile" value="<?= htmlspecialchars($user['mobile']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="aadhaar_number" class="form-label">Aadhaar Number</label>
                <input type="text" class="form-control" id="aadhaar_number" name="aadhaar_number" value="<?= htmlspecialchars($user['aadhaar_number']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">💾 Save Changes</button>
            <a href="show_my_profile.php" class="btn btn-secondary ms-2">🔙 Back to Profile</a>
        </form>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

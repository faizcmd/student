<?php
include 'db_connect.php';

$message = '';
$alertClass = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if token exists and is not expired
    $stmt = $conn->prepare("SELECT email FROM students WHERE reset_token = ? AND token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $email = $result->fetch_assoc()['email'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Update password and clear token
            $updateStmt = $conn->prepare("UPDATE students SET password = ?, reset_token = NULL, token_expiry = NULL WHERE email = ?");
            $updateStmt->bind_param("ss", $newPassword, $email);
            $updateStmt->execute();

            $message = "Password has been reset successfully.";
            // Redirect to login page after 3 seconds
header("refresh:3;url=login.php");
            $alertClass = "alert-success";
        }
    } else {
        $message = "Invalid or expired token.";
        $alertClass = "alert-danger";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<style>

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
    font-size: 1.2rem;
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
    
</style>
</head>
<body class="bg-light">
 <!-- Top Navigation Bar -->
 <div class="top-nav" >
    <div class="title">🎓 EduHelp</div>
    <div class="nav-links">
       
        <!-- <a href="donor_view.php">🙏 Donor View</a> -->
       <a href="Login.php">🔐 Login</a>
        </div>
</div>

<div class="container " style="max-width: 500px; margin-top: 80px;">
    <div class="card shadow">
        <div class="card-header bg-warning">
            <h4 class="mb-0">Reset Your Password</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($message)): ?>
                <div class="alert <?= $alertClass ?>"><?= $message ?></div>
            <?php endif; ?>

            <?php if (isset($email)): ?>
                <form method="POST">
                <div class="form-group">
    <label>New Password</label>
    <div class="input-group">
        <input type="password" name="password" id="password" class="form-control" required>
        <div class="input-group-append">
            <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                👁️
            </span>
        </div>
    </div>
</div>

                    <button type="submit" class="btn btn-success btn-block">Reset Password</button>
                </form>
            <?php else: ?>
                <a href="send_reset_link.php" class="btn btn-primary btn-block">Try Again</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordField = document.getElementById("password");
    const type = passwordField.type === "password" ? "text" : "password";
    passwordField.type = type;
}
</script>

</body>
</html>




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
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 500px;">
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
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">Reset Password</button>
                </form>
            <?php else: ?>
                <a href="send_reset_link.php" class="btn btn-primary btn-block">Try Again</a>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>




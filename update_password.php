<?php
$conn = new mysqli("localhost", "root", "root", "students");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    if (!empty($token) && !empty($new_password)) {
        // Verify token still valid
        date_default_timezone_set('Asia/Kolkata');
        $current_time = date("Y-m-d H:i:s");

        $stmt = $conn->prepare("SELECT * FROM students WHERE reset_token = ? AND token_expiry > ?");
        $stmt->bind_param("ss", $token, $current_time);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password, clear token
            $stmt = $conn->prepare("UPDATE students SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
            $stmt->bind_param("ss", $hashed_password, $token);
            $stmt->execute();

            $success = true;
        } else {
            $error = "Invalid or expired token.";
        }
    } else {
        $error = "Missing token or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset Result</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 500px;">
    <div class="card shadow">
        <div class="card-body text-center">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    ✅ Your password has been successfully updated. You can now <a href="login.php">login</a>.
                </div>
            <?php elseif (!empty($error)): ?>
                <div class="alert alert-danger">
                    ❌ <?= $error ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>

<?php 
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "root", "student_donation");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$justRegistered = isset($_GET['registered']) && $_GET['registered'] == 1;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query for user
    // $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND is_verified = 1");
    $stmt = $conn->prepare("SELECT * FROM students WHERE email = ? And is_verified=1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: scholarShipForm.php");
        exit();
    } else {
        $errorMsg = "Invalid email or password, or account not verified.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-form {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }
        .form-header {
            background-color: lightgreen;
            color: white;
            padding: 10px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .form-footer {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <form action="login.php" method="POST" class="login-form">
                <div class="form-header">
                    <h2>Login</h2>
                </div>

                <!-- Success message after registration -->
                <?php if ($justRegistered): ?>
                    <div class="alert alert-success mt-3">Registration successful. Please login.</div>
                <?php endif; ?>

                <!-- Error message -->
                <?php if (isset($errorMsg)): ?>
                    <div class="alert alert-danger mt-3"><?= $errorMsg ?></div>
                <?php endif; ?>

                <!-- Email -->
                <div class="form-group mt-3">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn btn-success btn-block">Login</button>

                <!-- Links -->
                <div class="form-footer">
                    <p>Don't have an account? <a href="register.php">Sign Up here</a></p>
                    <p>Or <a href="donor_view.php">Visit Donor View</a></p>
                    <p><a href="forgot_password.php">Forgot Password?</a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

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
    // $stmt = $conn->prepare("SELECT * FROM students WHERE email = ? AND is_verified = 1");
    $stmt = $conn->prepare("SELECT * FROM students WHERE email = ? ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
$_SESSION['mobile'] = $user['mobile'];
    
        if ($user['is_verified'] == 1) {
            header("Location: scholarShipForm.php");
        } else {
            header("Location: otp_verification.php?verify=0");
        }
        exit();
    } else {
        $errorMsg = "Invalid email or password.";
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
    <!-- FontAwesome for eye icon -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
         body { background-color: #f8f9fa; }

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

.container {
    margin-top: 50px;
    max-width: 700px;
}

.info-card {
    border: 1px solid #dee2e6;
    background: #fff;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
}

.info-card p {
    margin-bottom: 5px;
}
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
            background-color: #28a745;
            color: white;
            padding: 10px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .form-footer {
            margin-top: 20px;
            text-align: center;
        }
        .btn-login {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }
        .btn-login:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .input-group-text {
            background-color: #fff;
            border-left: none;
        }
        .form-control {
            border-right: none;
        }
    </style>
</head>
<body>

<!-- Top Navigation Bar -->
<div class="top-nav">
    <div class="title">🎓 EduHelp</div>
    <div class="nav-links">
    
        <a href="scholarShipForm.php">🏠 Dashboard</a>
        <a href="donor_view.php">🙏 Donor View</a>
       <a href="register.php">🔐 Sign Up</a>
        </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <form action="login.php" method="POST" class="login-form">
               
<!-- Success message after registration -->
                <?php if (isset($justRegistered) && $justRegistered): ?>
                    <div class="alert alert-success mt-3">Registration successful. Please login.</div>
                <?php endif; ?>

                <!-- Error message -->
                <?php if (isset($errorMsg)): ?>
                    <div class="alert alert-danger mt-3"><?= $errorMsg ?></div>
                <?php endif; ?>

                <!-- Email -->
                <div class="form-group mt-3">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">

                    <!-- <input type="email" class="form-control" id="email" name="email" placeholder="Email" required> -->
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fas fa-eye-slash" id="togglePassword" style="cursor: pointer;"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn btn-success btn-block">Login</button>

                <!-- Links -->
                <div class="form-footer">
                    <!-- <p>Don't have an account? <a href="register.php">Sign Up here</a></p> -->
                    <!-- <p>Or <a href="donor_view.php">Visit Donor View</a></p> -->
                    <p><a href="forgot_password.php">Forgot Password?</a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const togglePassword = document.querySelector("#togglePassword");
    const password = document.querySelector("#password");

    togglePassword.addEventListener("click", function () {
        const type = password.getAttribute("type") === "password" ? "text" : "password";
        password.setAttribute("type", type);
        this.classList.toggle("fa-eye");
        this.classList.toggle("fa-eye-slash");
    });
</script>
</body>
</html>

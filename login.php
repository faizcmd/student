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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />

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

        .top-nav .nav-links {
            display: flex;
            gap: 15px;
        }

        .top-nav .nav-links a {
            font-size: 1rem;
            text-decoration: none;
            color: #006400;
        }

        .top-nav .nav-links a:hover {
            color: #006400;
        }

        @media (max-width: 768px) {
      .top-nav .nav-links {
        display: none;
      }
      .top-nav .hamburger {
        display: block;
        cursor: pointer;
      }
    }
    @media (min-width: 769px) {
      .top-nav .hamburger {
        display: none;
      }
    }
        .container {
            margin-top: 80px;
            max-width: 700px;
        }

        .login-form {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
            background-color: #006400 !important; /* Dark green */
    border-color: #006400 !important;
            color: white;
        }

        .btn-login:hover {
            background-color: #006400 !important; /* Dark green */
    border-color: #006400 !important;
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

    <div class="nav-links" id="navLinks">
        <a href="scholarShipForm.php">🏠 Dashboard</a>
        <a href="donor_view.php">🙏 Donor View</a>
        <a href="register.php">🔐 Sign Up</a>
    </div>

    <div class="hamburger" id="hamburger" onclick="toggleMenu()">☰</div>
    <!-- <div class="hamburger" onclick="toggleMenu()">☰</div> -->
</div>

<script>
  function toggleMenu() {
    const nav = document.getElementById("navLinks");
    nav.style.display = nav.style.display === "flex" ? "none" : "flex";
    nav.style.flexDirection = "column";
    nav.style.gap = "10px";
  }
</script>

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

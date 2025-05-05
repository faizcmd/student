<?php
session_start();
include 'db_connect.php';
require 'send_email_otp.php'; // ✅ PHPMailer function file
require 'send_sms_otp.php';   // ✅ MSG91 function file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $name           = $_POST['name'];
    $email          = $_POST['email'];
    $mobile         = $_POST['mobile'];
    $password       = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $aadhaar_number = $_POST['aadhaar_number'];
    $gender         = $_POST['gender'];
    $zakat          = $_POST['zakat'] ?? 'No';
    $fee            = $_POST['fee'];
    $school_id      = $_POST['school_id'];

    // Generate OTPs
    $otp_email  = rand(100000, 999999);
    $otp_mobile = rand(100000, 999999);
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Check for duplicate email or mobile
    $check = $conn->prepare("SELECT id FROM students WHERE email = ? OR mobile = ?");
    $check->bind_param("ss", $email, $mobile);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('Email or mobile already registered.'); window.history.back();</script>";
        exit();
    }

    // Insert student record
    $stmt = $conn->prepare("INSERT INTO students 
        (name, email, mobile, password, aadhaar_number, gender, zakat, fee_amount, school_id, otp_email, otp_mobile, registered_ip) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssisis",
        $name, $email, $mobile, $password, $aadhaar_number, $gender, $zakat, $fee, $school_id, $otp_email, $otp_mobile, $ip_address);

    if ($stmt->execute()) {
        $_SESSION['email']  = $email;
        $_SESSION['mobile'] = $mobile;

        // Send OTPs
        $emailSent = sendEmailOTP($email, $otp_email);
        $smsSent   = sendSMSOTP($mobile, $otp_mobile);

        if ($emailSent && $smsSent) {
            header("Location: otp_verification.php");
            exit();
        } else {
            echo "<script>alert('Failed to send OTPs.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Something went wrong. Please try again.'); window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    margin-top: 100px;
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

    .form-container {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0px 0px 10px lightgray;
      margin-top:10px;
    }
    /* .form-header {
      background-color: #28a745;
      color: white;
      padding: 15px;
      text-align: center;
      border-radius: 8px 8px 0 0;
      margin-bottom: 20px;
    } */
  </style>
</head>

<body class="bg-light">
<!-- Top Navigation Bar -->
<div class="top-nav">
    <div class="title">🎓 EduHelp</div>
    <div class="nav-links">
        <!-- <a href="scholarShipForm.php">🏠 Dashboard</a> -->
        <a href="donor_view.php">🙏 Donor View</a>
       <a href="Login.php">🔐 Login</a>
        </div>
</div>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-6 col-12">
      <form method="post" action="register.php" class="form-container needs-validation" novalidate>
        <!-- <div class="form-header">
          <h2>Student Registration</h2>
        </div> -->

        <div class="mb-3">
          <label for="name" class="form-label">Full Name</label>
          <input type="text" class="form-control" name="name" required>
          <div class="invalid-feedback">Name is required.</div>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" name="email" 
          required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.(com|in|org|net|co\.in|edu)$"
         placeholder="e.g. yourname@gmail.com">
          <div class="invalid-feedback">Valid email required (e.g., user@example.com).</div>
        </div>

        <div class="mb-3">
  <label for="mobile" class="form-label">Mobile Number</label>
  <input type="text" class="form-control" name="mobile" pattern="^[6-9]\d{9}$" required>
  <div class="invalid-feedback">Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.</div>
</div>


        <!-- <div class="mb-3">
          <label for="mobile" class="form-label">Mobile Number (with +91)</label>
          <input type="text" class="form-control" name="mobile" pattern="^(\+91)?[6-9][0-9]{9}$" required>
          <div class="invalid-feedback">Enter valid 10-digit mobile starting with 6/7/8/9. Prefix +91 optional.</div>
        </div> -->

        <div class="mb-3">
  <label for="password" class="form-label">Password</label>
  <div class="input-group">
    <input type="password" class="form-control" name="password" id="password" required minlength="6">
    <span class="input-group-text">
      <i class="bi bi-eye-slash" id="togglePassword" style="cursor: pointer;"></i>
    </span>
    <div class="invalid-feedback">Password is required (min 6 characters).</div>
    
  </div>
  
</div>


        <div class="mb-3">
          <label for="aadhaar_number" class="form-label">Aadhaar Number</label>
          <input type="text" class="form-control" name="aadhaar_number" pattern="\d{12}" required>
          <div class="invalid-feedback">Enter a valid 12-digit Aadhaar number.</div>
        </div>

        <div class="mb-3">
          <label for="fee" class="form-label">Fee Amount (INR)</label>
          <input type="number" class="form-control" name="fee" required min="1">
          <div class="invalid-feedback">Enter a valid fee amount.</div>
        </div>

        <div class="mb-3">
          <label for="school_id" class="form-label">Select School/College</label>
          <select class="form-select" name="school_id" required>
            <option value="">-- Choose --</option>
            <?php
            $res = $conn->query("SELECT * FROM schools");
            while ($row = $res->fetch_assoc()) {
              echo "<option value='{$row['id']}'>{$row['school_name']}</option>";
            }
            ?>
          </select>
          <div class="invalid-feedback">Please select a school/college.</div>
        </div>

        <div class="mb-3">
          <label for="gender" class="form-label">Gender</label>
          <select class="form-select" name="gender" required>
            <option value="">-- Select Gender --</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
          <div class="invalid-feedback">Please select your gender.</div>
        </div>

        <div class="mb-3">
          <label for="zakat" class="form-label">Zakat Eligible?</label>
          <select class="form-select" name="zakat" required>
            <option value="">-- Are you eligible? --</option>
            <option value="yes">Yes</option>
            <option value="no">No</option>
          </select>
          <div class="invalid-feedback">Please select zakat eligibility.</div>
        </div>

        <div class="d-grid">
         <button class="btn btn-success btn-block" style="background-color: #28a745;" type="submit">Register</button>

        </div>

        <div class="text-center mt-3">
          <p>Already registered? <a href="login.php">Login</a></p>
          <p>Want to help? <a href="donor_view.php">Visit Donor View</a></p>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- JS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Bootstrap validation on submit
(() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);

    const inputs = form.querySelectorAll('input, select');
    inputs.forEach(input => {
      input.addEventListener('blur', () => {
        if (!input.checkValidity()) {
          input.classList.add('is-invalid');
        } else {
          input.classList.remove('is-invalid');
          input.classList.add('is-valid');
        }
      });

      input.addEventListener('input', () => {
        if (input.checkValidity()) {
          input.classList.remove('is-invalid');
          input.classList.add('is-valid');
        }
      });
    });
  });
})();


// Toggle password visibility
document.getElementById("togglePassword").addEventListener("click", function () {
  const password = document.getElementById("password");
  const icon = this;
  if (password.type === "password") {
    password.type = "text";
    icon.classList.remove("bi-eye-slash");
    icon.classList.add("bi-eye");
  } else {
    password.type = "password";
    icon.classList.remove("bi-eye");
    icon.classList.add("bi-eye-slash");
  }
});
</script>

</body>
</html>

<?php 
session_start();
include 'db_connect.php';
require 'send_email_otp.php'; 
require 'send_sms_otp.php';   

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name           = $_POST['name'];
    $email          = $_POST['email'];
    $mobile         = $_POST['mobile'];
    $password       = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $aadhaar_number = $_POST['aadhaar_number'];
    $gender         = $_POST['gender'];
    $zakat          = $_POST['zakat'] ?? 'No';
    $fee            = $_POST['fee'];
    $school_id      = $_POST['school_id'];

    $otp_email  = rand(100000, 999999);
    $otp_mobile = rand(100000, 999999);
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Check duplicates individually
    $errors = [];

    $check_email = $conn->prepare("SELECT id FROM students WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();
    if ($check_email->num_rows > 0) $errors[] = "Email";

    $check_mobile = $conn->prepare("SELECT id FROM students WHERE mobile = ?");
    $check_mobile->bind_param("s", $mobile);
    $check_mobile->execute();
    $check_mobile->store_result();
    if ($check_mobile->num_rows > 0) $errors[] = "Mobile";

    $check_aadhaar = $conn->prepare("SELECT id FROM students WHERE aadhaar_number = ?");
    $check_aadhaar->bind_param("s", $aadhaar_number);
    $check_aadhaar->execute();
    $check_aadhaar->store_result();
    if ($check_aadhaar->num_rows > 0) $errors[] = "Aadhaar";

    

    if (!empty($errors)) {
        $duplicateFields = implode(", ", $errors);
        echo "<script>alert('Already registered: $duplicateFields'); window.history.back();</script>";
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO students 
        (name, email, mobile, password, aadhaar_number, gender, zakat, fee_amount, school_id, otp_email, otp_mobile, registered_ip) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssisis",
        $name, $email, $mobile, $password, $aadhaar_number, $gender, $zakat, $fee, $school_id, $otp_email, $otp_mobile, $ip_address);

    if ($stmt->execute()) {
        $_SESSION['email']  = $email;
        $_SESSION['mobile'] = $mobile;

        $emailSent = sendEmailOTP($email, $otp_email);
        $smsSent   = sendSMSOTP($mobile, $otp_mobile);

        if ($emailSent && $smsSent) {
            header("Location: login.php");
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const passwordField = document.querySelector('#password');
    const passwordError = document.querySelector('#passwordError');

    // Password validation function
    function validatePassword() {
        const password = passwordField.value;
        const hasLetter = /[A-Za-z]/.test(password);
        const hasNumber = /\d/.test(password);
        const hasSpecial = /[@$!%*?&]/.test(password);
        const isLongEnough = password.length >= 6;

        if (!isLongEnough || !hasLetter || !hasNumber || !hasSpecial) {
            passwordField.classList.add('is-invalid');
            passwordField.classList.remove('is-valid');
            passwordError.style.display = 'block';
            return false;
        } else {
            passwordField.classList.remove('is-invalid');
            passwordField.classList.add('is-valid');
            passwordError.style.display = 'none';
            return true;
        }
    }

    // Add password validation on input
    passwordField.addEventListener('input', validatePassword);
    passwordField.addEventListener('blur', validatePassword);

    // Form submission validation
    form.addEventListener('submit', function(event) {
        if (!validatePassword()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Password visibility toggle
    document.querySelector('#togglePassword').addEventListener('click', function() {
        const type = passwordField.type === 'password' ? 'text' : 'password';
        passwordField.type = type;
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });

    // Other field validations
    const fields = Array.from(document.querySelectorAll('input, select')).filter(el => el.name && el.type !== 'hidden');
    
    fields.forEach((field) => {
        if (field !== passwordField) {
            field.addEventListener('input', function() {
                if (!this.checkValidity()) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        }
    });
  });
</script>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body { background-color: #f8f9fa; }
    .top-nav {
      position: fixed;
      top: 0; left: 0; right: 0;
      background-color: #ffffff;
      border-bottom: 1px solid #dee2e6;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 1000;
    }
    .top-nav .title { font-weight: bold; font-size: 1.2rem; }
    .top-nav .nav-links a {
      margin-left: 15px;
      font-size: 1rem;
      text-decoration: none;
      color: #006400 !important;
    }
    .top-nav .nav-links a:hover { color:#004d00 !important; }

    .form-container {
      margin-top: 100px;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0px 0px 10px lightgray;
    }

    .btn-primary {
    background-color: #006400 !important; /* Dark green */
    border-color: #006400 !important;
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
    }  </style>
</head>

<body>
<div class="top-nav">
  <div class="title">🎓 EduHelp</div>
  <div class="nav-links" id="navLinks">
    <a href="donor_view.php">🙏 Donor View</a>
    <a href="login.php">🔐 Login</a>
  </div>
  <div class="hamburger" onclick="toggleMenu()">☰</div>
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
    <div class="col-md-6 col-12">
      <form method="post" action="register.php" class="form-container needs-validation" novalidate>
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" name="name" placeholder="e.g. Ahmed Khan" required>
          <div class="invalid-feedback">Name is required.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email"
                 pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.(com|in|org|net|co\.in|edu)$"
                 placeholder="e.g. you@gmail.com" required>
          <div class="invalid-feedback">Valid email required.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Mobile Number</label>
          <input type="text" class="form-control" name="mobile"
                 pattern="^[6-9]\d{9}$" placeholder="e.g. 9876543210" required>
          <div class="invalid-feedback">Enter 10-digit mobile number starting with 6-9.</div>
        </div>

        <div class="mb-3"> 
          <label class="form-label">Password</label>
          <div class="input-group">
            <input type="password" class="form-control" name="password" id="password"
                   pattern="(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}" required>
            <span class="input-group-text">
              <i class="bi bi-eye-slash" id="togglePassword" style="cursor: pointer;"></i>
            </span>
          </div>
          <div class="invalid-feedback" id="passwordError">
            Password must be at least 6 characters and include one letter, one number, and one special character.
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Aadhaar Number</label>
          <input type="text" class="form-control" name="aadhaar_number" pattern="\d{12}" placeholder="e.g. 123456789012" required>
          <div class="invalid-feedback">Enter valid 12-digit Aadhaar number.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Fee Amount (INR)</label>
          <input type="number" class="form-control" name="fee" min="4" placeholder="e.g. 15000" required>
          <div class="invalid-feedback">Enter valid fee amount.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Select School/College</label>
          <select class="form-select" name="school_id" required>
            <option value="">-- Choose --</option>
            <?php
            $res = $conn->query("SELECT * FROM schools");
            while ($row = $res->fetch_assoc()) {
              echo "<option value='{$row['id']}'>{$row['school_name']}</option>";
            }
            ?>
          </select>
          <div class="invalid-feedback">Select your institution.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Gender</label>
          <select class="form-select" name="gender" required>
            <option value="">-- Select --</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
          <div class="invalid-feedback">Select gender.</div>
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

        <button type="submit" class="btn btn-primary w-100">Register</button>
      </form>
    </div>
  </div>
</div>

<script>
  (() => {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }

        // Validate all fields including password
        const fields = form.querySelectorAll('input, select');
        fields.forEach(field => {
          if (!field.checkValidity()) {
            field.classList.add('is-invalid');
          } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
          }
        });

        form.classList.add('was-validated');
      }, false);

      // Add real-time validation for password
      const passwordField = form.querySelector('#password');
      if (passwordField) {
        const passwordError = form.querySelector('#passwordError');
        function validatePassword() {
          const password = passwordField.value;
          const hasLetter = /[A-Za-z]/.test(password);
          const hasNumber = /\d/.test(password);
          const hasSpecial = /[@$!%*?&]/.test(password);
          const isLongEnough = password.length >= 6;

          if (!isLongEnough || !hasLetter || !hasNumber || !hasSpecial) {
            passwordField.classList.add('is-invalid');
            passwordField.classList.remove('is-valid');
            passwordError.style.display = 'block';
          } else {
            passwordField.classList.remove('is-invalid');
            passwordField.classList.add('is-valid');
            passwordError.style.display = 'none';
          }
        }

        passwordField.addEventListener('input', validatePassword);
        passwordField.addEventListener('blur', validatePassword);
      }
    });
  })();
</script>
</body>
</html>
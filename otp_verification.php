<?php
session_start();
 if (!isset($_SESSION['email']) || !isset($_SESSION['mobile'])) {
    header("Location: register.php");
   exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>OTP Verification</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    .resend-message {
        font-size: 0.9rem;
        color: green;
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
      font-size: 1.2rem;
    }

    .top-nav .nav-links a {
      margin-left: 15px;
      font-size: 1rem;
      text-decoration: none;
      color: #006400;
    }

    .top-nav .nav-links a:hover {
      color: #006400;
    }
  </style>
</head>
<body class="bg-light">
    <!-- Top Navigation Bar -->
<div class="top-nav">
    <div class="title">🎓 EduHelp</div>
    <div class="nav-links">
    
        
       <a href="login.php">🔐 Login</a>
        </div>
</div>

<div class="container " style="margin-top: 80px;">
  <div class="row justify-content-center">
    <div class="col-md-6 bg-white p-4 rounded shadow">
      <!-- <h4 class="mb-4 text-center">Verify Your Email and Mobile</h4> -->

      <!-- Show alert if redirected from login -->
      <?php if (isset($_GET['verify']) && $_GET['verify'] == 0): ?>
        <div class="alert alert-warning text-center">Please verify your account to proceed.</div>
      <?php endif; ?>

      <!-- OTP Form -->
      <form action="verify_otp.php" method="post">
        <div class="mb-3">
          <label>Email OTP (sent to <?= htmlspecialchars($_SESSION['email']) ?>):</label>
          <input type="text" name="otp_email" class="form-control" required pattern="\d{6}">
        </div>
        <div class="mb-3">
          <label>Mobile OTP (sent to <?= htmlspecialchars($_SESSION['mobile']) ?>):</label>
          <input type="text" name="otp_mobile" class="form-control" required pattern="\d{6}">
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-success">Verify OTP</button>
        </div>
      </form>

      <!-- Resend OTP Section -->
      <div class="text-center mt-3">
        <form action="resend_otp.php" method="post" id="resendForm">
          <button type="submit" class="btn btn-link" id="resendBtn">Resend OTP</button>
        </form>
        <div id="resendMessage" class="resend-message mt-2" style="display:none;">
          OTP resent successfully. Please wait <span id="countdown">60</span> seconds to retry.
        </div>
      </div>

    </div>
  </div>
</div>

<script>
  const resendBtn = document.getElementById('resendBtn');
  const resendForm = document.getElementById('resendForm');
  const resendMsg = document.getElementById('resendMessage');
  const countdownSpan = document.getElementById('countdown');

  resendForm.addEventListener('submit', function(e) {
    resendBtn.disabled = true;
    resendMsg.style.display = 'block';

    let countdown = 60;
    const interval = setInterval(() => {
      countdown--;
      countdownSpan.textContent = countdown;
      if (countdown <= 0) {
        clearInterval(interval);
        resendBtn.disabled = false;
        resendMsg.style.display = 'none';
      }
    }, 1000);
  });
</script>

</body>
</html>

<?php
include 'db_connect.php';
include 'send_email_otp.php'; // Include your PHPMailer logic
include 'send_sms_otp.php';   // Include your MSG91 logic

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name           = $_POST['name'];
    $email          = $_POST['email'];
    $mobile         = $_POST['mobile'];
    $password       = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $aadhaar_number = $_POST['aadhaar_number'];
    $zakat          = $_POST['zakat'];
    $fee            = $_POST['fee'];
    $school_id      = $_POST['school_id'];

    $otp_email  = rand(100000, 999999);
    $otp_mobile = rand(100000, 999999);

    $stmt = $conn->prepare("INSERT INTO students (name, email, mobile, password, aadhaar_number, zakat, fee_amount, school_id, otp_email, otp_mobile)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssissi", $name, $email, $mobile, $password, $aadhaar_number, $zakat, $fee, $school_id, $otp_email, $otp_mobile);

    if ($stmt->execute()) {
        // Send OTPs
        sendEmailOTP($email, $otp_email);       // From send_email_otp.php
        sendSMSOTP($mobile, $otp_mobile);       // From send_sms_otp.php

        echo "<script>
            alert('Registration successful. Check your mobile and email for OTP.');
            window.location.href='otp_verification.php?email=$email&mobile=$mobile';
        </script>";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
<!-- HTML form stays the same -->


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Student Registration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-5">
  <h2 class="text-center mb-4">Student Registration</h2>
  <div class="card shadow-lg">
    <div class="card-body">
      <form method="post" action="register.php">
        <div class="mb-3">
          <label for="name" class="form-label">Name</label>
          <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
          <label for="mobile" class="form-label">Mobile</label>
          <input type="text" class="form-control" id="mobile" name="mobile" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="mb-3">
          <label for="aadhaar_number" class="form-label">Aadhaar Number</label>
          <input type="text" class="form-control" id="aadhaar_number" name="aadhaar_number" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Zakat Eligible</label><br>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="zakat" value="yes" required>
            <label class="form-check-label">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="zakat" value="no">
            <label class="form-check-label">No</label>
          </div>
        </div>

        <div class="mb-3">
          <label for="fee" class="form-label">Fee Amount</label>
          <input type="number" class="form-control" id="fee" name="fee" required>
        </div>

        <div class="mb-3">
          <label for="school_id" class="form-label">School/College</label>
          <select class="form-select" name="school_id" id="school_id" onchange="fetchBankDetails(this.value)" required>
            <option value="">-- Select School/College --</option>
            <?php
            $res = $conn->query("SELECT * FROM schools");
            while ($row = $res->fetch_assoc()) {
              echo "<option value='{$row['id']}'>{$row['school_name']}</option>";
            }
            ?>
          </select>
        </div>

        <!-- Bank details box -->
        <div id="bankDetailsBox" class="card shadow-sm p-3 mb-3" style="display:none;">
          <h5 class="mb-3">Bank Details</h5>
          <div id="bankDetails"></div>
        </div>

        <div class="d-flex justify-content-between mt-4">
          <button type="submit" class="btn btn-primary">Register</button>
          <p class="mb-0">Already have an account? <a href="login.php">Login here</a></p>
        </div>

        <p class="mt-3">Or want to help? <a href="donor_view.php">Visit Donor View</a></p>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<script>
function fetchBankDetails(schoolId) {
  if (!schoolId) {
    document.getElementById('bankDetails').innerHTML = '';
    document.getElementById('bankDetailsBox').style.display = 'none';
    return;
  }

  fetch('fetch_bank.php?school_id=' + schoolId)
    .then(res => res.text())
    .then(html => {
      document.getElementById('bankDetails').innerHTML = html;
      document.getElementById('bankDetailsBox').style.display = 'block';
    });
}
</script>

</body>
</html>

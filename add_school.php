<?php
session_start();
// Optional: Check if admin is logged in
include 'db_connect.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add School Bank Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
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
    }  
    </style>

<body class="container">

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

    <h2>Add School and Bank Details</h2>
    <div class="container" style="max-width: 500px; margin-top: 80px;">

    <form action="save_school.php" method="POST">
        <div class="mb-3">
            <label>School Name</label>
            <input type="text" name="school_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Account Holder Name</label>
            <input type="text" name="account_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Account Number</label>
            <input type="text" name="account_number" class="form-control">
        </div>
        <div class="mb-3">
            <label>IFSC Code</label>
            <input type="text" name="ifsc_code" class="form-control">
        </div>
        <div class="mb-3">
            <label>Bank Name</label>
            <input type="text" name="bank_name" class="form-control">
        </div>
        <div class="mb-3">
            <label>UPI Name</label>
            <input type="text" name="upi_name" class="form-control">
        </div>
        <div class="mb-3">
            <label>UPI ID (Required for Razorpay)</label>
            <input type="text" name="upi_id" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Add School</button>
    </form>
</div>
</body>
</html>

<?php
include 'db_connect.php';

// Toast message handler for successful donation
$paymentSuccess = isset($_GET['payment']) && $_GET['payment'] == 'success';

// Handle search, sort, and page
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Start base WHERE clause
$whereClause = "students.is_helped = 0 AND students.form_path IS NOT NULL AND students.form_path != ''";

// Add search if given
if (!empty($search)) {
    $safeSearch = mysqli_real_escape_string($conn, $search);
    $whereClause .= " AND (students.name LIKE '%$safeSearch%' OR schools.school_name LIKE '%$safeSearch%')";
}

// Count total records for pagination
$count_sql = "SELECT COUNT(*) AS total FROM students 
              JOIN schools ON students.school_id = schools.id 
              WHERE $whereClause";

$total_result = $conn->query($count_sql);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch student data
$data_sql = "SELECT students.*, schools.school_name, schools.account_name, schools.account_number, 
             schools.ifsc_code, schools.bank_name, schools.upi_name, schools.upi_id 
             FROM students 
             JOIN schools ON students.school_id = schools.id 
             WHERE $whereClause";

// Sorting
if ($sort == 'ASC' || $sort == 'DESC') {
    $data_sql .= " ORDER BY students.fee_amount $sort";
} else {
    $data_sql .= " ORDER BY students.created_at DESC";
}

$data_sql .= " LIMIT $limit OFFSET $offset";

$result = $conn->query($data_sql);

// Fetch into array
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Students Awaiting Help</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <style>
   body {
    background-color: #f2f4f6;
    padding-top: 10px; /* enough space below navbar + search bar */
    font-family: Arial, sans-serif; 
    margin: 0;
    color: #333;
}

    .dashboard-card {
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        margin-top: 80px;
    }

    .welcome-message {
        font-size: 1.8rem;
        font-weight: bold;
        color: #343a40;
    }

    .btn-custom {
        margin: 12px 0;
        padding: 12px;
        font-size: 1rem;
    }

    .info-box {
        background-color: #e9f7ef;
        border-left: 5px solid #28a745;
        padding: 10px 15px;
        border-radius: 6px;
        font-size: 0.95rem;
        margin-bottom: 20px;
        color: #155724;
    }

    .icon {
        font-size: 18px;
        margin-right: 6px;
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

    * { 
        box-sizing: border-box; 
    }

    h2 { 
        text-align: center; 
        color: #00796b; 
        margin-bottom: 30px; 
    }

    .container { 
        max-width: 1200px; 
        margin:auto;
    }

    .search-form{ 
        margin-bottom: 20px; 
        display: flex; 
        justify-content: center; 
        gap: 10px; 
        flex-wrap: wrap; 
        
        
    }
     .sort-form { 
        margin-bottom: 20px; 
        display: flex; 
        justify-content: center; 
        gap: 10px; 
        flex-wrap: wrap; 
        
        
    }

    .search-form input, .sort-form select { 
        padding: 10px; 
        font-size: 1rem; 
        border-radius: 5px; 
        border: 1px solid #ccc; 
        margin-left: 10px; 
        
    }

    .student-card { 
        background: #fff; 
        border-radius: 10px; 
        padding: 20px; 
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
        margin-bottom: 20px; 
    }

    .student-card h3 { 
        color: #00796b; 
        margin-bottom: 10px; 
    }

    .student-card p { 
        line-height: 1.5; 
    }

    input[type="submit"], .btn {
        padding: 10px 20px; 
        background: #009688; 
        
        color: white; 
        border: none; 
        border-radius: 5px;
        text-decoration: none; 
        font-size: 1rem; 
        margin-top: 10px; 
        display: inline-block; 
        transition: 0.3s ease;
        cursor: pointer; 
    }

    .btn:hover, input[type="submit"]:hover { 
        background: #00796b; 
    }

    .btn-view { 
        background: #2196F3; 
    }

    .btn-view:hover { 
        background: #1976D2; 
    }

    .btn-register { 
        background: #00BCD4; 
    }

    .btn-register:hover { 
        background: #0097A7; 
    }

    .logout-btn { 
        background: #f44336; 
        margin-right: 5px; 
    }

    .logout-btn:hover { 
        background: #e53935; 
    }

    .pagination { 
        display: flex; 
        justify-content: center; 
        flex-wrap: wrap; 
        margin-top: 20px; 
    }

    .pagination a {
        padding: 10px; 
        margin: 5px; 
        background: #00796b; 
        color: white; 
        text-decoration: none; 
        border-radius: 5px;
    }

    .pagination a:hover { 
        background: #004d40; 
    }

    .toast {
        position: fixed; 
        bottom: 30px; 
        right: 30px; 
        background: #4caf50; 
        color: white; 
        padding: 15px 25px;
        border-radius: 5px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.2); 
        z-index: 9999;
        animation: fadeInOut 4s forwards;
    }

    @keyframes fadeInOut {
        0% { opacity: 0; transform: translateY(20px); }
        10%, 90% { opacity: 1; transform: translateY(0); }
        100% { opacity: 0; transform: translateY(20px); }
    }

    @media (max-width: 768px) {
        .search-form, .sort-form { 
            flex-direction: column; 
            align-items: stretch; 
        }
    }

    .search-bar-wrapper {
        position: sticky;
        top: 60px; 
        margin-top: 70px; 
        /* background: #fff; */
        z-index: 999;
        padding: 10px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        /* box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); */
        margin-bottom: 20px;
    }

    .search-controls {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
       
    }

    .search-form input[type="text"] {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 6px;
        width: 200px;
        
    }

    .search-form input[type="submit"] {
        padding: 8px 14px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;

    }

    .sort-form select {
        padding: 8px;
        
        border-radius: 6px;
        border: 1px solid #ccc;
    }

    .search-title {
        font-size: 20px;
        font-weight: 600;
        color: #333;
        white-space: nowrap;
        
    }
  </style>
</head>

<body>
  <!-- Top Navigation Bar -->
  <div class="top-nav">
    <div class="title">🎓 EduHelp Dashboard</div>
    <div class="nav-links">
        <a href="scholarShipForm.php">🏠 Dashboard</a>
        <a href="register.php">🔑 Register</a>
        <a href="logout.php" style="color: #dc3545;">🔓 Logout</a>
    </div>
  </div>

  <div class="container">
  <div class="search-bar-wrapper">
    <div class="search-controls">
      <form method="GET" action="" class="search-form" style="display: flex; gap: 10px; flex-wrap: wrap;">
        <!-- Search Field -->
        <input type="text" name="search" placeholder="Search by Name or School" value="<?= htmlspecialchars($search) ?>">
        <input type="submit" value="Search">

        <!-- Sort Dropdown: onchange auto-submit -->
        <select name="sort" onchange="this.form.submit()">
          <option value="" <?= ($sort == '') ? 'selected' : '' ?>>-- Sort By Fee --</option>
          <option value="ASC" <?= ($sort == 'ASC') ? 'selected' : '' ?>>Fee Low to High</option>
          <option value="DESC" <?= ($sort == 'DESC') ? 'selected' : '' ?>>Fee High to Low</option>
        </select>
      </form>

      <!-- <a href="donor_view.php" style="text-decoration: none; color: white;">
  <button type="button">Reset Filters</button>
</a> -->

    
</div>
      <div class="search-title">Students Awaiting Help</div>
    </div>

    <div id="student-list">
      <?php if (count($students) > 0): ?>
        <?php foreach ($students as $row): ?>
          <div class="student-card">
            <h3><?= htmlspecialchars($row['name']) ?></h3>
            <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
            <p><strong>Zakat Eligible:</strong> <?= htmlspecialchars($row['zakat']) ?></p>
            <p><strong>School/College:</strong> <?= htmlspecialchars($row['school_name']) ?></p>

            <h4>School Bank Details</h4>
            <p><strong>Account Name:</strong> <?= htmlspecialchars($row['account_name']) ?></p>
            <p><strong>Account Number:</strong> <?= htmlspecialchars($row['account_number']) ?></p>
            <p><strong>IFSC Code:</strong> <?= htmlspecialchars($row['ifsc_code']) ?></p>
            <p><strong>Bank Name:</strong> <?= htmlspecialchars($row['bank_name']) ?></p>
            <p><strong>UPI Name:</strong> <?= htmlspecialchars($row['upi_name']) ?></p>
            <p><strong>UPI ID:</strong> <?= htmlspecialchars($row['upi_id']) ?></p>

            <?php if (!empty($row['form_path'])): ?>
              <a href="<?= htmlspecialchars($row['form_path']) ?>" class="btn btn-view" target="_blank">View Student Info</a>
            <?php endif; ?>

            <form action="razorPay.php" method="POST">
              <input type="hidden" name="student_id" value="<?= $row['id'] ?>">
              <input type="hidden" name="student_name" value="<?= $row['name'] ?>">
              <input type="hidden" name="student_email" value="<?= $row['email'] ?>">
              <input type="hidden" name="fee_amount" value="<?= $row['fee_amount'] ?>">
              <input type="submit" value="Donate Now ₹<?= htmlspecialchars($row['fee_amount']) ?> with Razorpay" style="cursor: pointer;">
            </form>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No students found.</p>
      <?php endif; ?>
    </div>

    <div class="pagination">
      <?php 
      // Simple pagination
      if ($total_pages > 1) {
          for ($page = 1; $page <= $total_pages; $page++) {
              echo "<a href='?page=$page'>$page</a>";
          }
      }
      ?>
    </div>

    <!-- Toast Notifications -->
    <div id="toast" class="toast" style="display:none;">Donation Completed!</div>
  </div>

  <script>
    // Toast notification display
    function showToast(message) {
      var toast = document.getElementById('toast');
      toast.textContent = message;
      toast.style.display = 'block';
      setTimeout(function() {
        toast.style.display = 'none';
      }, 4000);
    }
  </script>

</body>
</html>

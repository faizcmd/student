<?php 
include 'db_connect.php';

// Handle the search, sort, and page
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'ASC';
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$limit = 5; // Set items per page
$offset = ($page - 1) * $limit;

// Prepare the SQL query with search and sort logic
$sql = "SELECT students.*, schools.school_name, schools.account_name, schools.account_number, 
        schools.ifsc_code, schools.bank_name, schools.upi_name, schools.upi_id 
        FROM students JOIN schools ON students.school_id = schools.id WHERE students.is_helped = 0";

if ($search != '') {
    $sql .= " AND (students.name LIKE '%$search%' OR schools.school_name LIKE '%$search%')";
}

$sql .= " ORDER BY students.fee_amount $sort LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Pagination setup
$total_result = $conn->query("SELECT COUNT(*) AS total FROM students WHERE is_helped = 0");
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit); // Calculate total pages

// Fetch the results into an array for AJAX response
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Students Awaiting Help</title>
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f9;
      margin: 0;
      padding: 20px;
      color: #333;
    }

    h2 {
      text-align: center;
      color: #00796b;
      margin-bottom: 30px;
      font-size: 2em;
      text-transform: uppercase;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    .search-form,
    .sort-form {
      margin-bottom: 20px;
      display: flex;
      justify-content: center;
      gap: 10px;
    }

    .search-form input,
    .sort-form select {
      padding: 10px ;
      font-size: 1rem;
      border-radius: 5px;
      border: 1px solid #ccc;
      margin-left: 10px;
      /* background-color: black; */
    }

    .student-card {
      background-color: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
      transition: transform 0.3s ease;
    }

    .student-card:hover {
      transform: scale(1.02);
    }

    .student-card h3 {
      color: #00796b;
      font-size: 1.5em;
      margin-bottom: 10px;
    }

    .student-card p {
      font-size: 1rem;
      line-height: 1.5;
    }

    .student-card h4 {
      margin-top: 15px;
      color: #00796b;
    }

    input[type="submit"] {
      padding: 12px 20px;
      background-color: #009688;
      color: #fff;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    input[type="submit"]:hover {
      background-color: #00796b;
    }

    .btn {
      padding: 12px 20px;
      background-color: #4CAF50;
      color: white;
      border-radius: 5px;
      text-decoration: none;
      margin: 5px;
      display: inline-block;
      font-size: 1rem;
      text-align: center;
      transition: background-color 0.3s ease;
    }

    .btn:hover {
      background-color: #45a049;
    }

    .logout-btn {
      background-color: #f44336;
    }

    .logout-btn:hover {
      background-color: #e53935;
    }

    .pagination {
      display: flex;
      justify-content: center;
      margin-top: 20px;
    }

    .pagination a {
      padding: 10px;
      margin: 0 5px;
      background-color: #00796b;
      color: white;
      text-decoration: none;
      border-radius: 5px;
    }

    .pagination a:hover {
      background-color: #004d40;
    }

  </style>
</head>
<body>

  <div class="container">

    <!-- Title -->
    <h2>Students Awaiting Help</h2>

    <div style="position: sticky; top: 0; background: #fff; z-index: 1000; padding: 15px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px;">
      <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        
        <!-- Filter/Search Section -->
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
          <form method="GET" action="" class="search-form" style="margin: 0;">
            <input type="text" name="search" placeholder="Search by Name or School" value="<?= htmlspecialchars($search ?? '') ?>" />
            <input type="submit" value="Search" />
          </form>

          <form method="GET" action="" class="sort-form" style="margin: 0;">
            <select name="sort" onchange="this.form.submit()">
              <option value="ASC" <?= (isset($sort) && $sort == 'ASC') ? 'selected' : '' ?>>Fee Low to High</option>
              <option value="DESC" <?= (isset($sort) && $sort == 'DESC') ? 'selected' : '' ?>>Fee High to Low</option>
            </select>
          </form>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 10px;">
          <a href="show_my_profile.php" class="btn">Dashboard</a>
          <a href="register.php" class="btn">Register</a>
          <a href="logout.php" class="btn logout-btn">Logout</a>
        </div>
      </div>
    </div>

    <!-- Display Students -->
    <div id="student-list">
      <?php if (count($students) > 0): ?>
        <?php foreach($students as $row): ?>
          <div class="student-card">
            <h3><?= htmlspecialchars($row['name']) ?> </h3>
            <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
            <p><strong>Mobile:</strong> <?= htmlspecialchars($row['mobile']) ?></p>
            <p><strong>Zakat Eligible:</strong> <?= htmlspecialchars($row['zakat']) ?></p>
            <p><strong>School/College:</strong> <?= htmlspecialchars($row['school_name']) ?></p>

            <h4>School Bank Details</h4>
            <p><strong>Account Name:</strong> <?= htmlspecialchars($row['account_name']) ?></p>
            <p><strong>Account Number:</strong> <?= htmlspecialchars($row['account_number']) ?></p>
            <p><strong>IFSC Code:</strong> <?= htmlspecialchars($row['ifsc_code']) ?></p>
            <p><strong>Bank Name:</strong> <?= htmlspecialchars($row['bank_name']) ?></p>
            <p><strong>UPI Name:</strong> <?= htmlspecialchars($row['upi_name']) ?></p>
            <p><strong>UPI ID:</strong> <?= htmlspecialchars($row['upi_id']) ?></p>

            <form action="razorPay.php" method="POST">
              <input type="hidden" name="student_id" value="<?= $row['id'] ?>">
              <input type="hidden" name="student_name" value="<?= $row['name'] ?>">
              <input type="hidden" name="student_email" value="<?= $row['email'] ?>">
              <input type="hidden" name="fee_amount" value="<?= $row['fee_amount'] ?>">
              <input type="submit" value="Donate Now ₹<?= htmlspecialchars($row['fee_amount']) ?> with Razorpay">
            </form>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No students need help at the moment.</p>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <div class="pagination">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="#" class="page-link" data-page="<?= $i ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>

  </div>

  <script>
    // AJAX for Pagination
    $(document).on('click', '.page-link', function() {
      var page = $(this).data('page');
      var search = $('input[name="search"]').val();
      var sort = $('select[name="sort"]').val();
      
      $.ajax({
        url: 'donor_view.php',
        type: 'GET',
        data: {
          page: page,
          search: search,
          sort: sort
        },
        success: function(response) {
          $('#student-list').html($(response).find('#student-list').html());
          $('.pagination').html($(response).find('.pagination').html());
        }
      });
    });
  </script>

</body>
</html>

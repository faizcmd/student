<?php
include 'db_connect.php';

$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'ASC';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Build WHERE clause
$where = "WHERE students.is_helped = 0";
if (!empty($search)) {
    $searchEscaped = $conn->real_escape_string($search);
    $where .= " AND (students.name LIKE '%$searchEscaped%' OR schools.school_name LIKE '%$searchEscaped%')";
}

// Count total records
$totalQuery = "SELECT COUNT(*) as total 
               FROM students 
               JOIN schools ON students.school_id = schools.id 
               $where";
$totalResult = $conn->query($totalQuery);
$total = $totalResult->fetch_assoc()['total'] ?? 0;

// Fetch students data
$sql = "SELECT students.*, schools.school_name, schools.account_name, schools.account_number, 
               schools.ifsc_code, schools.bank_name, schools.upi_name, schools.upi_id 
        FROM students 
        JOIN schools ON students.school_id = schools.id 
        $where 
        ORDER BY students.fee_amount $sort 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$start = $offset + 1;
$end = min($offset + $limit, $total);

// Output Results
if ($result->num_rows > 0) {
    echo "<p>Showing $start to $end of $total students</p>";

    while($row = $result->fetch_assoc()) {
        ?>
        <div class="student-card">
          <h3><?= htmlspecialchars($row['name']) ?></h3>
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
        <?php
    }

    // Pagination Links
    $totalPages = ceil($total / $limit);
    echo '<div class="pagination">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = ($i == $page) ? 'style="background:#004d40;"' : '';
        echo "<a href='javascript:void(0);' class='pagination-link' data-page='$i' $active>$i</a>";
    }
    echo '</div>';

} else {
    echo "<p>No students found.</p>";
}
?>

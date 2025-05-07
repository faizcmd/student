<?php  
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ✅ Fetch user data with JOIN to get school_name
$sql = "SELECT s.name, s.email, s.mobile, s.aadhaar_number, s.fee_amount, s.zakat, s.gender, s.school_id, sch.school_name 
        FROM students s
        LEFT JOIN schools sch ON s.school_id = sch.id
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// ✅ Fetch all schools for dropdown
$schools_result = $conn->query("SELECT id, school_name FROM schools ORDER BY school_name ASC");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $aadhaar = trim($_POST['aadhaar_number']);
    $fee = trim($_POST['fee_amount']);
    $zakat = trim($_POST['zakat']);
    $gender = trim($_POST['gender']);
    $school_id = intval($_POST['school_id']);  // This will properly handle the school_id as integer

    // Validate the form data
    if (empty($name) || empty($email) || empty($mobile) || empty($aadhaar) || empty($fee) || empty($zakat) || empty($gender) || empty($school_id)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
        $error = "Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.";
    } elseif (!preg_match('/^\d{12}$/', $aadhaar)) {
        $error = "Aadhaar number must be 12 digits.";
    } elseif ($fee < 1000 || $fee > 9999) {  // Allow only 4 digit fee amounts
        $error = "Fee amount must be a 4-digit number.";
    } else {
        $update_sql = "UPDATE students SET name = ?, email = ?, mobile = ?, aadhaar_number = ?, fee_amount = ?, zakat = ?, gender = ?, school_id = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssisssi", $name, $email, $mobile, $aadhaar, $fee, $zakat, $gender, $school_id, $user_id);

        if ($update_stmt->execute()) {
            $success = "Profile updated successfully.";

            // Refresh user data
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $error = "Error updating profile. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f2f4f6;
            padding-top: 70px;
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
            text-decoration: none;
            color: #006400;
        }
        .top-nav .nav-links a:hover {
            color: #006400;
        }
        .form-container {
            max-width: 700px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
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
</head>
<body>

<div class="top-nav">
    <div class="title">🎓 EduHelp Dashboard</div>
    <div class="nav-links" id="navLinks">
        <a href="scholarShipForm.php">🏠 Dashboard</a>
        <a href="donor_view.php">🙏 Donor View</a>
        <a href="login.php">🔑 Login</a>
        <a href="logout.php" style="color: #dc3545;">🔓 Logout</a>
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
<div class="container mt-5">
    <div class="form-container">
        <h2 class="mb-4">Edit Profile</h2>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="mobile" class="form-label">Mobile Number</label>
                <input type="text" class="form-control" id="mobile" name="mobile" pattern="[6-9]{1}[0-9]{9}" maxlength="10" value="<?= htmlspecialchars($user['mobile']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="aadhaar_number" class="form-label">Aadhaar Number</label>
                <input type="text" class="form-control" id="aadhaar_number" name="aadhaar_number" pattern="\d{12}" maxlength="12" value="<?= htmlspecialchars($user['aadhaar_number']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="fee_amount" class="form-label">Fee Amount</label>
                <input type="number" class="form-control" id="fee_amount" name="fee_amount" min="1000" max="9999" value="<?= htmlspecialchars($user['fee_amount']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="zakat" class="form-label">Eligible for Zakat?</label>
                <select class="form-control" id="zakat" name="zakat" required>
                    <option value="">-- Select --</option>
                    <option value="Yes" <?= ($user['zakat'] == 'Yes') ? 'selected' : '' ?>>Yes</option>
                    <option value="No" <?= ($user['zakat'] == 'No') ? 'selected' : '' ?>>No</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="gender" class="form-label">Gender</label>
                <select class="form-control" id="gender" name="gender" required>
                    <option value="">-- Select --</option>
                    <option value="Male" <?= ($user['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= ($user['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                    <!-- <option value="Other" <?= ($user['gender'] == 'Other') ? 'selected' : '' ?>>Other</option> -->
                </select>
            </div>

            <div class="mb-3">
                <label for="school_id" class="form-label">School/College Name</label>
                <select class="form-control" id="school_id" name="school_id" required>
                    <option value="">-- Select School --</option>
                    <?php while ($row = $schools_result->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>" <?= ($row['id'] == $user['school_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['school_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">💾 Save Changes</button>
            <a href="show_my_profile.php" class="btn btn-secondary"> Show My Profile</a>
        </form>
    </div>
</div>

</body>
</html>

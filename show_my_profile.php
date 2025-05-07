<?php 
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT students.*, schools.school_name 
        FROM students 
        LEFT JOIN schools ON students.school_id = schools.id 
        WHERE students.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <style> 
   body {
            background-color: #f2f4f6;
            padding-top: 20px; /* adds spacing below fixed top navbar */
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

    </style>
</head>
<body>

<!-- Top Navigation Bar -->
<div class="top-nav">
    <div class="title">🎓 EduHelp Dashboard</div>
    <div class="nav-links" id="navLinks">
        <a href="scholarShipForm.php"> 🏠 Dashboard </a>
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

<div class="container my-5">
    <h2 class="text-center mb-4, mt-3 ;">Welcome, <?= htmlspecialchars($user['name']) ?>!</h2>

    <div class="card shadow mt-3">
        <div class="card-header bg-primary text-white">
            My Profile
        </div>
        <div class="card-body">
            <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Mobile:</strong> <?= htmlspecialchars($user['mobile']) ?></p>
            <p><strong>School:</strong> <?= htmlspecialchars($user['school_name'] ?? 'Not Assigned') ?></p>
            <p><strong>Zakat Eligible:</strong> <?= htmlspecialchars($user['zakat']) ?></p>
            <p><strong>Fee Amount:</strong> ₹<?= htmlspecialchars($user['fee_amount']) ?></p>
            <p><strong>Aadhaar Number:</strong> <?= htmlspecialchars($user['aadhaar_number']) ?></p>
            <p><strong>Gender:</strong> <?= htmlspecialchars($user['gender']) ?></p> <!-- Added Gender -->
            
            <!-- Scholarship form section -->
            <hr>
            <h5 class="mt-4">Scholarship Form</h5>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <?php if (empty($user['form_path'])): ?>
                        <div class="alert alert-warning mb-0">
                            <strong>Action Needed:</strong> Please upload your filled and stamped scholarship form 
                            <a href="scholarShipForm.php" class="btn btn-sm btn-outline-primary ms-2">Upload Form</a>
                        </div>
                    <?php else: ?>
                        <p class="mb-0">
                            <strong>Form Uploaded:</strong> 
                            <a href="<?= htmlspecialchars($user['form_path']) ?>" target="_blank" class="btn btn-sm btn-success ms-2">View Form</a>
                        </p>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="edit_profile.php" class="btn btn-outline-primary">✏️ Edit My Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

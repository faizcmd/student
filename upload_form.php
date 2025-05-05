<?php    
session_start();

$conn = new mysqli("localhost", "root", "root", "student_donation");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$isLoggedIn = isset($_SESSION['user_id']);

if (!$isLoggedIn) {
    header("Location: login.php");
    exit();
}

$message = "";
$form_path = "";
$student = ['name' => '', 'email' => '', 'mobile' => ''];

// Fetch student details and form path
$stmt = $conn->prepare("SELECT name, email, mobile, form_path FROM students WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($student['name'], $student['email'], $student['mobile'], $form_path);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['scholarship_form'])) {
    $upload_folder = "uploads/";
    $original_filename = basename($_FILES["scholarship_form"]["name"]);
    $filename = time() . "_" . preg_replace("/[^A-Za-z0-9_.]/", "_", $original_filename);
    $target_file = $upload_folder . $filename;

    $fileTmpPath = $_FILES["scholarship_form"]["tmp_name"];
    $fileSize = $_FILES["scholarship_form"]["size"];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileTmpPath);
    finfo_close($finfo);

    $fileHeader = file_get_contents($fileTmpPath, false, null, 0, 4);

    // ✅ Check size, MIME type and header
    if ($mimeType === 'application/pdf' && $fileSize <= 2 * 1024 * 1024 && $fileHeader === "%PDF") {
        $uploadedHash = hash_file('sha256', $fileTmpPath);

        // ✅ Fetch original generated hash from DB
        // $conn = new mysqli("localhost", "root", "root", "student_donation");
        $stmt = $conn->prepare("SELECT generated_form_hash FROM students WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->bind_result($originalHash);
        $stmt->fetch();
        $stmt->close();

        // ✅ Validate hash match
        if ($uploadedHash === $originalHash) {
            if (!file_exists($upload_folder)) {
                mkdir($upload_folder, 0777, true);
            }

            if (move_uploaded_file($fileTmpPath, $target_file)) {
                $stmt = $conn->prepare("UPDATE students SET form_path = ? WHERE id = ?");
                $stmt->bind_param("si", $target_file, $_SESSION['user_id']);
                if ($stmt->execute()) {
                    header("Location: show_my_profile.php?success=1");
                    exit();
                } else {
                    $message = "<div class='alert alert-danger'>❌ Database error: " . htmlspecialchars($stmt->error) . "</div>";
                }
                $stmt->close();
            } else {
                $message = "<div class='alert alert-danger'>⚠️ Upload failed. Try again.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>⚠️ Uploaded form doesn't match the original generated form. Please re-upload the correct form.</div>";
        }

        $conn->close();
    } else {
        $message = "<div class='alert alert-warning'>Only valid PDF files under 2MB are allowed.</div>";
    }
}
// $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EduHelp - Upload Form</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    </style>
</head>
<body>

<!-- Top Navigation Bar -->
<div class="top-nav">
    <div class="title">🎓 EduHelp</div>
    <div class="nav-links">
        <a href="scholarShipForm.php">🏠 Dashboard</a>
        <a href="donor_view.php">🙏 Donor View</a>
        <?php if ($isLoggedIn): ?>
            <a href="logout.php" style="color: #dc3545;">🔓 Logout</a>
        <?php else: ?>
            <a href="login.php">🔐 Login</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <h2 class="mb-3 text-primary text-center">Upload Your Signed Scholarship Form</h2>

    <!-- 👤 Profile Info -->
    <div class="info-card">
        <h5 class="text-info">👤 Your Info</h5>
        <p><strong>Name:</strong> <?= htmlspecialchars($student['name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
        <p><strong>Mobile:</strong> <?= htmlspecialchars($student['mobile']) ?></p>
    </div>

    <?php if (!empty($form_path)): ?>
        <!-- 🔒 Already Uploaded -->
        <div class="alert alert-success">
            ✅ You already uploaded your form. 
            <a href="<?= htmlspecialchars($form_path) ?>" target="_blank" class="btn btn-sm btn-outline-success ml-2">
                <i class="fas fa-eye"></i> View Form
            </a>
        </div>
    <?php else: ?>
        <!-- 🔓 Upload Form -->
        <form action="upload_form.php" method="POST" enctype="multipart/form-data" class="shadow p-4 bg-white rounded" id="uploadForm">
            <div class="form-group">
                <label for="scholarship_form"><strong>Select PDF File:</strong></label>
                <input type="file" name="scholarship_form" id="scholarship_form" class="form-control-file" required accept="application/pdf">
                <div id="file-preview" class="text-success font-weight-bold mt-2" style="display:none;"></div>
            </div>
            <button type="submit" class="btn btn-success btn-block"><i class="fas fa-upload"></i> Upload</button>
        </form>
    <?php endif; ?>
</div>
<?php if (!empty($message) && strpos($message, 'doesn\'t match') !== false): ?>
    <div class="alert alert-warning mt-3">
        ⚠️ Please upload the original generated form.
        <br><a href="download_form.php" class="btn btn-sm btn-primary mt-2"><i class="fas fa-download"></i> Download Correct Form</a>
    </div>
<?php endif; ?>

<script>
    const fileInput = document.getElementById('scholarship_form');
    const previewBox = document.getElementById('file-preview');
    const form = document.getElementById('uploadForm');

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert("❌ File too large. Max allowed: 2MB");
                    fileInput.value = '';
                    previewBox.style.display = 'none';
                } else {
                    previewBox.innerText = `Selected: ${file.name}`;
                    previewBox.style.display = 'block';
                }
            }
        });

        // Confirm before uploading
        form.addEventListener("submit", function (e) {
            if (!fileInput.files.length) return;
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to upload this form: " + fileInput.files[0].name,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, upload it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }

    // Toast for upload result
    <?php if (!empty($message)): ?>
    Swal.fire({
        title: "Upload Status",
        html: <?= json_encode(strip_tags($message)) ?>,
        icon: <?= strpos($message, 'success') !== false ? '"success"' : (strpos($message, 'warning') !== false ? '"warning"' : '"error"') ?>,
        confirmButtonText: "OK"
    });
    <?php endif; ?>
</script>

</body>
</html>

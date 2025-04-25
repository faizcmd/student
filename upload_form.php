<?php
session_start();

$conn = new mysqli("localhost", "root", "root", "student_donation");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['scholarship_form'])) {
    $upload_folder = "uploads/";
    $original_filename = basename($_FILES["scholarship_form"]["name"]);
    $filename = time() . "_" . preg_replace("/[^A-Za-z0-9_.]/", "_", $original_filename);
    $target_file = $upload_folder . $filename;

    if (mime_content_type($_FILES["scholarship_form"]["tmp_name"]) === 'application/pdf') {
        if (!file_exists($upload_folder)) {
            mkdir($upload_folder, 0777, true);
        }

        if (move_uploaded_file($_FILES["scholarship_form"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE students SET form_path = ? WHERE id = ?");
            $stmt->bind_param("si", $target_file, $_SESSION['user_id']);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>🎉 Form uploaded successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>❌ Database error: " . htmlspecialchars($stmt->error) . "</div>";
            }
            $stmt->close();
        } else {
            $message = "<div class='alert alert-danger'>⚠️ Upload failed. Try again.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Only PDF files are allowed.</div>";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Scholarship Form</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { margin-top: 40px; max-width: 600px; }
    </style>
</head>
<body>

<div class="container">
    <h1 class="mb-4 text-primary text-center">📤 Upload Stamped Form</h1>

    <div class="alert alert-info">Only upload <strong>PDF</strong> of your signed scholarship form. Max 2MB.</div>

    <?php if (!empty($message)): ?>
        <div class="mb-3"><?= $message ?></div>
    <?php endif; ?>

    <form action="upload_form.php" method="POST" enctype="multipart/form-data" class="shadow p-4 bg-white rounded">
        <div class="form-group">
            <label for="scholarship_form"><strong>Select PDF File:</strong></label>
            <input type="file" name="scholarship_form" id="scholarship_form" class="form-control-file" required accept="application/pdf">
            <div id="file-preview" class="text-success font-weight-bold mt-2" style="display:none;"></div>
        </div>
        <button type="submit" class="btn btn-success btn-block"><i class="fas fa-upload"></i> Upload</button>
    </form>

    <div class="nav-links text-center mt-4">
        <a href="register.php" class="btn btn-outline-secondary btn-sm">🔙 Register</a>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">🚪 Logout</a>
        <a href="show_my_profile.php" class="btn btn-outline-info btn-sm">👤 View My Details</a>
        <a href="donor_view.php" class="btn btn-outline-primary btn-sm">💖 Donor View</a>
    </div>
</div>

<script>
    const fileInput = document.getElementById('scholarship_form');
    const previewBox = document.getElementById('file-preview');
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
</script>

</body>
</html>

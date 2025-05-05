<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

require('vendor/autoload.php'); // FPDI via Composer
require('db_connect.php'); // Include your database connection file

use setasign\Fpdi\Fpdi;

// === Step 1: Prepare values ===
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$sourceFile = 'form/helping_heart_organization_form.pdf';

if (!file_exists($sourceFile)) {
    die("Form not found.");
}

// === Step 2: Generate a unique filename ===
$timestamp = time();
$generatedFilename = "Scholarship_Form_User_{$userId}_{$timestamp}.pdf";
$savePath = __DIR__ . "/form/generated_forms/{$generatedFilename}";

// === Step 3: Create PDF with identifier using FPDI ===
$pdf = new Fpdi();
$pdf->AddPage();
$pdf->setSourceFile($sourceFile);
$templateId = $pdf->importPage(1);
$pdf->useTemplate($templateId);

// Set identifier (watermark or footer)
$pdf->SetFont('Helvetica', '', 10);
$pdf->SetTextColor(150, 150, 150);
$identifierText = "Generated for ID: $userId | $userName | " . date("Y-m-d H:i:s");
$pdf->SetXY(10, -15); // Near bottom
$pdf->Write(5, $identifierText);

// === Step 4: Save PDF to server ===
$pdf->Output('F', $savePath); // Save to server

// === Step 5: Compute SHA-256 hash of the generated PDF ===
$generatedHash = hash_file('sha256', $savePath);

// === Step 6: Save filename and hash to database ===
$stmt = $conn->prepare("UPDATE students SET downloaded_form_name = ?, generated_form_hash = ? WHERE id = ?");
$stmt->bind_param("ssi", $generatedFilename, $generatedHash, $userId);
$stmt->execute();
$stmt->close();

// === Step 7: Send file to user for download ===
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $generatedFilename . '"');
readfile($savePath);
exit();
?>

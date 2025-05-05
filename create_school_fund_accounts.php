<?php
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'db_connect.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

use Razorpay\Api\Api;

// Initialize Razorpay
$api = new Api($keyId, $keySecret);

// ✅ Step 1: Get the most recently added school (where fund account is not yet created)
$schoolQuery = $conn->query("SELECT * FROM schools WHERE contact_id IS NULL AND fund_account_id IS NULL ORDER BY id DESC LIMIT 1");

if ($schoolQuery->num_rows === 0) {
    die("❌ No new school found that requires a fund account.");
}

$school = $schoolQuery->fetch_assoc();
$school_id = $school['id'];

// ✅ Step 2: Sanitize account name to avoid Razorpay error
$sanitized_account_name = preg_replace("/[^a-zA-Z0-9 .'-]/", "", $school['account_name']);

try {
    // ✅ Step 3: Create Razorpay Contact
    $contactData = [
        'name' => $sanitized_account_name,
        'type' => 'vendor',
        'reference_id' => 'school_' . $school_id,
        'notes' => [
            'school_name' => $school['school_name']
        ]
    ];

    $contact = $api->request->request('POST', '/contacts', $contactData);
    $contact_id = $contact['id'];

    // ✅ Step 4: Create Razorpay Fund Account
    $fundAccountData = [
        'contact_id' => $contact_id,
        'account_type' => 'vpa',
        'vpa' => [
            'address' => $school['upi_id']
        ]
    ];

    $fundAccount = $api->request->request('POST', '/fund_accounts', $fundAccountData);
    $fund_account_id = $fundAccount['id'];

    // ✅ Step 5: Update DB
    $update = $conn->prepare("UPDATE schools SET contact_id = ?, fund_account_id = ? WHERE id = ?");
    $update->bind_param("ssi", $contact_id, $fund_account_id, $school_id);
    $update->execute();

    echo "✅ Fund account created and saved for school: " . $school['school_name'];

} catch (Exception $e) {
    echo "❌ Razorpay Error: " . $e->getMessage();
}
?>

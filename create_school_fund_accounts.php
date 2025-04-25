<?php
require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'db_connect.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

use Razorpay\Api\Api;

$api = new Api($keyId, $keySecret);

// 1. Fetch school details
$school_id = 2;

$schoolQuery = $conn->prepare("SELECT * FROM schools WHERE id = ?");
$schoolQuery->bind_param("i", $school_id);
$schoolQuery->execute();
$result = $schoolQuery->get_result();
$school = $result->fetch_assoc();

if (!$school) {
    die("School not found.");
}

// 2. Create Contact
try {
    $contactData = [
        'name' => $school['account_name'],
        'type' => 'vendor',
        'reference_id' => 'school_' . $school_id,
        'notes' => [
            'school_name' => $school['school_name']
        ]
    ];

    $contact = $api->request->request('POST', '/contacts', $contactData);
    $contact_id = $contact['id'];

    // 3. Create Fund Account
    $fundAccountData = [
        'contact_id' => $contact_id,
        'account_type' => 'vpa',
        'vpa' => [
            'address' => $school['upi_id']
        ]
    ];

    $fundAccount = $api->request->request('POST', '/fund_accounts', $fundAccountData);
    $fund_account_id = $fundAccount['id'];

    // 4. Save to DB
    $update = $conn->prepare("UPDATE schools SET contact_id = ?, fund_account_id = ? WHERE id = ?");
    $update->bind_param("ssi", $contact_id, $fund_account_id, $school_id);
    $update->execute();

    echo "✅ Fund account created and saved for school: " . $school['school_name'];

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>

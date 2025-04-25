<?php
require_once 'config.php'; // Razorpay keys
require_once 'db_connect.php'; // Database connection
require_once 'vendor/autoload.php'; // Razorpay SDK

use Razorpay\Api\Api;

$api = new Api($keyId, $keySecret);

// 1. Get all schools where fund_account_id is NULL
$query = $conn->query("SELECT * FROM schools WHERE fund_account_id IS NULL");

while ($school = $query->fetch_assoc()) {
    $school_id = $school['id'];
    $name = $school['account_name'];
    $email = 'school' . $school_id . '@eduhelp.com'; // Dummy email for contact
    $contact = '99999999' . $school_id; // Dummy phone number

    try {
        // 2. Create Contact in Razorpay
        $contactResponse = $api->contact->create([
            'name' => $name,
            'email' => $email,
            'contact' => $contact,
            'type' => 'vendor',
            'reference_id' => 'school_' . $school_id,
            'notes' => ['purpose' => 'EduHelp School']
        ]);

        $contact_id = $contactResponse['id'];

        // 3. Create Fund Account (Using UPI or Bank)
        $fund_account = null;

        if (!empty($school['upi_id'])) {
            // UPI based fund account
            $fund_account = $api->fund_account->create([
                'contact_id' => $contact_id,
                'account_type' => 'vpa',
                'vpa' => ['address' => $school['upi_id']],
            ]);
        } else {
            // Bank based fund account
            $fund_account = $api->fund_account->create([
                'contact_id' => $contact_id,
                'account_type' => 'bank_account',
                'bank_account' => [
                    'name' => $school['account_name'],
                    'ifsc' => $school['ifsc_code'],
                    'account_number' => $school['account_number']
                ]
            ]);
        }

        $fund_account_id = $fund_account['id'];

        // 4. Save to DB
        $update = $conn->prepare("UPDATE schools SET contact_id = ?, fund_account_id = ? WHERE id = ?");
        $update->bind_param("ssi", $contact_id, $fund_account_id, $school_id);
        $update->execute();

        echo "✅ Fund account created for school ID $school_id<br>";

    } catch (Exception $e) {
        echo "❌ Error for school ID $school_id: " . $e->getMessage() . "<br>";
    }
}
?>

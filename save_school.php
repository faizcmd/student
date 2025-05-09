<?php
require_once 'db_connect.php';
require_once 'config.php';
require_once 'vendor/autoload.php';

use Razorpay\Api\Api;

// Ensure POST data exists
if (isset($_POST['school_name'], $_POST['account_name'], $_POST['account_number'], $_POST['ifsc_code'], $_POST['bank_name'], $_POST['upi_name'], $_POST['upi_id'])) {

    // Get form data
    $school_name = $_POST['school_name'];
    $account_name = $_POST['account_name'];
    $account_number = $_POST['account_number'];
    $ifsc_code = $_POST['ifsc_code'];
    $bank_name = $_POST['bank_name'];
    $upi_name = $_POST['upi_name'];
    $upi_id = $_POST['upi_id'];

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO schools (school_name, account_name, account_number, ifsc_code, bank_name, upi_name, upi_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $school_name, $account_name, $account_number, $ifsc_code, $bank_name, $upi_name, $upi_id);

    if ($stmt->execute()) {
        $school_id = $conn->insert_id; // Get the new ID from the last inserted record

        // Razorpay Fund Account Creation
        if (!empty($upi_id)) {
            try {
                $api = new Api($keyId, $keySecret); // Initialize Razorpay API

                // Create contact using the correct API endpoint
                $contactData = [
                    'name' => $account_name,
                    'type' => 'vendor',
                    'reference_id' => 'school_' . $school_id,
                    'notes' => [
                        'school_name' => $school_name
                    ]
                ];

                $contact = $api->request->request('POST', '/contacts', $contactData);
                $contact_id = $contact['id'];

                // Create a Razorpay Fund Account
                $fundAccountData = [
                    'contact_id' => $contact_id,
                    'account_type' => 'vpa',
                    'vpa' => [
                        'address' => $upi_id
                    ]
                ];

                $fundAccount = $api->request->request('POST', '/fund_accounts', $fundAccountData);
                $fund_account_id = $fundAccount['id'];

                // Update the DB with Razorpay contact and fund account IDs
                $update = $conn->prepare("UPDATE schools SET contact_id = ?, fund_account_id = ? WHERE id = ?");
                $update->bind_param("ssi", $contact_id, $fund_account_id, $school_id);

                if ($update->execute()) {
                    echo "✅ School added and Razorpay fund account created.";
                } else {
                    echo "❌ Failed to update Razorpay details in the database.";
                }
            } catch (Exception $e) {
                echo "❌ Razorpay Error: " . $e->getMessage();
            }
        } else {
            echo "School added, but UPI ID missing so Razorpay not created.";
        }
    } else {
        echo "❌ Failed to add school to the database.";
    }

    $stmt->close(); // Close the statement
} else {
    echo "❌ Missing required form data.";
}

$conn->close(); // Close the database connection
?>

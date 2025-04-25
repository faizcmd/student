<?php
function sendSMSOTP($mobile, $otp) {
    $apiKey     = '446297A9pI57XyCx67f6427fP1'; // Your MSG91 Auth Key
    $templateId = '67f6477cd6fc055eb51e1dc5';  // Your MSG91 Template ID with {{otp}}
    $senderId   = 'EDUHPY';                    // Your 6-char approved Sender ID

    $payload = [
        'template_id' => $templateId,
        'short_url'   => 0,
        'recipients'  => [
            [
                'mobiles' => '91' . $mobile,
                'otp'     => $otp // Match this to {{otp}} in your template
            ]
        ]
    ];

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://control.msg91.com/api/v5/flow/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "authkey: $apiKey",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $response = curl_exec($curl);
    $error    = curl_error($curl);
    curl_close($curl);

    // ✅ Log response to file
    $logMessage = "[" . date("Y-m-d H:i:s") . "]\n";
    $logMessage .= "To: $mobile\nOTP: $otp\n";
    $logMessage .= "Response: $response\n";
    if ($error) {
        $logMessage .= "CURL Error: $error\n";
    }
    $logMessage .= "------------------------\n";

    file_put_contents("msg91_sms_log.txt", $logMessage, FILE_APPEND);

    if ($error) {
        error_log("MSG91 SMS Error: $error");
        return false;
    }

    return true;
}
?>

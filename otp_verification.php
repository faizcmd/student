<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa; /* Light gray background */
        }
        .container {
            margin-top: 40px;
            max-width: 400px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="mb-4">Verify OTP</h2>
    
    <form action="verify_otp.php" method="POST">
        <div class="form-group">
            <label for="otp_email">Email OTP</label>
            <input type="text" name="otp_email" id="otp_email" class="form-control" placeholder="Enter Email OTP" required>
        </div>
        <div class="form-group">
            <label for="otp_mobile">Mobile OTP</label>
            <input type="text" name="otp_mobile" id="otp_mobile" class="form-control" placeholder="Enter Mobile OTP" required>
        </div>
        <button type="submit" class="btn btn-success btn-block">Verify</button>
        
        <div class="text-center mt-3">
            <button type="button" class="btn btn-link" onclick="resendOTP()">Resend OTP</button>
            <div id="otpMsg" class="text-success small mt-2"></div>
        </div>
    </form>
</div>

<script>
    let cooldown = false;
    let timer;

    function resendOTP() {
        if (cooldown) return;

        cooldown = true;

        const button = document.querySelector('.btn-link');
        const otpMsg = document.getElementById('otpMsg');
        let secondsLeft = 60;

        button.disabled = true;
        otpMsg.innerText = "Please wait 60 seconds before retrying.";

        timer = setInterval(() => {
            secondsLeft--;
            otpMsg.innerText = `Please wait ${secondsLeft} seconds before retrying.`;
            if (secondsLeft <= 0) {
                clearInterval(timer);
                button.disabled = false;
                cooldown = false;
                otpMsg.innerText = "You can now resend OTP if you haven't received it.";
            }
        }, 1000);

        // Make API call to resend OTP
        fetch('resend_otp.php', {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            console.log(data.message); // For debugging/log
        })
        .catch(err => {
            otpMsg.innerText = 'Something went wrong while sending OTP.';
        });
    }
</script>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

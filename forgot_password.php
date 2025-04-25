<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 500px;">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Forgot Password</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="send_reset_link.php">
                <div class="form-group">
                    <label for="email">Enter your email address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success btn-block">Send Reset Link</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>

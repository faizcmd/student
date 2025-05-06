<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
/* .container{
    margin-top: 70px;
} */




    </style>
</head>
<body  >
    <!-- Top Navigation Bar -->
<div class="top-nav" >
    <div class="title">🎓 EduHelp</div>
    <div class="nav-links">
       
        <!-- <a href="donor_view.php">🙏 Donor View</a> -->
       <a href="Login.php">🔐 Login</a>
        </div>
</div>

<div class="container" style="max-width: 500px; margin-top: 80px;">

    <div class="card shadow" >
        <!-- <div class="card-header bg-primary text-white" style="margin-top: 10px;">
            <h4 class="mb-0">Forgot Password</h4>
        </div> -->
        <div class="card-body" >
            <form method="POST" action="send_reset_link.php">
                <div class="form-group">
                    <label for="email">Enter your email address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success btn-block">Forgot password Send Reset Link</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>

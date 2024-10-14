<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PetCare System</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="video-background">
        <video autoplay muted loop id="background-video">
            <source src="images/video1.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
    <div class="login-container">
        <div class="login-box">
            <h2>Login</h2>
            <form action="login_process.php" method="POST">
                <div class="input-group">
                    <input type="text" name="email" required>
                    <label>E-mail</label>
                </div>
                <div class="input-group">
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>
                <button type="submit" name="sub" class="btn">Login</button>
            </form>
            <p class="register-link">Don't have an account? <a href="register_user.php">Register here</a></p>
        </div>
    </div>
</body>
</html>
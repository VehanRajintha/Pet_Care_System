<?php
session_start();
include 'includes/db_connect.php';

// Check if the admin is logged in
if (isset($_POST['sub'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $q  = $db->prepare("SELECT * FROM admins WHERE username = :username AND password = :password");
    $q->bindParam(':username', $username);
    $q->bindParam(':password', $password);
    $q->execute();
    $res = $q->fetchAll(PDO::FETCH_OBJ);
    if ($res) {
        $_SESSION['username'] = $username;
        header("Location:admin_dashboard.php");
    } else {
        echo "<script>alert('Username or Password is incorrect')</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="css/admin_login.css">
</head>
<body>
    <video class="background-video" autoplay muted loop>
        <source src="images/video2.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <div class="container">
        <div class="login-box">
            <div class="login-form admin-form">
                <h1>Admin Login</h1>
                <form action="admin_login.php" method="post">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" name="sub">Login</button>
                </form>
                <button id="manager-login-btn">Login as Manager</button>
            </div>
            <div class="login-form manager-form">
                <h1>Manager Login</h1>
                <form action="manager_login.php" method="post">
                    <div class="form-group">
                        <label for="manager-username">Username:</label>
                        <input type="text" id="manager-username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="manager-password">Password:</label>
                        <input type="password" id="manager-password" name="password" required>
                    </div>
                    <button type="submit" name="sub">Login</button>
                </form>
                <button id="admin-login-btn">Back to Admin Login</button>
            </div>
        </div>
    </div>
    <script src="js/admin_login.js"></script>
</body>
</html>
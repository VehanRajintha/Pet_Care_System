<?php
session_start();
include('includes/db_connect.php'); // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $username = $_POST['username'];
    $password = $_POST['password']; // Store the password as plain text
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Insert data into the database
    $sql = "INSERT INTO users (username, password, email, phone, address) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $username, $password, $email, $phone, $address);

    if ($stmt->execute()) {
        // Store user information in session and redirect to petregistration.php
        $_SESSION['username'] = $username;
        header("Location: register_pet.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration - PetCare System</title>
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
    <div class="video-background">
        <video autoplay muted loop id="background-video">
            <source src="images/video1.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        </div>
    <div class="registration-container">
        <div class="registration-box">
            <h2>User Registration</h2>
            <form action="register_user.php" method="post">
                <div class="form-grid">
                    <div class="input-group">
                        <input type="text" id="username" name="username" required>
                        <label for="username">Username</label>
                    </div>
                    <div class="input-group">
                        <input type="password" id="password" name="password" required>
                        <label for="password">Password</label>
                    </div>
                    <div class="input-group">
                        <input type="email" id="email" name="email" required>
                        <label for="email">Email</label>
                    </div>
                    <div class="input-group">
                        <input type="text" id="phone" name="phone" required>
                        <label for="phone">Phone</label>
                    </div>
                    <div class="input-group full-width">
                        <input type="text" id="address" name="address" required>
                        <label for="address">Address</label>
                    </div>
                </div>
                <button type="submit" class="btn">Register</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php
session_start();
include('includes/db_connect.php'); // Include your database connection file

if (isset($_POST['sub'])) {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Use prepared statements to prevent SQL injection
        $q = $db->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
        $q->bindParam(':email', $email);
        $q->bindParam(':password', $password);
        $q->execute();

        $res = $q->fetch(PDO::FETCH_OBJ);
        if ($res) {
            // Set the session with the correct username variable
            $_SESSION['email'] = $email;
            header("Location: user_dashboard.php");
            exit(); // Always exit after header redirection to stop the script execution
        } else {
            echo "<script>
                    alert('Username or Password is incorrect');
                    window.location.href = 'user_login.php';
                </script>";
        }
    } else {
        echo "<script>
        alert('Please provide both username and password');
        window.location.href = 'user_login.php';
        </script>";
    }
}
?>
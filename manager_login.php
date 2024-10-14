<?php
session_start();
include 'includes/db_connect.php';

// Check if the manager is logged in
if (isset($_POST['sub'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $q  = $db->prepare("SELECT * FROM managers WHERE username = :username AND password = :password");
    $q->bindParam(':username', $username);
    $q->bindParam(':password', $password);
    $q->execute();
    $res = $q->fetchAll(PDO::FETCH_OBJ);
    if ($res) {
        $_SESSION['username'] = $username;
        header("Location:manager_dashboard.php");
    } else {
        echo "<script>
        alert('Username or Password is incorrect');
        window.location.href = 'admin_login.php';
        </script>";
    }
}
?>
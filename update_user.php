<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $update_sql = "UPDATE users SET username = :username, phone = :phone, address = :address WHERE email = :email";
    $update_stmt = $db->prepare($update_sql);
    $update_stmt->bindParam(':username', $username);
    $update_stmt->bindParam(':phone', $phone);
    $update_stmt->bindParam(':address', $address);
    $update_stmt->bindParam(':email', $email);

    if ($update_stmt->execute()) {
        $_SESSION['success'] = "User information updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update user information.";
    }

    header("Location: user_dashboard.php");
    exit();
}
?>
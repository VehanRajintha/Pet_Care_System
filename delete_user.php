<?php
session_start();
include 'includes/db_connect.php';

// Handle the deletion of the user
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Delete user from the database
    try {
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        echo "User deleted successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>
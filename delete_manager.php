<?php
session_start();
include 'includes/db_connect.php';

// Handle the deletion of the manager
if (isset($_GET['id'])) {
    $manager_id = $_GET['id'];

    // Delete manager from the database
    try {
        $stmt = $db->prepare("DELETE FROM managers WHERE id = :id");
        $stmt->bindParam(':id', $manager_id);
        $stmt->execute();
        echo "Manager deleted successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>
<?php
session_start();
include 'includes/db_connect.php';

// Handle the deletion of the pet
if (isset($_GET['id'])) {
    $pet_id = $_GET['id'];

    // Delete pet from the database
    try {
        $stmt = $db->prepare("DELETE FROM pets WHERE id = :id");
        $stmt->bindParam(':id', $pet_id);
        $stmt->execute();
        echo "Pet deleted successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>
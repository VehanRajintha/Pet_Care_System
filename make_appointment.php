<?php
session_start();
include 'includes/db_connect.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id = $_POST['pet_id'] ?? null;
    $appointment_date = $_POST['appointment_date'] ?? null;
    $description = $_POST['description'] ?? null;
    $pet_name = $_POST['pet_name'] ?? null;

    // Validate form data
    if ($pet_id && $appointment_date && $description && $pet_name) {
        // Insert appointment into the database
        $q = $db->prepare("INSERT INTO appointments (pet_id, appointment_date, description, pet_name) VALUES (:pet_id, :appointment_date, :description, :pet_name)");
        $q->bindParam(':pet_id', $pet_id);
        $q->bindParam(':appointment_date', $appointment_date);
        $q->bindParam(':description', $description);
        $q->bindParam(':pet_name', $pet_name);
        $q->execute();

        // Redirect to user dashboard
        header("Location: user_dashboard.php");
        exit();
    } else {
        echo "All fields are required.";
    }
}
?>
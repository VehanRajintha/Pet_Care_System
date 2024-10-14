<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $petId = $_POST['id'];
    include 'includes/db_connect.php';

    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    // Delete pet from database
    $stmt = $conn->prepare('DELETE FROM pets WHERE id = ?');
    $stmt->bind_param('i', $petId);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();

    echo json_encode(['success' => $success]);
}
?>
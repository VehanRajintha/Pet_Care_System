<?php
session_start();
include 'includes/db_connect.php';

// Fetch the manager's details
if (isset($_GET['id'])) {
    $manager_id = $_GET['id'];
    $stmt = $db->prepare("SELECT * FROM managers WHERE id = :id");
    $stmt->bindParam(':id', $manager_id);
    $stmt->execute();
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle form submission for updating manager details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_manager'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Do not hash the password

    // Update manager details in the database
    try {
        $stmt = $db->prepare("UPDATE managers SET username = :username, email = :email, password = :password WHERE id = :id");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        echo "Manager details updated successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Manager</title>
    <link rel="stylesheet" href="css/admin_dashboard.css">
</head>
<body>
    <h2>Edit Manager</h2>
    <form method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($manager['id']); ?>">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($manager['username']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($manager['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($manager['password']); ?>" required>
        </div>
        <button type="submit" name="update_manager">Save Changes</button>
    </form>
</body>
</html>
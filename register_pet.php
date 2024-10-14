<?php
session_start();
include('includes/db_connect.php'); // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit();
}

// Retrieve the user's email from the database
$username = $_SESSION['username'];
$sql = "SELECT email FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();

$registration_success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $petname = $_POST['petname'];
    $species = $_POST['species'];
    $breed = $_POST['breed'];
    $age = $_POST['age'];
    $owner = $_SESSION['username'];
    $email = $_POST['email'];

    // Handle pet picture upload securely
    $petpicture = null;
    if (isset($_FILES['petpicture']) && $_FILES['petpicture']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['petpicture']['type'], $allowed_types)) {
            $target_dir = 'uploads/';
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $petpicture = basename($_FILES['petpicture']['name']);
            move_uploaded_file($_FILES['petpicture']['tmp_name'], $target_dir . $petpicture);
        } else {
            die("Unsupported file type.");
        }
    }

    // Insert pet data into the database
    $sql = "INSERT INTO pets (petname, species, breed, age, owner, email, petpicture) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $petname, $species, $breed, $age, $owner, $email, $petpicture);
    if ($stmt->execute()) {
        $registration_success = true;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Registration - PetCare System</title>
    <link rel="stylesheet" href="css/register.css">
    <style>
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            z-index: 1000;
            animation: fadeIn 0.5s ease-in-out;
        }

        .popup h2 {
            margin-bottom: 20px;
        }

        .popup .btn {
            margin: 10px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>
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
            <h2>Pet Registration</h2>
            <form action="register_pet.php" method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="input-group">
                        <input type="text" id="petname" name="petname" required>
                        <label for="petname">Pet Name</label>
                    </div>
                    <div class="input-group" style="position: relative; width: 100%; margin-bottom: 15px;">
                        <select id="species" name="species" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background: transparent; font-size: 16px; color: #333;">
                            <option value="" disabled selected>Select Category</option>
                            <option value="dog">Dog</option>
                            <option value="cat">Cat</option>
                            <option value="bird">Bird</option>
                            <option value="fish">Fish</option>
                            <option value="reptile">Reptile</option>
                            <option value="small_mammal">Small Mammal</option>
                        </select>
                    <label for="species"></label>
                </div>
                </div>
                <div class="form-row">
                    <div class="input-group">
                        <input type="text" id="breed" name="breed" required>
                        <label for="breed">Breed</label>
                    </div>
                    <div class="input-group">
                        <input type="number" id="age" name="age" required>
                        <label for="age">Age</label>
                    </div>
                </div>
                <div class="form-row">
                    <div class="input-group" style="position: relative; display: flex; flex-direction: column;">
                        <input type="file" id="petpicture" name="petpicture" required >
                        <label for="petpicture" style="background-color: #007bff; color: white; padding: 7px 15px; border-radius: 5px; cursor: pointer; text-align: center; transition: background-color 0.3s ease;">Pet Picture</label>
                    </div>
                </div>
                <div class="form-row">
                    <div class="input-group">
                        <input type="email" id="email" name="email" value="<?php echo $email; ?>" readonly>
                        <label for="email"></label>
                    </div>
                </div>
                <button type="submit" class="btn">Register Pet</button>
            </form>
        </div>
    </div>

    <div class="overlay"></div>
    <div class="popup" id="successPopup">
        <h2>Pet Registered Successfully!</h2>
        <button class="btn" onclick="addAnotherPet()">Register Another Pet</button>
        <button class="btn" onclick="goToAccount()">Go to Account</button>
    </div>

    <script>
        function showPopup() {
            document.querySelector('.overlay').style.display = 'block';
            document.getElementById('successPopup').style.display = 'block';
        }

        function addAnotherPet() {
            window.location.href = 'register_pet.php';
        }

        function goToAccount() {
            window.location.href = 'user_login.php';
        }

        <?php if ($registration_success): ?>
            showPopup();
        <?php endif; ?>
    </script>
</body>
</html>
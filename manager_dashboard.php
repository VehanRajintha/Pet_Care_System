<?php
session_start();
include 'includes/db_connect.php';

// Check if the admin is logged in
if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
    exit();
}

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit();
}



// Handle the update
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $username = $_POST['username'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    $petname = $_POST['petname'] ?? null;
    $age = $_POST['age'] ?? null;
    $petpicture = null;

    // Handle pet picture upload securely
    if (isset($_FILES['petpicture']) && $_FILES['petpicture']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['petpicture']['type'], $allowed_types)) {
            $petpicture = basename($_FILES['petpicture']['name']);
            move_uploaded_file($_FILES['petpicture']['tmp_name'], 'uploads/' . $petpicture);
        } else {
            die("Unsupported file type.");
        }
    }


    if ($username && $email && $password) {
        $q = $db->prepare("UPDATE users SET username = :username, email = :email, password = :password WHERE id = :id");
        $q->bindParam(':id', $id);
        $q->bindParam(':username', $username);
        $q->bindParam(':email', $email);
        $q->bindParam(':password', $password);
        $q->execute();
    } elseif ($petname && $age) {
        if ($petpicture) {
            // Update with petpicture
            $q = $db->prepare("UPDATE pets SET petname = :petname, petpicture = :petpicture, age = :age WHERE id = :id");
            $q->bindParam(':petpicture', $petpicture);
        } else {
            // Update without petpicture
            $q = $db->prepare("UPDATE pets SET petname = :petname, age = :age WHERE id = :id");
        }
        $q->bindParam(':id', $id);
        $q->bindParam(':petname', $petname);
        $q->bindParam(':age', $age);
        $q->execute();
    }


    header("Location: manager_dashboard.php");
    exit();
}


// Fetch data from the database
$users = $db->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
$pets = $db->query("SELECT * FROM pets")->fetchAll(PDO::FETCH_ASSOC);
$managers = $db->query("SELECT * FROM managers")->fetchAll(PDO::FETCH_ASSOC);

// Fetch the logged-in manager's data
$manager_username = $_SESSION['username'];
$manager_sql = "SELECT * FROM managers WHERE username = :username";
$manager_stmt = $db->prepare($manager_sql);
$manager_stmt->bindParam(':username', $manager_username);
$manager_stmt->execute();
$manager = $manager_stmt->fetch(PDO::FETCH_ASSOC);

// Handle the manager update
if (isset($_POST['update_manager'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $update_sql = "UPDATE managers SET username = :username, email = :email, password = :password WHERE id = :id";
    $update_stmt = $db->prepare($update_sql);
    $update_stmt->bindParam(':id', $id);
    $update_stmt->bindParam(':username', $username);
    $update_stmt->bindParam(':email', $email);
    $update_stmt->bindParam(':password', $password);

    if ($update_stmt->execute()) {
        // Update session username to the new manager username
        $_SESSION['username'] = $username;
        echo "Manager updated successfully.";
        // Optionally, you can redirect to the same page to see the updated data
        header("Location: manager_dashboard.php");
        exit();
    } else {
        die("Error updating manager.");
    }
}
// Fetch appointments
$appointments = [];
try {
    $stmt = $db->prepare("SELECT * FROM appointments");
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <link rel="stylesheet" href="css/manager.css">
</head>
<body>
    <form method="post" style="text-align: center;">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>

    <video class="background-video" autoplay muted loop>
        <source src="images/video2.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <div class="container">
        <h1>Manager Dashboard</h1>
        <div class="header">
            <div onclick="showSection('user-section')">User Section</div>
            <div onclick="showSection('pet-section')">Pet Section</div>
            <div onclick="showSection('admin-section')">Manager Section</div>
            <div onclick="showSection('appointments-section')">Appointments</div>
        </div>

        <!-- User Section -->
        <div id="user-section" class="section active">
            <h2>Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td><?php echo htmlspecialchars($user['address']); ?></td>
                        <td>
                        <span class="edit-btn" data-id="<?php echo $user['id']; ?>" data-type="user" data-username="<?php echo htmlspecialchars($user['username']); ?>" data-email="<?php echo htmlspecialchars($user['email']); ?>" data-password="<?php echo htmlspecialchars($user['password']); ?>">Edit</span>
                        <span class="delete-btn" data-id="<?php echo $user['id']; ?>" data-type="user">Delete</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pet Section -->
        <div id="pet-section" class="section">
            <h2>Pets</h2>
            <table>
                <thead>
                    <tr>
                        <th>Picture</th>
                        <th>Pet Name</th>
                        <th>Owner</th>
                        <th>Owner Email</th>
                        <th>Species</th>
                        <th>Age</th>
                        <th>Breed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pets as $pet): ?>
                    <tr>
                    <td><img src="uploads/<?php echo htmlspecialchars($pet['petpicture']); ?>" alt="Pet Picture" class="round-image" width="50"></td>
                        <td><?php echo htmlspecialchars($pet['petname']); ?></td>
                        <td><?php echo htmlspecialchars($pet['owner']); ?></td>
                        <td><?php echo htmlspecialchars($pet['email']); ?></td>
                        <td><?php echo htmlspecialchars($pet['species']); ?></td>
                        <td><?php echo htmlspecialchars($pet['age']); ?></td>
                        <td><?php echo htmlspecialchars($pet['breed']); ?></td>
                        <td>
                        <span class="edit-btn" data-id="<?php echo $pet['id']; ?>" data-type="pet" data-petname="<?php echo htmlspecialchars($pet['petname']); ?>" data-petpicture="<?php echo htmlspecialchars($pet['petpicture']); ?>" data-age="<?php echo htmlspecialchars($pet['age']); ?>">Edit</span>
                        <span class="delete-btn" data-id="<?php echo $pet['id']; ?>" data-type="pet">Delete</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

<!-- Manager Section -->
<div id="admin-section" class="section">
    <h2>Manager Section</h2>
    <div class="glass-effect manager-section">
        <div class="left-column">
            <img src="images/profile.png" alt="Profile Picture" class="profile-image">
        </div>
        <div class="right-column">
            <form id="managerForm" method="post" enctype="multipart/form-data">
                <input type="hidden" id="managerId" name="id" value="<?php echo htmlspecialchars($manager['id']); ?>">
                <div class="form-group">
                    <label for="managerUsername">Username:</label>
                    <input type="text" id="managerUsername" name="username" value="<?php echo htmlspecialchars($manager['username']); ?>">
                </div>
                <div class="form-group">
                    <label for="managerEmail">Email:</label>
                    <input type="email" id="managerEmail" name="email" value="<?php echo htmlspecialchars($manager['email']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="managerPassword">Password:</label>
                    <input type="password" id="managerPassword" name="password" value="<?php echo htmlspecialchars($manager['password']); ?>">
                </div>
                <button type="submit" name="update_manager">Save Changes</button>
            </form>
        </div>
    </div>
</div>

 <!-- Appointments Section -->
 <div id="appointments-section" class="section">
            <h2>Appointments</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pet Name</th>
                        <th>Appointment Date</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                        <td><?php echo htmlspecialchars($appointment['id']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['pet_name']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['description']); ?></td>
                            <td><span class="status-btn <?php echo $appointment['status'] == 'Done' ? 'done' : 'scheduled'; ?>"><?php echo htmlspecialchars($appointment['status']); ?></span></td>
                            <td>
                                <button class="mark-done-btn" data-id="<?php echo $appointment['id']; ?>">Mark as Done</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Details</h2>
            <form id="editForm" method="post" enctype="multipart/form-data">
                <input type="hidden" id="editId" name="id">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div id="userFields">
                    <div class="form-group">
                        <label for="editUsername">Username:</label>
                        <input type="text" id="editUsername" name="username">
                    </div>
                    <div class="form-group">
                        <label for="editEmail">Email:</label>
                        <input type="email" id="editEmail" name="email">
                    </div>
                    <div class="form-group">
                        <label for="editPassword">Password:</label>
                        <input type="password" id="editPassword" name="password">
                    </div>
                </div>

                <div id="petFields">
                    <div class="form-group">
                        <label for="editPetName">Pet Name:</label>
                        <input type="text" id="editPetName" name="petname">
                    </div>
                    <div class="form-group">
                        <label for="editAge">Age:</label>
                        <input type="text" id="editAge" name="age">
                    </div>
                    <div class="form-group">
                        <label for="editPetPicture">Pet Picture:</label>
                        <input type="file" id="editPetPicture" name="petpicture">
                    </div>
                </div>

                <button type="submit" name="update">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content animated">
            <span class="close">&times;</span>
            <iframe src="https://lottie.host/embed/dc6f5014-25ef-43c5-b6c9-0810c943a05e/8dNQoGWKJk.json" frameborder="0" class="transparent-iframe"></iframe>
            <h2>Ask Admin First</h2>
            <button id="confirmDelete" class="btn">ok</button>
        </div>
    </div>


<script>

function showSection(sectionId) {
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => {
                section.classList.remove('active');
            });
            document.getElementById(sectionId).classList.add('active');
        }

// Get the modal
var modal = document.getElementById("editModal");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

// Function to open the modal and populate it with data
function openEditModal(id, type, data) {
    document.getElementById('editId').value = id;

    if (type === 'user') {
        document.getElementById('userFields').style.display = 'block';
        document.getElementById('petFields').style.display = 'none';
        document.getElementById('editUsername').value = data.username;
        document.getElementById('editEmail').value = data.email;
        document.getElementById('editPassword').value = data.password;
    } else if (type === 'pet') {
        document.getElementById('userFields').style.display = 'none';
        document.getElementById('petFields').style.display = 'block';
        document.getElementById('editPetName').value = data.petname;
        document.getElementById('editAge').value = data.age;
        // For file input, we cannot set the value directly due to security reasons
        // document.getElementById('editPetPicture').value = data.petpicture;
    }

    modal.style.display = "block";

}

// Function to open the delete modal
function openDeleteModal(id, type) {
    deleteModal.style.display = "block";
    document.getElementById('confirmDelete').onclick = function() {
        deleteModal.style.display = "none"; // Close the modal
    }
}

// Add event listeners to edit buttons
document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.dataset.id;
        const type = this.dataset.type;
        const data = {
            username: this.dataset.username,
            email: this.dataset.email,
            password: this.dataset.password,
            petname: this.dataset.petname,
            petpicture: this.dataset.petpicture,
            age: this.dataset.age
        };
        openEditModal(id, type, data);
    });
});

// Add event listeners to delete buttons
document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const type = this.dataset.type;
            openDeleteModal(id, type);
        });
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const headerDivs = document.querySelectorAll('.header div');

    headerDivs.forEach(div => {
        div.addEventListener('click', function() {
            // Remove active class from all divs
            headerDivs.forEach(d => d.classList.remove('active'));
            // Add active class to the clicked div
            this.classList.add('active');
        });
    });
});
function showSection(sectionId) {
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    document.getElementById(sectionId).classList.add('active');
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.mark-done-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const statusBtn = this.closest('tr').querySelector('.status-btn');

            // Send AJAX request to update status in the database
            fetch('update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id, status: 'Done' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusBtn.textContent = 'Done';
                    statusBtn.classList.remove('scheduled');
                    statusBtn.classList.add('done');
                } else {
                    alert('Failed to update status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });

    const headerDivs = document.querySelectorAll('.header div');

    headerDivs.forEach(div => {
        div.addEventListener('click', function() {
            // Remove active class from all divs
            headerDivs.forEach(d => d.classList.remove('active'));
            // Add active class to the clicked div
            this.classList.add('active');
        });
    });
});
</script>
</body>
</html>



   
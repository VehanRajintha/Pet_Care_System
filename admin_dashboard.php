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

// Handle form submission for adding a new manager
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_manager'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Do not hash the password

    // Insert new manager into the database
    try {
        $stmt = $db->prepare("INSERT INTO managers (username, email, password) VALUES (:username, :email, :password)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $_SESSION['message'] = " ";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
    }

    // Redirect to the same page to prevent form resubmission
    header("Location: admin_dashboard.php");
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

// Fetch the logged-in admin's data
// Fetch the logged-in admin's data
$admin_username = $_SESSION['username'];
$admin_sql = "SELECT * FROM admins WHERE username = :username";
$admin_stmt = $db->prepare($admin_sql);
$admin_stmt->bindParam(':username', $admin_username);
$admin_stmt->execute();
$admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);

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
        header("Location: admin_dashboard.php");
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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin.css">
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
        <h1>Admin Dashboard</h1>
        <div class="header">
            <div onclick="showSection('user-section')">User Section</div>
            <div onclick="showSection('pet-section')">Pet Section</div>
            <div onclick="showSection('admin-section')">Admin Section</div>
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

<!-- Admin Section -->
<div id="admin-section" class="section">
    <h2>Admin Section</h2>
    <div class="glass-effect admin-section">
        <div class="left-column">
            <img src="images/profile.png" alt="Profile Picture" class="profile-image">
        </div>
        <div class="right-column">
            <form id="adminForm" method="post" enctype="multipart/form-data">
                <input type="hidden" id="adminId" name="id" value="<?php echo htmlspecialchars($admin['id']); ?>">
                <div class="form-group">
                    <label for="adminUsername">Username:</label>
                    <input type="text" id="adminUsername" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>">
                </div>
                <div class="form-group">
                    <label for="adminEmail">Email:</label>
                    <input type="email" id="adminEmail" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="adminPassword">Password:</label>
                    <input type="password" id="adminPassword" name="password" value="<?php echo htmlspecialchars($admin['password']); ?>">
                </div>
                <button type="submit" name="update_admin">Save Changes</button>
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

 

    <button class="manage-managers-btn" onclick="toggleManagersOverlay()">Manage Managers</button>
    <div id="managers-overlay" class="overlay">
        <div class="overlay-content">
            <h2>Managers</h2>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($managers as $manager): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($manager['username']); ?></td>
                        <td><?php echo htmlspecialchars($manager['email']); ?></td>
                        <td><?php echo htmlspecialchars($manager['password']); ?></td>
                        <td>
                        <a href="javascript:void(0);" onclick="showEditManagerModal(<?php echo $manager['id']; ?>)">Edit</a>
                        <a href="javascript:void(0);" onclick="showDeleteManagerModal(<?php echo $manager['id']; ?>)">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button onclick="addManager()">Add Manager</button>
            <button onclick="toggleManagersOverlay()">Close</button>
        </div>
    </div>

    <!-- Modal Structure -->
    <div id="addManagerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Add New Manager</h2>
            <form id="addManagerForm" method="post">
                <div class="form-group">
                    <label for="newManagerUsername">Username:</label>
                    <input type="text" id="newManagerUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="newManagerEmail">Email:</label>
                    <input type="email" id="newManagerEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="newManagerPassword">Password:</label>
                    <input type="password" id="newManagerPassword" name="password" required>
                </div>
                <button type="submit" name="add_manager">Add Manager</button>
            </form>
        </div>
    </div>

<!-- Edit Manager Modal -->
<div id="editManagerModal" class="modal">
        <div class="modal-content">
        <span class="close" onclick="closeModal('editManagerModal')">&times;</span>
            <div id="editManagerContent"></div>
        </div>
    </div>

     <!-- Delete Manager Modal -->
     <div id="deleteManagerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('deleteManagerModal')">&times;</span>
            <h2>Are you sure you want to delete this manager?</h2>
            <button id="confirmDeleteBtn" style="background-color: red; color: white;">Yes, Delete</button>
            <button onclick="closeModal('deleteManagerModal')">Cancel</button>
        </div>
    </div>

    <!-- Delete Pet Modal -->
    <div id="deletePetModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('deletePetModal')">&times;</span>
            <h2>Are you sure you want to delete this pet?</h2>
            <button id="confirmDeletePetBtn"style="background-color: red; color: white;">Yes, Delete</button>
            <button onclick="closeModal('deletePetModal')">Cancel</button>
        </div>
    </div>
    <!-- Delete User Modal -->
    <div id="deleteUserModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('deleteUserModal')">&times;</span>
            <h2>Are you sure you want to delete this user?</h2>
            <button id="confirmDeleteUserBtn"style="background-color: red; color: white;">Yes, Delete</button>
            <button onclick="closeModal('deleteUserModal')">Cancel</button>
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
<script>
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => {
                section.classList.remove('active');
            });
            document.getElementById(sectionId).classList.add('active');
        }

        function toggleManagersOverlay() {
            const overlay = document.getElementById('managers-overlay');
            overlay.classList.toggle('active');
        }

        function addManager() {
            // Redirect to add_manager.php or open a form to add a new manager
            window.location.href = 'add_manager.php';
        }
    </script>
    <script>
        function addManager() {
    document.getElementById('addManagerModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('addManagerModal').style.display = 'none';
    document.getElementById('editManagerModal').style.display = 'none';
    document.getElementById('deleteManagerModal').style.display = 'none';
    document.getElementById('deletePetModal').style.display = 'none';
    document.getElementById('deleteUserModal').style.display = 'none';
}



// Close the modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('addManagerModal');
    const deleteManagerModal = document.getElementById('deleteManagerModal');
    const deletePetModal = document.getElementById('deletePetModal');
    const deleteUserModal = document.getElementById('deleteUserModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
    if (event.target == editModal) {
        editModal.style.display = 'none';
    }
    if (event.target == deleteModal) {
        deleteModal.style.display = 'none';
    }
}

// Show the edit manager modal and load content via AJAX
function showEditManagerModal(managerId) {
    const modal = document.getElementById('editManagerModal');
    const content = document.getElementById('editManagerContent');
    modal.style.display = 'block';

    // Load content via AJAX
    fetch(`edit_manager.php?id=${managerId}`)
        .then(response => response.text())
        .then(data => {
            content.innerHTML = data;
        })
        .catch(error => console.error('Error:', error));
}

// Show the delete manager modal and load content via AJAX
function showDeleteManagerModal(managerId) {
    const modal = document.getElementById('deleteManagerModal');
    const content = document.getElementById('deleteManagerContent');
    modal.style.display = 'block';

    // Load content via AJAX
    fetch(`delete_manager.php?id=${managerId}`)
        .then(response => response.text())
        .then(data => {
            content.innerHTML = data;
        })
        .catch(error => console.error('Error:', error));
}

// Show the delete manager modal
function showDeleteManagerModal(managerId) {
    managerIdToDelete = managerId;
    const modal = document.getElementById('deleteManagerModal');
    modal.style.display = 'block';
}

// Handle the delete confirmation
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (managerIdToDelete) {
        fetch(`delete_manager.php?id=${managerIdToDelete}`)
            .then(response => response.text())
            .then(data => {
                alert(data);
                closeModal('deleteManagerModal');
                location.reload(); // Reload the page to reflect changes
            })
            .catch(error => console.error('Error:', error));
    }
});

// Show the delete pet modal
function showDeletePetModal(petId) {
    petIdToDelete = petId;
    const modal = document.getElementById('deletePetModal');
    modal.style.display = 'block';
}

// Handle the delete confirmation for pet
document.getElementById('confirmDeletePetBtn').addEventListener('click', function() {
    if (petIdToDelete) {
        fetch(`delete_pet.php?id=${petIdToDelete}`)
            .then(response => response.text())
            .then(data => {
                alert(data);
                closeModal('deletePetModal');
                location.reload(); // Reload the page to reflect changes
            })
            .catch(error => console.error('Error:', error));
    }
});

// Attach event listeners to delete buttons
document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const type = this.getAttribute('data-type');
        if (type === 'pet') {
            showDeletePetModal(id);
        } else if (type === 'manager') {
            showDeleteManagerModal(id);
        } else if (type === 'user') {
            showDeleteUserModal(id);
        }
    });
});
// Show the delete user modal
function showDeleteUserModal(userId) {
    userIdToDelete = userId;
    const modal = document.getElementById('deleteUserModal');
    modal.style.display = 'block';
}

// Handle the delete confirmation for user
document.getElementById('confirmDeleteUserBtn').addEventListener('click', function() {
    if (userIdToDelete) {
        fetch(`delete_user.php?id=${userIdToDelete}`)
            .then(response => response.text())
            .then(data => {
                alert(data);
                closeModal('deleteUserModal');
                location.reload(); // Reload the page to reflect changes
            })
            .catch(error => console.error('Error:', error));
    }
});

    </script>
</body>
</html>



   
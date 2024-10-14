<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['email'])) {
    header("Location: user_login.php");
    exit();
}

$email = $_SESSION['email'];



// Fetch user data
$user_sql = "SELECT * FROM users WHERE email = :email";
$user_stmt = $db->prepare($user_sql);
$user_stmt->bindParam(':email', $email);
$user_stmt->execute();
$user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    echo "No user data found.";
    exit;
}

// Fetch pet data
$pet_sql = "SELECT * FROM pets WHERE email = :email";
$pet_stmt = $db->prepare($pet_sql);
$pet_stmt->bindParam(':email', $email);
$pet_stmt->execute();
$pets = $pet_stmt->fetchAll(PDO::FETCH_ASSOC);


// Fetch appointments related to user's pets
$pet_ids = array_column($pets, 'id');
$placeholders = implode(',', array_fill(0, count($pet_ids), '?'));
$appointment_sql = "SELECT * FROM appointments WHERE pet_id IN ($placeholders)";
$appointment_stmt = $db->prepare($appointment_sql);
$appointment_stmt->execute($pet_ids);
$appointments = $appointment_stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css/user.css">
    </head>
<body>
    <!-- Background Video -->
    <video autoplay muted loop id="backgroundVideo">
        <source src="images/video2.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <div class="dashboard">
        <h1>Welcome, <?php echo htmlspecialchars($user_data['username']); ?></h1>
        
     <!-- User Information Section -->
<div class="user-info glass">
    <div class="user-info-container">
        <div class="user-info-left">
            <img src="images/profile.png" alt="Profile Picture" class="profile-picture">
            
        </div>
        <div class="user-info-right">
            <h2>User Information</h2>
            <p>Username: <?php echo htmlspecialchars($user_data['username']); ?></p>
            <p>Email: <?php echo htmlspecialchars($user_data['email']); ?></p>
            <p>Phone: <?php echo htmlspecialchars($user_data['phone']); ?></p>
            <p>Address: <?php echo htmlspecialchars($user_data['address']); ?></p>
            <button id="editButton" class="btn glass-btn">Edit</button>
            <button><a href="logout.php" id="logoutButton" class="btn glass-btn">Logout</a></button>
            <button id="yourAppointmentsButton" class="btn glass-btn">Your Appointments</button>
        </div>
    </div>
</div>

<!-- Pet Section -->
<h2>Your Pets</h2>
<div class="button-row">
    <button id="registerPetButton" class="register-pet-btn ">Register New Pet</button>
    <button id="makeAppointmentButton" class="btnn">Make an Appointment</button>
</div>
<div class="pets">
    <?php foreach ($pets as $pet): ?>
        <div class="pet-card glass" id="pet-<?php echo $pet['id']; ?>">
            <div class="pet-image">
                <img src="uploads/<?php echo htmlspecialchars($pet['petpicture']); ?>" alt="Pet Picture" width="100">
            </div>
            <div class="pet-details">
                <h3><?php echo htmlspecialchars($pet['petname'] ?? 'Unknown'); ?></h3>
                <p>Type: <?php echo htmlspecialchars($pet['species'] ?? 'Unknown'); ?></p>
                <p>Age: <?php echo htmlspecialchars($pet['age'] ?? 'Unknown'); ?></p>
                <p>Breed: <?php echo htmlspecialchars($pet['breed'] ?? 'Unknown'); ?></p>
                <button onclick="deletePet(<?php echo $pet['id']; ?>)" style="background-color: red; color: white; border: none; padding: 10px 20px; cursor: pointer;">Delete</button>
            </div>
        </div>
    <?php endforeach; ?>
</div>


        <!-- Make Appointment Button -->
        




<!-- Edit Modal -->
<div id="editModal" class="emodal">
    <div class="modal-content glass">
        <span class="close">&times;</span>
        <h2>Edit User Information</h2>
        <form action="update_user.php" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>">
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user_data['phone']); ?>">
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user_data['address']); ?>">
            </div>
            <button type="submit">Update</button>
        </form>
    </div>
</div>

<!-- Register Pet Modal -->
<div id="registerPetModal" class="rmodal">
        <div class="rmodal-content glass">
            <span class="close">&times;</span>
            <h2>Register New Pet</h2>
            <iframe id="registerPetIframe" src="" frameborder="0" style="width: 100%; height: 90%; border-radius:15px; "></iframe>
        </div>
    </div>

   <!-- Make Appointment Modal -->
   <div id="makeAppointmentModal" class="rmodal">
        <div class="rmodal-content glass">
            <span class="close">&times;</span>
            <h2>Make an Appointment</h2>
            <div class="appointments">
                <?php foreach ($pets as $pet): ?>
                    <div class="appointment-card glass">
                        <h3>Make an Appointment for <?php echo htmlspecialchars($pet['petname'] ?? 'Unknown'); ?></h3>
                        <form action="make_appointment.php" method="post" class="appointment-form">
                            <input type="hidden" name="pet_id" value="<?php echo htmlspecialchars($pet['id']); ?>">
                            <div class="form-group">
                                <label for="pet_name_<?php echo htmlspecialchars($pet['id']); ?>">Pet Name:</label>
                                <input type="text" id="pet_name_<?php echo htmlspecialchars($pet['id']); ?>" name="pet_name" value="<?php echo htmlspecialchars($pet['petname']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="appointment_date_<?php echo htmlspecialchars($pet['id']); ?>">Appointment Date:</label>
                                <input type="datetime-local" id="appointment_date_<?php echo htmlspecialchars($pet['id']); ?>" name="appointment_date" required>
                            </div>
                            <div class="form-group">
                                <label for="description_<?php echo htmlspecialchars($pet['id']); ?>">Description:</label>
                                <textarea id="description_<?php echo htmlspecialchars($pet['id']); ?>" name="description" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn">Make Appointment</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
 <div>

 </div>   

 <!-- Modal Structure -->
<div id="appointmentsModal" class="rmodal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Your Appointments</h2>
        <div id="appointmentsList">
            <!-- Appointments will be populated here -->
        </div>
    </div>
</div>
<script>
    // Get the modals
    var editModal = document.getElementById("editModal");
    var registerPetModal = document.getElementById("registerPetModal");

    // Get the buttons that open the modals
    var editBtn = document.getElementById("editButton");
    var registerPetBtn = document.getElementById("registerPetButton");

    // Get the <span> elements that close the modals
    var closeBtns = document.getElementsByClassName("close");

    // When the user clicks the button, open the edit modal 
    editBtn.onclick = function() {
        editModal.style.display = "block";
    }

 // When the user clicks the button, open the register pet modal 
    registerPetBtn.onclick = function() {
        var iframe = document.getElementById("registerPetIframe");
        var email = "<?php echo htmlspecialchars($_SESSION['email']); ?>";
        iframe.src = "add_pet.php?email=" + encodeURIComponent(email);
        registerPetModal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modals
    for (var i = 0; i < closeBtns.length; i++) {
        closeBtns[i].onclick = function() {
            this.parentElement.parentElement.style.display = "none";
        }
    }

    // When the user clicks anywhere outside of the modals, close them
    window.onclick = function(event) {
        if (event.target == editModal) {
            editModal.style.display = "none";
        }
        if (event.target == registerPetModal) {
            registerPetModal.style.display = "none";
        }
    }
</script>
<script>
        // Get the modals
        var makeAppointmentModal = document.getElementById("makeAppointmentModal");
        var registerPetModal = document.getElementById("registerPetModal");

        // Get the buttons that open the modals
        var makeAppointmentBtn = document.getElementById("makeAppointmentButton");
        var registerPetBtn = document.getElementById("registerPetButton");

        // Get the <span> elements that close the modals
        var closeBtns = document.getElementsByClassName("close");

        // When the user clicks the button, open the make appointment modal
        makeAppointmentBtn.onclick = function() {
            makeAppointmentModal.style.display = "block";
            makeAppointmentModal.classList.add("show");
        }

        // When the user clicks the button, open the register pet modal
        registerPetBtn.onclick = function() {
            var iframe = document.getElementById("registerPetIframe");
            var email = "<?php echo htmlspecialchars($_SESSION['email']); ?>";
            iframe.src = "add_pet.php?email=" + encodeURIComponent(email);
            registerPetModal.style.display = "block";
            registerPetModal.classList.add("show");
        }

        // When the user clicks on <span> (x), close the modals
        for (var i = 0; i < closeBtns.length; i++) {
            closeBtns[i].onclick = function() {
                this.parentElement.parentElement.style.display = "none";
                this.parentElement.parentElement.classList.remove("show");
            }
        }
        // When the user clicks anywhere outside of the modals, close them
        window.onclick = function(event) {
            if (event.target == makeAppointmentModal) {
                makeAppointmentModal.style.display = "none";
                makeAppointmentModal.classList.remove("show");
            }
            if (event.target == registerPetModal) {
                registerPetModal.style.display = "none";
                registerPetModal.classList.remove("show");
            }
        }
    </script>
     <!-- Pass the appointments to JavaScript -->
     <script>
        const appointments = <?php echo json_encode($appointments); ?>;
    </script>

    <script>
         document.getElementById('yourAppointmentsButton').addEventListener('click', function() {
            // Populate the appointments list
            const appointmentsList = document.getElementById('appointmentsList');
            appointmentsList.innerHTML = '';
            appointments.forEach(appointment => {
                const appointmentItem = document.createElement('div');
                appointmentItem.className = 'appointment-item';
                appointmentItem.innerHTML = `
                    <p><strong>Pet Name:</strong> ${appointment.pet_name}</p>
                    <p><strong>Date:</strong> ${appointment.appointment_date}</p>
                    <p><strong>Description:</strong> ${appointment.description}</p>
                `;
                appointmentsList.appendChild(appointmentItem);
            });

            // Show the modal
            const modal = document.getElementById('appointmentsModal');
            modal.style.display = 'block';
        });

        // Close the modal when the user clicks on <span> (x)
        document.querySelector('#appointmentsModal .close').addEventListener('click', function() {
            const modal = document.getElementById('appointmentsModal');
            modal.style.display = 'none';
        });

        // Close the modal when the user clicks anywhere outside of the modal
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('appointmentsModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    </script>
    <script>
function deletePet(petId) {
    if (confirm('Are you sure you want to delete this pet?')) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'delete_user_pet.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    var petCard = document.getElementById('pet-' + petId);
                    petCard.parentNode.removeChild(petCard);
                } else {
                    alert('Failed to delete pet.');
                }
            }
        };
        xhr.send('id=' + petId);
    }
}
</script>
</body>
</html>
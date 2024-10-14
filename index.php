<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetCare System</title>
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h1>PetCare System</h1>
            </div>
        </nav>
    </header>
    <main>
<div class="hero">
    <video autoplay muted loop id="hero-video">
        <source src="images/video1.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <h1>Welcome to PetCare System</h1>
    <p>Your one-stop solution for all pet care needs.</p>
    <div class="hero-buttons">
        <a href="register_user.php" class="btn btn-primary">Register Now</a>
        <a href="services.php" class="btn btn-secondary">Explore Our Services</a>
    </div>
</div>

<section class="services-overview">
    <h2>Our Services</h2>
    <div class="service">
        <h3>Grooming</h3>
        <p>Professional grooming services to keep your pet looking their best.</p>
    </div>
    <div class="service">
        <h3>Veterinary Services</h3>
        <p>Comprehensive veterinary care to ensure your pet's health.</p>
    </div>
    <div class="service">
        <h3>Pet Sitting</h3>
        <p>Reliable pet sitting services for your peace of mind.</p>
    </div>
    <div class="service">
        <h3>Training</h3>
        <p>Expert training to help your pet learn and grow.</p>
    </div>
   

</section>
</main>
    <a href="user_login.php" class="btn btn-login">Log In</a>
     <iframe src="https://lottie.host/embed/e6da1a3c-0faf-440f-9513-dcfdf491a573/E8EGofcdUZ.json" class="overlay-iframe" style="border: none; width: 800%; height: 80%;"></iframe>

<section class="about-us">
    <h2>About Us</h2>
    <p>At PetCare System, we are dedicated to providing the best care for your pets. Our team of experienced professionals is here to offer a wide range of services to meet all your pet care needs.</p>
</section>

<section class="contact-us">
    
</section>

<?php include('includes/footer.php'); ?>

<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: userLogin.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
 
    include_once "../connection.php";
    //header
    include_once "header_user.php";
    ?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About page</title>
    <link rel="stylesheet" href="../beforeLogin_style/aboutUs.css" type="text/css">
</head>
<body>
    
   

    <!-- About Us Section -->
    <section class="about-section">
        <div class="about-hero">
            <h1>About Us</h1><br>
            <p>Your Pet's Well-being, Our Passion.</p>
        </div>

        <!-- Intro Section with Image -->
        <div class="about-intro">
            <div class="intro-text">
                <h2>Who We Are</h2><br>
                <p>PetHug Veterinary Hospital is committed to providing top-tier medical care for your beloved pets. We take a personal approach to veterinary medicine, ensuring that your pets receive the utmost care and compassion.</p>
            </div>
            <div class="intro-img">
                <img src="../images/istockphoto-1171733307-612x612.jpg" alt="Pets and Vets">
            </div>
        </div>

        <!-- Mission Section -->
        <div class="mission-section">
            <div class="mission-content">
                <h2>Our Mission</h2>
                <p>Our mission is simple: to enhance the health and happiness of every pet we encounter. We believe in comprehensive care, which means we go beyond treatment and focus on prevention, education, and a holistic approach to health.</p>
            </div>
        </div>

        <!-- Our Team Section -->
        <div class="team-section">
            <h2>Meet Our Team</h2>
            <div class="team-cards">
                <div class="team-card">
                    <img src="../images/doc4.jpg" alt="Vet 1">
                    <h3>Dr. Emma Johnson</h3>
                    <p>Chief Veterinarian</p>
                </div>
                <div class="team-card">
                    <img src="../images/doc3.jpg" alt="Vet 2">
                    <h3>Dr. Michael Brown</h3>
                    <p>Veterinary Surgeon</p>
                </div>
                <div class="team-card">
                    <img src="../images/doc1" alt="Vet 3">
                    <h3>Dr. Sarah Lee</h3>
                    <p>Animal Behavior Specialist</p>
                </div>
            </div>
        </div>

        <!-- Values Section -->
        <div class="values-section">
            <h2>Our Values</h2>
            <div class="values-grid">
                <div class="value-card">
                    <h3>Compassion</h3>
                    <p>We treat every pet like family, ensuring their well-being is our top priority.</p>
                </div>
                <div class="value-card">
                    <h3>Excellence</h3>
                    <p>We aim for the highest standards in all veterinary services we provide.</p>
                </div>
                <div class="value-card">
                    <h3>Innovation</h3>
                    <p>Staying updated with the latest in veterinary care helps us offer the best to your pets.</p>
                </div>
                <div class="value-card">
                    <h3>Integrity</h3>
                    <p>We communicate openly and prioritize your pet's needs above all else.</p>
                </div>
            </div>
        </div>

    </section>

    <!--footer-->
    <?php include_once "../footer.php"?>

</body>
</html>
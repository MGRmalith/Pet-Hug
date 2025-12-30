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
    <title>Our Services</title>
    <link rel="stylesheet" href="../beforeLogin_style/service.css" type="text/css">
</head>
<body>

   
    <!--services-->
    <div class="service-header">
        <h1>Our Veterinary Services</h1>
        <p>Explore the range of services we offer to keep your pet healthy and happy.</p>
    </div>

    <section class="services-section">
        <!-- General Veterinary Care Section -->
         <div class="service-container1">
            <div class="service" id="service-1">
                <h2><i class="fas fa-user-md"></i> General Veterinary Care</h2><br>
                <p>We offer a broad range of veterinary care, including regular examinations, vaccinations, diagnostics, and microchipping to keep your pet safe and healthy.</p><br>
                <ul>
                    <li><strong>Examinations & Consultations:</strong> Routine check-ups and wellness exams to monitor your pet’s health.</li>
                    <li><strong>Vaccinations:</strong> Core and non-core vaccines to prevent diseases.</li>
                    <li><strong>Diagnostics:</strong> Blood tests, urinalysis, fecal exams, and imaging (X-rays, ultrasound).</li>
                    <li><strong>Microchipping:</strong> Implanting microchips for pet identification and safety.</li>
                </ul>
            </div>
            <div class="img">
                <img src="../images/vaccination.jpg">
            </div>
        </div>

        <!-- Pet Boarding Section -->
        <div class="service-container2">
            <div class="img">
                <img src="../images/Pet-Boarding.png">
            </div>
            <div class="service" id="service-2">
                <h2><i class="fas fa-home"></i> Pet Boarding</h2><br>
                <p>We provide a safe and comfortable environment for your pets while you are away. Our boarding facilities are designed with your pet’s well-being in mind, ensuring they are cared for and happy throughout their stay.</p><br>
                <ul>
                    <li><strong>Short-term and Long-term Boarding:</strong> Whether it’s a weekend getaway or an extended trip, we’ve got your pets covered.</li>
                    <li><strong>Medical Boarding:</strong> For pets with special needs, our team ensures they receive the attention and care required, including administering medication.</li>
                    <li><strong>Comfort and Play:</strong> Spacious areas for rest, regular feeding, and supervised playtime tailored to your pet’s personality.</li>
                    <li><strong>Veterinary Supervision:</strong> Experienced staff monitor your pet’s health around the clock, providing peace of mind while you're away.</li>
                </ul>
            </div>
        </div>

        <!-- Consultations Section -->
        <div class="service-container3">
            <div class="service" id="service-3">
                <h2><i class="fas fa-stethoscope"></i> Consultations</h2><br>
                <p>We believe in proactive care and expert guidance for your pet’s health at every stage. Our consultation services are here to assist with regular check-ups, behavioral issues, and specialized treatments.</p><br>
                <ul>
                    <li><strong>General Health Consultations:</strong> Comprehensive wellness checks to ensure your pet’s health and happiness.</li>
                    <li><strong>Specialist Consultations:</strong> Access to specialists for specific conditions such as dermatology, neurology, and more.</li>
                    <li><strong>Behavioral Consultations:</strong> Expert advice for managing anxiety, aggression, or training challenges.</li>
                    <li><strong>Telemedicine:</strong> Virtual consultations for follow-ups or minor health concerns, from the comfort of your home.</li>
                    <li><strong>Geriatric Care:</strong> Tailored care plans for senior pets, addressing age-related concerns.</li>
                </ul>
            </div>
            <div class="img">
                <img src="../images/consultaion.jpg">
            </div>
        </div>

        <!-- Surgery Section -->
        <div class="service-container4">
            <div class="img">
                <img src="../images/su.jpg">
            </div>
            <div class="service" id="service-4">
                <h2><i class="fas fa-syringe"></i> Surgery</h2><br>
                <p>Our veterinary team is skilled in performing a wide range of surgeries, from routine procedures to more complex operations, ensuring your pet receives the highest level of care.</p><br>
                <ul>
                    <li><strong>Routine Surgeries:</strong> Spaying, neutering, and other standard surgeries.</li>
                    <li><strong>Complex Surgeries:</strong> Orthopedic, soft tissue, and other advanced procedures.</li>
                    <li><strong>Post-Surgical Care:</strong> Comprehensive aftercare to ensure a smooth and fast recovery.</li>
                </ul>
            </div>
        </div>

        <!-- Preventive Care Section -->
        <div class="service-container5">
            <div class="service" id="service-5">
                <h2><i class="fas fa-heartbeat"></i> Preventive Care</h2><br>
                <p>Our preventive care services are designed to keep your pet in good health throughout their life. Regular check-ups, vaccinations, and health monitoring are key to a long, healthy life for your pet.</p><br>
                <ul>
                    <li><strong>Vaccinations:</strong> Essential vaccines to prevent common diseases.</li>
                    <li><strong>Parasite Control:</strong> Regular treatments for fleas, ticks, and worms.</li>
                    <li><strong>Health Screening:</strong> Routine blood tests, urinalysis, and other preventive diagnostics.</li>
                </ul>
            </div>
            <div class="img">
                <img src="../images/preventive care.jpeg">
            </div>
        </div>
    </section>

    <!--footer-->
    <?php include_once "../footer.php"?>

</body>
</html>
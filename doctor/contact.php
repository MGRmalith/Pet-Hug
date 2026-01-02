<?php

session_start();
if (!isset($_SESSION['dr_id'])) {
    header("Location: doctorLogin.php");
    exit();
}

include_once '../connection.php'; // Include the database connection file
include_once 'header_dr.php';

$doctor_id = $_SESSION['dr_id'];




if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $rating = $_POST['rating'];
    $feedback_text = $_POST['feedback'];

    // Validate data
    if (empty($rating) || empty($feedback_text)) {
        echo "Please fill in all fields.";
    } else {
        // Insert feedback into the database
        $stmt = $conn->prepare("INSERT INTO feedback (rating, feedback_text) VALUES (?, ?)");
        $stmt->bind_param("is", $rating, $feedback_text);

        if ($stmt->execute()) {
            // Get the feedback ID for notification
            $feedback_id = $stmt->insert_id;

            // Insert notification for admin
            $notification_stmt = $conn->prepare("INSERT INTO notifications (recipient_type, recipient_id, title, message, service_type, service_id) VALUES ('admin', 1, ?, ?, 'feedback', ?)");
            $title = 'New Feedback Submission';
            $message = "A new feedback has been submitted with ID: $feedback_id. Please review it.";
            $notification_stmt->bind_param("ssi", $title, $message, $feedback_id);

            if ($notification_stmt->execute()) {
                echo "Thank you for your feedback! Admin has been notified.";
            } else {
                echo "Feedback submitted, but there was an error notifying the admin.";
            }

            $notification_stmt->close();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}


?>





<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact page</title>
    <link rel="stylesheet" href="../beforeLogin_style/contact.css" type="text/css">
    <?php echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">';?>
</head>
<body>
    
   

    <!-- Contact Section -->
    <section class="contact-us">
        <div class="container">
            <!-- Success Message -->
            <?php
            if (isset($_GET['status']) && $_GET['status'] == 'success') {
                echo "<p class='success_message'>Thank you for your message. We will get back to you soon!</p>";
            }
            ?>

            <h1>Get in Touch</h1><br>
            <p>We’d love to hear from you! Whether it’s a question about our services or just some advice for your pet, feel free to reach out.</p>

            <!-- Contact Information -->
            <div class="contact-info">
                <div class="info-item">
                    <i class="fas fa-phone-alt"></i>
                    <h3>Phone</h3><br>
                    <p>+1 234 567 890</p>
                    <p>+1 987 654 321</p>
                </div>
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <h3>Email</h3><br>
                    <p>info@pethugvet.com</p>
                </div>
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Visit Us</h3><br>
                    <p>123 Pet Lane, Animal Town, Country</p>
                </div>
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <h3>Opening Hours</h3><br>
                    <div id="opening-hours">
                        <p>Monday - Friday: 8:00 AM - 6:00 PM</p>
                        <p>Saturday: 9:00 AM - 5:00 PM</p>
                        <p>Sunday: Closed</p>
                        <p>Poya day: Closed</p>
                    </div>
                </div>
            </div>

            <!-- Social Media Links -->
            <div class="social-links">
                <a href="https://facebook.com/pethugvet" class="social-link" target="_blank"><i class="fab fa-facebook"></i></a>
                <a href="https://twitter.com/pethugvet" class="social-link" target="_blank"><i class="fab fa-twitter"></i></a>
                <a href="https://instagram.com/pethugvet" class="social-link" target="_blank"><i class="fab fa-instagram"></i></a>
            </div>

            <div class="contact-feedback">
                <!-- Feedback Section -->
                <div class="feedback">
                    <h2>We Value Your Feedback</h2><br>
                    <p>Let us know how we can improve your experience at Pet Hug Veterinary Hospital.</p><br>
                    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
                        <label for="rating">Rating:</label>
                        <select id="rating" name="rating" required>
                            <option value="" disabled selected>Select your rating</option>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Good</option>
                            <option value="3">3 - Average</option>
                            <option value="2">2 - Below Average</option>
                            <option value="1">1 - Poor</option>
                        </select>

                        <label for="feedback">Your Feedback:</label>
                        <textarea id="feedback" name="feedback" placeholder="Tell us about your experience" rows="5" required></textarea>

                        <button type="submit" class="submit-btn">Submit Feedback</button>
                    </form>
                </div>

                <!-- Contact Form -->
                <div class="contact-form">
                    <h2>Contact Us</h2><br>
                    <form action="../contact_process.php" method="post">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" placeholder="Your name" required>

                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" placeholder="Your email" required>

                        <label for="subject">Subject:</label>
                        <input type="text" id="subject" name="subject" placeholder="Subject" required>

                        <label for="message">Message:</label>
                        <textarea id="message" name="message" placeholder="Your message" rows="5" required></textarea>

                        <button type="submit" class="submit-btn">Send Message</button>
                    </form>
                </div>
            </div>

        <?php
            // Query to fetch all feedback and ratings
            $sql = "SELECT rating, feedback_text FROM feedback ORDER BY created_at DESC";
            $result = $conn->query($sql);

            // Check if feedback exists
            if ($result->num_rows > 0) {
                // Output feedback data
                echo "<h2>Customer Feedback</h2>";
                echo "<div class='feedback-list'>";
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='feedback-item'>";
                    echo "<p><strong>Rating: </strong>" . $row['rating'] . "/5</p>";
                    echo "<p><strong>Feedback:</strong><br>" . $row['feedback_text'] . "</p>";
                    echo "</div>";
                }
                echo "</div>";
                echo"<div class='carousel-dots'>
                    <span class='carousel-dot active' onclick='moveSlide(0)'></span>
                    <span class='carousel-dot' onclick='moveSlide(1)'></span>
                    <span class='carousel-dot' onclick='moveSlide(2)'></span>
                    <!-- Add more dots as per the number of feedback items -->
                </div>";
            }
        ?>


            <!-- Map Integration (You can replace the src with a real map) -->
            <div class="map">
                <h2>Visit Us</h2><br>
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.2871487030316!2d79.91297847448317!3d6.975408517747951!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae2597c8dde7e47%3A0x341e7e820c46d3ed!2sUniversity%20of%20Kelaniya!5e0!3m2!1sen!2slk!4v1729247408508!5m2!1sen!2slk" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </section>

    <script>
        let currentSlide = 0;

        function moveSlide(slideIndex) {
            const feedbackList = document.querySelector('.feedback-list');
            const dots = document.querySelectorAll('.carousel-dot');
            const slideWidth = feedbackList.clientWidth / 2; // Adjust to show two items per view

            currentSlide = slideIndex;
            
            feedbackList.style.transform = `translateX(-${slideWidth * currentSlide}px)`;

            dots.forEach(dot => dot.classList.remove('active'));
            dots[slideIndex].classList.add('active');
        }


    </script>


    <!--footer-->
    <?php include_once "../footer.php"?>

</body>
</html>

<?php $conn->close(); ?>
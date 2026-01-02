<?php
// Get the current page's file name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <link rel="stylesheet" href="../afterLoginUser_style/header.css" type="text/css">
    <?php echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">';?>
</head>
<body>
    
    <!--header section-->
    <header class="header">
        <div id="logo">
            <img src="../images/PetHugLogo.png">
        </div>
        <nav class="nav-bar">
            <!-- Hamburger icon -->
            <div class="hamburger" onclick="toggleMenu()">&#9776;</div>

            <ul class="nav-links">
                <li><a href="home.php" class="<?php if ($current_page == 'doctor_home.php'){echo 'active';} ?>">Home</a></li>
                <li><a href="doctor_dashboard.php" class="<?php if($current_page == 'doctor_dashboard.php'){echo 'active';} ?>">Dashboard</a></li>
                <li><a href="give_consultation.php" class="<?php if($current_page == 'give_consultation.php'){echo 'active';} ?>"> Consultations</a></li>
                <li><a href="assigned_appointments.php" class="<?php if($current_page == 'assigned_appointments.php'){echo 'active';} ?>">Appointments</a></li>

                <li><a href="generate_doctor_report.php" class="<?php if($current_page == 'generate_doctor_report.php'){echo 'active';} ?>">Reports</a></li>
                <li><a href="doctor_earnings.php" class="<?php if($current_page == 'doctor_earnings.php'){echo 'active';} ?>">Earnings</a></li>
                
                <li><a href="doctor_notifications.php"><i style="font-size: 30px" class="fas fa-bell"></i></a></li>
                
                <li>
                    <button onclick="togglePopup('profilePopup')"><i style="font-size: 30px " class="fas fa-user-circle"></i></button>
                    <div id="profilePopup" class="popup">
                        <p><a href="doctor_profile.php">Profile</a></p><br>
                        <p><a href="../logout.php">Logout</a></p>
                    </div>
                </li>

            </ul>
        </nav>
    </header>
    <script src="../javascript_A/header.js"></script>
    <script>
        function toggleMenu() {
            const navLinks = document.querySelector('.nav-links');
            navLinks.classList.toggle('active');  // Toggle the 'active' class to show or hide the menu
        }
    </script>
</body>
</html>
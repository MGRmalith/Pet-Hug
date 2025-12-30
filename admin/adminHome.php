<?php
session_start();  // Start the session for managing user login

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to the dashboard or another page if the user is logged in
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Home</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: url('https://t3.ftcdn.net/jpg/00/58/63/80/360_F_58638099_bLBnXzWN6eMulLSvyCaRIJfPN5yRayU1.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white;
        }

        .main-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: rgba(0, 0, 0, 0.6); /* Dark overlay */
        }

        .welcome-box {
            text-align: center;
            max-width: 600px;  /* Increased width */
            padding: 100px;  /* Increased padding */
            background: linear-gradient(45deg, rgba(29, 151, 212, 0.7), rgba(224, 214, 224, 0.7));
            border-radius: 12px;
        }

        .logo {
            max-width: 150px;  /* Adjust logo size */
            margin-bottom: 30px;  /* Space between logo and text */
        }

        .welcome-box h1 {
            font-size: 40px;
            margin-bottom: 30px;
            color: #fff;
        }

        .welcome-box p {
            font-size: 20px;
            margin-bottom: 20px;
        }

        .action-btn {
            background-color:rgb(37, 84, 214);
            color: white;
            padding: 16px 34px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 18px;
            margin: 18px 12px 6px;
            cursor: pointer;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .action-btn:hover {
            background-color:rgb(22, 14, 81);
        }

        .welcome-box a {
            color: white;
            text-decoration: none;
        }

        .welcome-box p a:hover {
            text-decoration: underline;
        }

        .welcome-box a:hover {
            text-decoration: none;
        }

        .logo {
            position: absolute;
            top: 100px;
            right: 400px;
            max-width: 200px;  /* Adjust logo size */
            margin-bottom: 0;  /* Space between logo and text */
        }

        
    </style>
</head>
<body>

    <div class="main-container">
        <div class="welcome-box">
            <!-- Logo Added Here -->
            <img src="../images/PetHugLogo(1).png" alt="PetHug Logo" class="logo">  <!-- Change the logo path accordingly -->
            <h1>Welcome to PetHug Veterinary Hospital Administration!</h1>
            <p>Already a member? <a href="adminLogin.php">Log In</a></p>
            <p>New here? <a href="admin_signup.php">Create a New Account</a></p>
            <div>
                <a href="adminLogin.php" class="action-btn">Log In to Your Account</a>
                <a href="admin_signup.php" class="action-btn">Join Us Today</a>
            </div>
        </div>
    </div>

</body>
</html>

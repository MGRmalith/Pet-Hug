<?php
include_once "../connection.php";

session_start(); // Start the session


// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the user exists in the database
    $sql = "SELECT * FROM user WHERE user_email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['user_password'])) {
            // Set session variable
            $_SESSION['user_id'] = $user['user_id'];
            header("Location:../user/dashboard.php"); // Redirect to user home page
            exit();
        } else {
            $error_message = "Incorrect password. Please try again.";
        }
    } else {
        $error_message = "No account found with this email.";
    }
}

$conn->close();
?>

<?php
// Get the current page's file name
$current_page = basename($_SERVER['PHP_SELF']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Page</title>
  <link rel="stylesheet" href="../afterLoginUser_style/login.css">

  <script>
     function validateForm() {
         var email = document.getElementById("email").value;
         var password = document.getElementById("password").value;
         var emailErrorMessage = document.getElementById("email-error-message");
         var passwordErrorMessage = document.getElementById("password-error-message");

         // Clear previous error messages
         emailErrorMessage.innerText = "";
         passwordErrorMessage.innerText = "";

        // Email validation
        if (email == "") {
          emailErrorMessage.innerText = "Please enter your email.";
          return false;
        }
        if (!validateEmail(email)) {
          emailErrorMessage.innerText = "Please enter a valid email address.";
          return false;
       }

        // Password validation
       if (password == "") {
         passwordErrorMessage.innerText = "Please enter your password.";
         return false;
       }

       return true; // All validations passed
   }

   function validateEmail(email) {
      var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
     return re.test(email);
  }
  </script>

</head>
<body>
  <div class="container">
    <!-- Left Section -->
    <div class="left-section">
      <img src="../images/PetHugLogo(1).png" alt="Logo">
      <h2>Login</h2>
      <p class="subtitle">Enter your email and password to access your account.</p>

      <?php 
        if (isset($error_message)) { 
            echo "<div class='error' style='color: red;'>$error_message</div>"; 
        } 
        ?>

      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" onsubmit="return validateForm()">
        <div class="form-group">
            <div id="email-error-message" class="error"></div> <!-- Email error message -->
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
            <div id="password-error-message" class="error"></div> <!-- Password error message -->
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <div class="form-actions">
          <div>
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Remember me</label>
          </div>
          <a href="#" class="forgot-password">Forgot Password?</a>
        </div>
        <button type="submit" class="btn-signin">Log In</button>
      </form>
      <p class="signup-link">Don't have an account? <a href="user_signup.php">Sign Up</a></p>
    </div>

    <!-- Right Section -->
    <div class="right-section">
        <h1>Welcome Back to PetHug</h1>
        <p>Your partner in pet care and wellness.</p>
        <img src="../images/images (1).png" alt="Pets Illustration" class="right-image">
    </div>
</body>
</html>
